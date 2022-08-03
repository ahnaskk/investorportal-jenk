<?php

namespace App\Http\Controllers\Admin;

use App\Bank;
use App\Funding;
use App\Helpers\ActumRequest;
use MerchantUserHelper;
use App\Helpers\PdfDocumentHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoSignupFundingRequest;
use App\Http\Requests\Admin\ProfileFundingRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Industries;
use App\Jobs\CommonJobs;
use App\Jobs\UserActivityLogJob;
use App\Library\Repository\InvestorTransactionRepository;
use App\Merchant;
use App\Models\InvestorAchRequest;
use App\Providers\UserActivityLogServiceProvider;
use App\Settings;
use App\Template;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Exception;
use FFM;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Stripe\Charge;
use Stripe\Stripe;
use FundingHelper;

class FundingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $bank = Bank::where('investor_id', Auth::user()->id)->count();
                $user = User::find(Auth::user()->id);
                switch (true) {
                    case Auth::user()->investor_type != 5:
                        Auth::logout();
                        Session::flush();

                        return redirect()->to('/fundings/login')->with('error', 'Please try again');
                    case! $user->cell_phone:
                        Auth::logout();
                        Session::flush();

                        return redirect()->to('/fundings/login')->with('error', 'Cell Phone not attached');
                    case! $bank:
                        Auth::logout();
                        Session::flush();

                        return redirect()->to('/fundings/login')->with('error', 'Bank not attached');
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $req = [];
        $merchants = MerchantUserHelper::getNotInvestedMarketplaceMerchants($req);

        return view('funding.index', compact('merchants'));
    }

    public function login()
    {
        return view('funding.login');
    }

    public function forgot_password()
    {
        return view('funding.forgot-password');
    }

    public function check_login(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $bank = FundingHelper::getBankCount($request);
            if (! $bank) {
                $request->session()->flush();
                session_set('user_id', $request->user()->id);

                return redirect()->to('/fundings/updatebank');
            }
            if ($request->user()->investor_type != 5) {
                $request->session()->flush();

                return redirect()->to('/fundings/login')->with('error', 'This investor not allowed to login');
            } elseif ($request->user()->funding_status != 1) {
                $request->session()->flush();

                return redirect()->to('/fundings/login')->with('error', 'Not approved Yet');
            }
            $user = $request->user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            session_set('email', $request->user()->email);
            session_set('encryted_id', $user->email);
            session_set('marketplace_flag', true);
            session_set('name', $request->user()->name);
            session_set('token', $user->createToken('MyApp')->accessToken);

            return redirect()->to('/fundings');
        } else {
            return redirect()->to('/fundings/login')->with('error', 'Invalid Email address or Password');
        }
    }

    public function signup()
    {
        Auth::logout();

        return view('funding.signup');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->to('/fundings/login')->with('success', 'Logged out successfully ');
    }

    public function doSignup(DoSignupFundingRequest $request)
    {
    Auth::logout();
    $request->session()->flush();
    $user = FundingHelper::signup($request);          
    UserActivityLogServiceProvider::activityLog(User::findOrFail($user->id), 'investor', 'created', [], $user->id);
    session_set('user_id', $user->id);
    return redirect()->to('/fundings/updatebank');
    }

    public function marketplace(Request $request, $industry = null)
    {
        if ($industry) {
            $request->merge(['industry_id' => $industry, 'monthly_revenue' => null]);
        }
        $merchants = MerchantUserHelper::getNotInvestedMarketplaceMerchants($request->all());
        $factor_rate_filter = $industry_filter = [];
        $industry_filter = FundingHelper::getIndustries();
        foreach ($merchants as $merchant) {
            if (in_array($merchant->industry->name, $industry_filter)) {
                unset($industry_filter[$merchant->industry_id]);
            }
            if (! in_array($merchant->industry->name, $industry_filter)) {
                $industry_filter[$merchant->industry_id] = ucfirst($merchant->industry->name);
            }
            if (! in_array(round($merchant->factor_rate, 2), $factor_rate_filter)) {
                $factor_rate_filter[] = round($merchant->factor_rate, 2);
            }
        }
        $monthly_revenue_filer = [0 => '0 - 10000', 1 => '10000 - 20000', 2 => '20000 - 30000', 3 => '30000 - 40000', 4 => '40000 - 50000', 5 => '50000+'];
        sort($factor_rate_filter);
        $post_data = $request->all();
        $factor_rate[] = 0;
        $factor_rate[] = 2;
        if (isset($post_data['factor_rate']) && $post_data['factor_rate'] != 'null') {
            $factor_rate = explode(';', $post_data['factor_rate']);
        }
        return view('funding.marketplace', compact('merchants', 'industry_filter', 'factor_rate_filter', 'monthly_revenue_filer', 'post_data', 'factor_rate'));
    }

    public function marketplace_details(Request $request, $id)
    {
        $merchant = Merchant::find($id);
        $fundings = (new Funding())->raised($id);
        $hasFunded = $request->user() ? MerchantUserHelper::investorFunds($id, $request->user()->id) : 0;
        $merchant_market_data = FundingHelper::merchant_market_data($id);

        return view('funding.marketplace-details', compact('id', 'merchant', 'fundings', 'hasFunded', 'merchant_market_data'));
    }   
    public function updateBank(Request $request)
    {
        if (! $request->isMethod('post')) {
            return view('funding.update_bank');
        }
        try {
            $data = ['routing' => $request->routing, 'bank_address' => $request->bank_address, 'name' => $request->name, 'investor_id' => session('user_id'), 'account_holder_name' => $request->account_holder_name, 'acc_number' => $request->acc_number, 'default_debit' => $request->default_debit, 'default_credit' => $request->default_credit];
            $data['type'] = '';
            if (isset($request->type)) {
                $data['type'] = implode(',', $request->type);
            }
            $BankModel = new Bank;
            if ($request->bid) {
                $return_function = $BankModel->selfUpdate($data, $request->bid);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $return['result'] = 'Bank Details Updated Successfully';
            } else {
                $return_function = $BankModel->selfCreate($data);
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $return['result'] = 'Bank Details Created Successfully';
            }
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();

            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
        $request->session()->flash('success', $return['result']);
        if ($request->bid) {
            return redirect()->back();
        } else {
            return redirect()->to('fundings/login');
        }
    }

    public function about_us()
    {
        return view('funding.about-us');
    }

    public function contact_us(Request $request)
    {
        if (! $request->isMethod('post')) {
            return view('funding.contact-us');
        }
        $settings = Settings::where('keys', 'admin_email_address')->first();
        $admin_email = $settings->values;
        $message['title'] = 'Investor Contact us';
        $message['subject'] = 'Investor Contact us';
        $message['status'] = 'funding_investor_contact';
        $message['name'] = 'Admin';
        $message['template_type'] = 'admin';
        $message['email'] = $request->email;
        $message['username'] = $request->name;
        $message['phone'] = $request->phone;
        $message['company'] = $request->company;
        $message['content'] = $request->message;
        $message['message'] = $request->message;
        $message['to_mail'] = $admin_email;
        $emailJob = (new CommonJobs($message));
        dispatch($emailJob);
        $message['content'] = 'Thank you for showing interest we will get back to you soon.';
        $message['name'] = $request->name;
        $message['to_mail'] = $request->email;
        $message['template_type'] = 'others';
        $emailJob = (new CommonJobs($message));
        dispatch($emailJob);

        return redirect()->back()->with('success', 'Thanks for contacting us');
    }

    public function profile(ProfileFundingRequest $request)
    {
        if (! $request->isMethod('post')) {
            return view('funding.profile');
        }
        User::find($request->user()->id)->update($request->except('_token', 'email'));

        return redirect()->back()->with('success', 'Profile Updated Successfully');
    }

    public function how_it_works()
    {
        return view('funding.how-it-works');
    }

    public function terms_and_condition()
    {
        return view('funding.terms-and-condition');
    }

    public function privacy_policy()
    {
        return view('funding.privacy-policy');
    }
}
