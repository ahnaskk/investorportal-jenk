<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserResource;
use App\InvestorDocuments;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMerchantRepository;
use App\Settings;
use App\User;
use App\UserDetails;
use App\UserMeta;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class AuthController extends Controller
{
    use ResetsPasswords;

    public function __construct(IMerchantRepository $merchant)
    {
        $this->merchant = $merchant;
    }

    public function login(LoginRequest $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required', 'type' => 'sometimes']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $data = $validator->validated();
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            abort(response()->json($this->getErrorMessages(['message' => trans('auth.failed')]), 200));
        }
        if ($user && Hash::check($request->password, $user->getAuthPassword())) {
            if ($user->hasRole('investor') || $user->hasRole('merchant')) {
                if ($user && $user->two_factor_secret) {
                    $login_id = $user->getKey();
                    abort(response()->json([
                      'data'       => ['role'=>optional($user->roles()->first()->toArray())['name'] ?? ''],
                      'status'     => true,
                      'two_factor' => true,
                      'login_id'   => $login_id
                    ], 200));
                }
                event(new LoginEvent('api', $user, false));
                $user->current_merchant_id = 0;
                $user->update();

                return new UserResource($user);
            } else {
                abort(response()->json($this->getErrorMessages(['message' => trans('auth.failed')]), 200));
            }
        } else {
            abort(response()->json($this->getErrorMessages(['message' => trans('auth.failed')]), 200));
        }
    }

    public function fundings_login(Request $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'required']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $data = $validator->validated();
        $user_exist = User::join('user_has_roles', 'users.id', '=', 'user_has_roles.model_id');
        $user_exist = $user_exist->join('roles', 'roles.id', '=', 'user_has_roles.role_id')->where('users.email', decrypt($request->email))->where(function ($q) {
            $q->whereIn('roles.id', [User::OVERPAYMENT_ROLE,User::AGENT_FEE_ROLE]);
        })->first();
        if ($user_exist) {
            abort(response()->json($this->getErrorMessages(['message' => trans('auth.failed')]), 200));
        }
        $user = User::where('email', decrypt($request->email))->first();
        $user->current_merchant_id = 0;
        $user->update();

        return new UserResource($user);
    }

    public function loginUserById(int $userId, string $token)
    {
        $user = User::where('id', $userId)->first();
        if ($user && $token == $user->getDownloadToken()) {
            Auth::login($user, true);

            return true;
        }
        abort(response()->json($this->getErrorMessages(['message' => trans('auth.failed')]), 200));
    }

    public function getErrorMessages($errors)
    {
        return ['status' => false, 'errors' => $errors];
    }

    public function getMessages()
    {
        return ['status' => true, 'two_factor' => true];
    }

    public function check(Request $request)
    {
        return new UserResource($request->user(), null);
    }

    public function token(Request $request)
    {
        $user = $request->user();
        $token = collect($user->sanctumTokens)->first();

        return new UserResource($user, $token->plainTextToken);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $token = $user->getAccessToken(true);

        return new UserResource($user);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->current_merchant_id = 0;
        $user->update();

        return new SuccessResource(['message' => 'User has been Logged out successfully!']);
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'required|email|unique:users', 'password' => 'required', 'name' => 'required|unique:users']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $type = $request->type ?? 'investor';
        if ($type == 'investor') {
            $request->merge(['creator_id' => 1, 'investor_type' => isset($request->investor_type) ? $request->investor_type : 5, 'global_syndication' => isset($request->global_syndication) ? $request->global_syndication : 0, 'management_fee' => isset($request->management_fee) ? $request->management_fee : 0, 's_prepaid_status' => isset($request->s_prepaid_status) ? $request->s_prepaid_status : 2, 'interest_rate' => isset($request->interest_rate) ? $request->interest_rate : 0.5, 'notification_email' => isset($request->notification_email) ? $request->notification_email : 'testmail@vgusa.com', 'notification_recurence' => isset($request->notification_recurence) ? $request->notification_recurence : 1, 'cell_phone' => isset($request->cell_phone) ? $request->cell_phone : null, 'company' => isset($company) ? $company->id : 284, 'active_status' => isset($request->active_status) ? $request->active_status : 1, 'file_type' => isset($request->file_type) ? $request->file_type : 1, 'email_notification' => 1, 'phone' => isset($request->phone) ? $request->phone : null, 'auto_generation' => 1, 'create_mode' => 'mob']);
            $investorController = new InvestorController($this->merchant);
            if ($investor = $investorController->postCreate($request)) {
                $investor->sanctumTokens()->delete();
                $token = $investor->createSanctumToken($request->device_name.'-'.$investor->email);

                return new UserResource($investor, $token->plainTextToken);
            }
        }

        return response()->json($this->getErrorMessages(['message' => 'Oops!, Something is missing']), 200);
    }

    public function update(Request $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'unique:users,email,'.$request->id, 'position' => 'nullable', 'password' => 'sometimes']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $data = $validator->validated();
        $user = $request->user();
        $user->name = isset($request->name) ? $request->name : $user->name;
        $user->email = isset($request->email) ? $request->email : $user->email;
        if (isset($data['password']) and ! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->update();

        return new UserResource($user, null);
    }

    public function postResetPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $data = $validator->validated();
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $token = Str::random(60);
            $tokenData = \DB::table('password_resets')->where('email', $data['email'])->first();
            if (! $tokenData) {
                DB::table('password_resets')->insert(['email' => $data['email'], 'token' => Hash::make($token), 'created_at' => Carbon::now()]);
            } else {
                DB::table('password_resets')->where('email', $data['email'])->update(['token' => Hash::make($token), 'created_at' => Carbon::now()]);
            }
            Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                $message->to($user->email, $user->first_name.' '.$user->last_name);
                $message->subject('Reset Password');
            });

            return response()->json(['status' => true, 'message' => 'success']);
        }

        return abort(response()->json('Not found', 404));
    }

    public function postResetEmail(Request $request)
    {
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json($this->getErrorMessages($validator->messages()), 422);
        }
        $response = $this->broker()->reset($this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        });
        if ($response == Password::PASSWORD_RESET) {
            return new UserResource($request->user(), null);
        }
        abort(response()->json($this->getErrorMessages(['message' => trans($response)]), 422));
    }

    public function postTwoFactorChallenge(TwoFactorLoginRequest $request)
    {
        $login_id = $request->login_id;
        $user = User::where('id', $login_id)->first();
        $two_factor_secret = $user->two_factor_secret;
        session::put('logid', $login_id);
        $verify = app(TwoFactorAuthenticationProvider::class)->verify(decrypt($two_factor_secret), $request->code);
        if ($verify) {
            return new UserResource($user);
        } else {
            abort(response()->json($this->getErrorMessages(['message' => 'The provided two factor authentication code was invalid']), 200));
        }
    }

    public function postLoginByRecoveryCode(Request $request)
    {
        $login_id = $request->login_id;
        if ($code = $this->validRecoveryCode($request->recovery_code, $login_id)) {
            $user = User::where('id', $login_id)->first();
            $user->replaceRecoveryCode($code);

            return new UserResource($user);
        } else {
            abort(response()->json($this->getErrorMessages(['message' => 'The provided two factor authentication recovery code was invalid']), 200));
        }
    }

    public function validRecoveryCode($recovery_code, $login_id)
    {
        if (! $recovery_code) {
            abort(response()->json($this->getErrorMessages(['message' => 'Enter valid recovery code']), 200));
        }
        $user = User::where('id', $login_id)->first();

        return collect($user->recoveryCodes())->first(function ($code) use ($recovery_code) {
            return hash_equals($recovery_code, $code) ? $code : null;
        });
    }
}
