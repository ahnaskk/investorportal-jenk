<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:12 AM.
 */

namespace App\Library\Repository\Interfaces;

interface IMerchantRepository
{
    public function getAll($fields = null);

    public function datatable($fields = null);

    public function investorDatatable($userId, $fields);

    public function createRequest($request);

    public function updateRequest($request);

    public function paymentUpdateRequest($request);

    public function generatePayment($merchant);

    public function searchForGeneralReport($sdate, $edate, $id);

    public function searchForPaymentReport($sdate, $edate, $id);

    public function searchForPaymentReportApi($sdate, $edate, $rcode, $userIds);

    public function searchForMerchantsPerDiffReport();

    public function modify_payments($merchant_id);

    public function modify_payments1($merchant_id);

    // test interface

    // public function searchForPaymentReport_test($sdate, $edate, $id);

    public function find($id);

    public function delete($id);

    public function findIfBelongsToUser($merchantId, $userId);

    public function countMerchants();

    public function searchForInvestorReport($date_type, $advance_type, $lenders, $startDate, $endDate, $investors, $merchants);

    public function searchForInvestorAssignmentReport($startDate, $endDate, $investors, $merchants);

    public function getCreatorId($merchant_id);

    public function merchant_details($merchant_id, $company_id, $investor_id);

    public function delinquentRateReport($lenders, $industry, $company, $from_date, $to_date, $sub_status, $funded_date);

    public function searchForProfitCarryForward($startDate, $endDate, $investors, $merchants, $type);
}
