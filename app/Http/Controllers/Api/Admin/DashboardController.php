<?php

namespace App\Http\Controllers\Api\Admin;

use App\Bank;
use App\BankDetails;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankAccountRequest;
use App\Http\Resources\SuccessResource;
use App\Merchant;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class DashboardController extends AdminAuthController
{
    public function postIndex(Request $request)
    {
        list($companyIds, $setInvestors, $merchantIds) = $this->getCompanyIds($request);
        $permission = ($request->user()->hasRole(['company'])) ? 0 : 1;
        $pactolus_distribution = 0;
        $investor_distribution = 0;
        $velocity_distribution = 0;
        $portfolio_difference = 0;
    }

    public function postCompanies()
    {
    }

    private function getCompanyIds(Request $request)
    {
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
        $setInvestors = $investorQuery->pluck('users.id')->toArray();
        $merchantQuery = Merchant::where('active_status', 1);
        if (empty($permission) || count($companyIds) > 0) {
            $merchantQuery->whereHas('investors', function ($inner) use ($setInvestors) {
                $inner->whereIn('user_id', $setInvestors);
            });
        }
        $merchantIds = $merchantQuery->distinct()->pluck('id')->toArray();

        return [$companyIds, $setInvestors, $merchantIds];
    }
}
