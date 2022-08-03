<?php

namespace App\Helpers;

use App\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\User;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\UserDetails;
use FFM;

class CompanyHelper
{
    public function __construct(IRoleRepository $role)
    {        
        $this->role = $role;        
    }
  public static function getCompanyIds(Request $request)
    {
        $label = $request->label;
        $account_filter = $request->account_filter;
        $companyIds = $request->input('company');
        $companyIds = (! is_array($companyIds)) ? [] : $companyIds;
        if (count($companyIds) < 1) {
            $companyIds = User::getAllCompanies()->pluck('users.id')->toArray();
        }
        if (Auth::user()->hasRole(['company'])) {
            $companyIds = [];
        }
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $userId = Auth::user()->id;
        $setInvestors = [];
        if ($account_filter != 'overpayment') {
            $investorQuery = Role::whereName('investor')->first()->users();
            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $investorQuery->where('company', $userId);
                } else {
                    $investorQuery->where('creator_id', $userId);
                }
            }
            if (is_array($companyIds) and count($companyIds) > 0) {
                $investorQuery->whereIn('company', $companyIds);
            }
            if ($account_filter != null) {
                if ($account_filter == 'disabled') {
                    $investorQuery->where('active_status', 0);
                }
                if ($account_filter == 'enabled') {
                    $investorQuery->where('active_status', 1);
                }
            }
            $setInvestors = $investorQuery->pluck('users.id')->toArray();
        }
        if ($account_filter == 'overpayment' || $account_filter == null) {
            $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
            $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
            if (empty($permission)) {
                if (Auth::user()->hasRole(['company'])) {
                    $OverpaymentAccount->where('company', $userId);
                } else {
                    $OverpaymentAccount->where('creator_id', $userId);
                }
            }
            if (is_array($companyIds) and count($companyIds) > 0) {
                $OverpaymentAccount->whereIn('company', $companyIds);
            }
            $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
            if ($OverpaymentAccount) {
                $setInvestors[] = $OverpaymentAccount->id;
            }
        }
        $merchantQuery = Merchant::where('active_status', 1);
        if ($label) {
            $merchantQuery = $merchantQuery->where('label', $label);
        }
        if (empty($permission) || count($companyIds) > 0) {
            $merchantQuery->whereHas('investors', function ($inner) use ($setInvestors) {
                $inner->whereIn('user_id', $setInvestors);
            });
        }
        $merchantIds = $merchantQuery->distinct()->pluck('id')->toArray();

        return [$companyIds, $setInvestors, $merchantIds];
    }
    public function getCompanyDetails($request,$companyIds,$setInvestors,$merchantIds){
        $investorIds = $this->role->allInvestors()->pluck('id')->toArray();
        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
        if ($OverpaymentAccount) {
            $investorIds[] = $OverpaymentAccount->id;
        }
        $liquidityQuery = UserDetails::join('users', 'users.id', '=', 'user_details.user_id')->whereIn('user_id', $setInvestors);
        if (empty($permission) || count($companyIds) > 0) {
            $liquidityQuery->whereIn('user_id', $setInvestors);
        }
        $cash_in_hands = $liquidityQuery->sum('liquidity');
        $liquidity = $this->role->companyWiseInvestorsLiquidity($companyIds, $setInvestors);
        $subAdminLiquidity = [];
        if (! empty($liquidity)) {
            foreach ($liquidity as $key => $value) {
                $velocity = 0;
                if ($cash_in_hands != 0) {
                    $velocity = 100 - ($cash_in_hands - $value['liquidity']) / $cash_in_hands * 100;
                }
                $subAdminLiquidity[] = [
                    'liquidity' => FFM ::dollar($value['liquidity']),
                    'name'      => User::where('id', $value['company'])->value('name'),
                    'velocity'  => FFM ::percent($velocity)
                ];
            }
        }
        return $subAdminLiquidity;
    }


}
