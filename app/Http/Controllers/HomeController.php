<?php

namespace App\Http\Controllers;

use App\CompanyAmount;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Merchant;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(IRoleRepository $role)
    {
        $this->middleware('auth');
        $this->role = $role;
    }

    public function index()
    {
        return view('home');
    }

    public static function logos()
    {
        $iid = Auth::user()->id;
        $image = User::where('id', $iid)->first();

        return $image->logo;
    }

    public function lenderUnderWritingFeeChangeToJsonAction()
    {
        $companies = $this->role->allCompanies();
        $companies = $companies->pluck('name', 'id')->toArray();
        array_unshift($companies, '');
        unset($companies[0]);
        $comp = array_keys($companies);
        $users = User::select('underwriting_status', 'id')->whereNotNull('underwriting_status')->get()->toArray();
        if (! empty($users)) {
            $underwriting_status = 0;
            foreach ($users as $key => $value) {
                if ($value['underwriting_status'] == 0) {
                    $underwriting_status = $comp;
                } else {
                    $underwriting_status = [$value['underwriting_status']];
                }
                $underwriting_status = json_encode($underwriting_status, true);
                $change = User::find($value['id'])->update(['underwriting_status' => $underwriting_status]);
            }
            echo 'all lender underwriting_status change to json format';
        }
    }

    public function merchantUnderWritingStatusChangeToJsonAction()
    {
        $companies = $this->role->allCompanies();
        $companies = $companies->pluck('name', 'id')->toArray();
        array_unshift($companies, '');
        unset($companies[0]);
        $comp = array_keys($companies);
        $merchants = Merchant::select('underwriting_status', 'id')->whereNotNull('underwriting_status')->get()->toArray();
        if (! empty($merchants)) {
            $underwriting_status = 0;
            foreach ($merchants as $key => $value) {
                if ($value['underwriting_status'] == 0) {
                    $underwriting_status = $comp;
                } else {
                    $underwriting_status = [$value['underwriting_status']];
                }
                $underwriting_status = json_encode($underwriting_status, true);
                $change = Merchant::find($value['id'])->update(['underwriting_status' => $underwriting_status]);
            }
            echo 'all underwriting_status change to json format';
        }
    }

    public function merchantsToCompanyAmountAction()
    {
        $merchants = Merchant::select('velocity1_max', 'velocity2_max', 'id')->where('move_status', 0)->get()->toArray();
        if (! empty($merchants)) {
            foreach ($merchants as $key => $value) {
                $data[$key]['max_participant'] = $value['velocity1_max'];
                $data1[$key]['max_participant'] = $value['velocity2_max'];
                $data[$key]['merchant_id'] = $value['id'];
                $data1[$key]['merchant_id'] = $value['id'];
                if ($merchants[$key]['velocity1_max']) {
                    $data[$key]['company_id'] = 58;
                }
                if ($merchants[$key]['velocity2_max']) {
                    $data1[$key]['company_id'] = 89;
                }
                Merchant::find($value['id'])->update(['move_status' => 1]);
            }
            $total = array_merge($data, $data1);
            $test = CompanyAmount::insert($total);
            echo 'move merchants table to company table successfully';
        }
    }

    public function resetDbAction(Request $request)
    {
        $db = session('DB_DATABASE');
        if ($db) {
            $database = config('app.database');
            $host = config('app.db_url');
            $username = config('app.username');
            $password = config('app.password');
            if ($database) {
                \Config::set('database.connections.mysql.database', $database);
                DB::purge('mysql');
                $request->session()->put('DB_DATABASE', '');
                $request->session()->put('restore', 0);
                echo 'reset db successfully';
            }
        }
    }

    public function reconciliationStatus($id, $status)
    {
        $merchant_id = $id;
        $status = ($status == 1) ? 'yes' : 'no';
        $insert = DB::table('reconcilation_status')->insert(['merchant_id' => $id, 'reconciliation_status' => $status]);
        echo 'updated successfully';
    }
}
