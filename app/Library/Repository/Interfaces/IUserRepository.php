<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 3/11/17
 * Time: 11:47 AM.
 */

namespace App\Library\Repository\Interfaces;

interface IUserRepository
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function createInvestor(\Illuminate\Http\Request $request);

    public function createAccount(\Illuminate\Http\Request $request);

    public function createCollectionUser(\Illuminate\Http\Request $request);

    public function findInvestor($id);

    public function findAccount($id);

    public function updateInvestor($id, $req);

    public function deleteInvestor($id);

    public function equityInvestorReport($investors);

    public function totalPortfolioEarnings($investors);

    public function accuredInterestReport($investors);

    public function investorList($investor_type, $velocity, $active_status, $active_status_companies, $liquidity, $auto_invest, $role_id);

    public function investorProfitReport($investors);

    public function lenderReport();

    public function investorDashboard($userId, $investor_type);

    public function duplicateDbGenerate(\Illuminate\Http\Request $request);

    // public function activityLogger($log_array);

    public function generatePDFCSV($investors, $filters);
}
