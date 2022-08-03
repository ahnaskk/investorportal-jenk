<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\Report\AccruedInterestReportHelper;
use App\Helpers\Report\AnticipatedPaymentReportHelper;
use App\Helpers\Report\DebitInvestorReportHelper;
use App\Helpers\Report\DefaultRateReportHelper;
use App\Helpers\Report\DelinquentRateReportHelper;
use App\Helpers\Report\EquityInvestorReportHelper;
use App\Helpers\Report\InvestmentReportHelper;
use App\Helpers\Report\LiquidityReportHelper;
use App\Helpers\Report\MerchantsPerDiffReportHelper;
use App\Helpers\Report\OverpaymentReportHelper;
use App\Helpers\Report\PaymentReportHelper;
use App\Helpers\Report\PortfolioEarningReportHelper;
use App\Helpers\Report\ProfitReportHelper;
use App\Helpers\Report\TransactionReportHelper;
use App\Helpers\Report\VelocityProfitReportHelper;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Merchant;
use Illuminate\Http\Request;

class ReportController extends AdminAuthController
{
    public function postDefaultRate(Request $request)
    {
        return DefaultRateReportHelper::getReport($request);
    }

    public function postDefaultRateColumns(Request $request)
    {
        $columns = DefaultRateReportHelper::getTableColumns();

        return new SuccessResource(['data' => $columns]);
    }

    public function postDefaultRateDownload(Request $request)
    {
        DefaultRateReportHelper::getDownload($request);
    }

    public function postDefaultRateMerchant(Request $request)
    {
        return DefaultRateReportHelper::getMerchantReport($request);
    }

    public function postDefaultRateMerchantColumns(Request $request)
    {
        $columns = DefaultRateReportHelper::getMerchantTableColumns();

        return new SuccessResource(['data' => $columns]);
    }

    public function postDefaultRateMerchantDownload(Request $request)
    {
        DefaultRateReportHelper::getMerchantDownload($request);
    }

    public function postDelinquent(Request $request)
    {
        return DelinquentRateReportHelper::getReport($request);
    }

    public function postDelinquentColumns()
    {
        $columns = DelinquentRateReportHelper::getTableColumns();

        return new SuccessResource(['data' => $columns]);
    }

    public function postDelinquentDownload(Request $request)
    {
        DelinquentRateReportHelper::getDownload($request);
    }

    public function postPaymentLeftColumns()
    {
        $columns = PaymentReportHelper::getPaymentLeftReportColumns();

        return new SuccessResource(['data' => $columns]);
    }

    public function postPaymentLeft(Request $request)
    {
        return PaymentReportHelper::getPaymentLeftReport($request);
    }

    public function postDelinquentLenderColumns()
    {
        $columns = DelinquentRateReportHelper::getLenderReportColumns();

        return new SuccessResource(['data' => $columns]);
    }

    public function postDelinquentLender(Request $request)
    {
        return DelinquentRateReportHelper::getLenderReport($request);
    }

    public function postProfitability2(Request $request)
    {
        return ProfitReportHelper::getProfit2Report($request);
    }

    public function postProfitability2Columns(Request $request)
    {
        return new SuccessResource(['data' => ProfitReportHelper::getProfit2Columns()]);
    }

    public function postProfitability2Download(Request $request)
    {
        ProfitReportHelper::profit2ReportDownload($request);
    }

    public function postProfitability3(Request $request)
    {
        return ProfitReportHelper::getProfit3Report($request);
    }

    public function postProfitability3Columns(Request $request)
    {
        return new SuccessResource(['data' => ProfitReportHelper::getProfit3Columns()]);
    }

    public function postProfitability3Download(Request $request)
    {
        ProfitReportHelper::profit3ReportDownload($request);
    }

    public function postProfitability4(Request $request)
    {
        return ProfitReportHelper::getProfit4Report($request);
    }

    public function postProfitability4Columns(Request $request)
    {
        return new SuccessResource(['data' => ProfitReportHelper::getProfit4Columns()]);
    }

    public function postProfitability4Download(Request $request)
    {
        ProfitReportHelper::profit4ReportDownload($request);
    }

    public function postInvestment(Request $request)
    {
        return InvestmentReportHelper::getReport($request);
    }

    public function postInvestmentColumns(Request $request)
    {
        return new SuccessResource(['data' => InvestmentReportHelper::getTableColumns()]);
    }

    public function postInvestmentDownload(Request $request)
    {
        InvestmentReportHelper::downloadReport($request);
    }

    public function postInvestorAssignment(Request $request)
    {
        return InvestmentReportHelper::getAssignmentReport($request);
    }

    public function postInvestorAssignmentColumns(Request $request)
    {
        return new SuccessResource(['data' => InvestmentReportHelper::getAssignmentColumns()]);
    }

    public function postInvestorAssignmentDownload(Request $request)
    {
        InvestmentReportHelper::downloadAssignmentReport($request);
    }

    public function postInvestorReAssignment(Request $request)
    {
        return InvestmentReportHelper::getReAssignmentReport($request);
    }

    public function postInvestorReAssignmentColumns(Request $request)
    {
        return new SuccessResource(['data' => InvestmentReportHelper::getReAssignmentColumns()]);
    }

    public function postInvestorReAssignmentDownload(Request $request)
    {
        InvestmentReportHelper::downloadReAssignmentReport($request);
    }

    public function postLiquidity(Request $request)
    {
        return LiquidityReportHelper::getReport($request);
    }

    public function postLiquidityColumns(Request $request)
    {
        return new SuccessResource(['data' => LiquidityReportHelper::getColumns()]);
    }

    public function postLiquidityDownload(Request $request)
    {
        LiquidityReportHelper::downloadReport($request);
    }

    public function postPayment(Request $request)
    {
        return PaymentReportHelper::getReport($request);
    }

    public function postPaymentColumns()
    {
        return new SuccessResource(['data' => PaymentReportHelper::getColumns()]);
    }

    public function postPaymentDownload(Request $request)
    {
        PaymentReportHelper::downloadReport($request);
    }

    public function postTransaction(Request $request)
    {
        return TransactionReportHelper::getReport($request);
    }

    public function postTransactionColumns()
    {
        return new SuccessResource(['data' => TransactionReportHelper::getColumns()]);
    }

    public function postTransactionDownload(Request $request)
    {
        TransactionReportHelper::downloadReport($request);
    }

    public function postAccruedInterest(Request $request)
    {
        return AccruedInterestReportHelper::getReport($request);
    }

    public function postAccruedInterestColumns()
    {
        return new SuccessResource(['data' => AccruedInterestReportHelper::getColumns()]);
    }

    public function postAccruedInterestDownload(Request $request)
    {
        AccruedInterestReportHelper::downloadReport($request);
    }

    public function postDebitInvestor(Request $request)
    {
        return DebitInvestorReportHelper::getReport($request);
    }

    public function postDebitInvestorColumns()
    {
        return new SuccessResource(['data' => DebitInvestorReportHelper::getColumns()]);
    }

    public function postDebitInvestorDownload(Request $request)
    {
        DebitInvestorReportHelper::reportDownload($request);
    }

    public function postEquityInvestor(Request $request)
    {
        return EquityInvestorReportHelper::getReport($request);
    }

    public function postEquityInvestorColumns()
    {
        return new SuccessResource(['data' => EquityInvestorReportHelper::getColumns()]);
    }

    public function postEquityInvestorDownload(Request $request)
    {
        EquityInvestorReportHelper::downloadReport($request);
    }

    public function postOverpaymentColumns()
    {
        return new SuccessResource(['data' => OverpaymentReportHelper::getColumns()]);
    }

    public function postOverpayment(Request $request)
    {
        return OverpaymentReportHelper::getReport($request);
    }

    public function postOverpaymentDownload(Request $request)
    {
        OverpaymentReportHelper::downloadReport($request);
    }

    public function postVelocityProfitability(Request $request)
    {
        return VelocityProfitReportHelper::getReport($request);
    }

    public function postVelocityProfitabilityColumns(Request $request)
    {
        return new SuccessResource(['data' => VelocityProfitReportHelper::getColumns()]);
    }

    public function postVelocityProfitabilityDownload(Request $request)
    {
        VelocityProfitReportHelper::downloadReport($request);
    }

    public function postMerchantPerDiff(Request $request)
    {
        return MerchantsPerDiffReportHelper::getReport($request);
    }

    public function postMerchantPerDiffColumns(Request $request)
    {
        return new SuccessResource(['data' => MerchantsPerDiffReportHelper::getColumns()]);
    }

    public function postMerchantPerDiffDownload(Request $request)
    {
        MerchantsPerDiffReportHelper::downloadReport($request);
    }

    public function postAnticipatedPayment(Request $request)
    {
        return AnticipatedPaymentReportHelper::getReport($request);
    }

    public function postAnticipatedPaymentColumns(Request $request)
    {
        return new SuccessResource(['data' => AnticipatedPaymentReportHelper::getColumns()]);
    }

    public function postAnticipatedPaymentDownload(Request $request)
    {
        AnticipatedPaymentReportHelper::downloadReport($request);
    }

    public function postPortfolioEarning(Request $request)
    {
        return PortfolioEarningReportHelper::getReport($request);
    }

    public function postPortfolioEarningColumns(Request $request)
    {
        return new SuccessResource(['data' => PortfolioEarningReportHelper::getColumns()]);
    }

    public function postPortfolioEarningDownload(Request $request)
    {
        PortfolioEarningReportHelper::getDownload($request);
    }
}
