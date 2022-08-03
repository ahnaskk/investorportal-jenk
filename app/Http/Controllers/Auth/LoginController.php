<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordLoginRequest;
use App\Library\Repository\Interfaces\IUserRepository;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $adminDashPath = '/admin/dashboard';
    protected $redirectTo = '/admin/dashboard';
    protected $investorDashPath = '/investors/dashboard';
    protected $BranchDashPath = '/branch/dashboard';
    protected $LenderDashPath = '/lender/dashboard';

    public function __construct(IUserRepository $user)
    {
        $this->user = $user;
        $this->middleware('guest')->except('logout');

        return redirect()->to('/login');
    }

    protected function authenticated($request, $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('lender') || $user->hasRole('editor') || $user->hasRole('viewer')) {
            return redirect()->to($this->adminDashPath);
        } elseif ($user->hasRole('branch manager')) {
            return redirect()->to($this->BranchDashPath);
        } elseif ($user->hasRole('lender')) {
            return redirect()->to($this->LenderDashPath);
        } elseif ($user->hasRole('company')) {
            return redirect()->to($this->adminDashPath);
        } elseif ($user->hasRole('investor')) {
            $funding_status = User::where('id', $user->id)->value('funding_status');
            if ($funding_status == 0) {
                $this->guard()->logout();

                return redirect()->to('/login')->withErrors('Not approved yet');
            } else {
                return redirect()->to($this->investorDashPath);
            }
        } else {
            return redirect()->to($this->BranchDashPath);
        }

        return redirect()->intended($this->investorDashPath);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->loggedOut($request);

        return $this->loggedOut($request) ?: redirect()->to(url('login'));
    }

    public function loginByRecoveryKey()
    {
        return view('auth.login_recovery_key');
    }
}
