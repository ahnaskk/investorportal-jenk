<?php

use App\Http\Controllers\Admin\UserActivityLogController;
use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Admin\FilterController;
use App\Http\Controllers\Api\Admin\MerchantLogController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\StatementController;
use App\Http\Controllers\Api\Investor\DownloadController;
use App\Http\Controllers\Api\InvestorController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PusherController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VisitLog;
//use App\Events\TwoFactorMandatoryUpdation;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/**
 * Routes
 */

//  Route::get('test-pusher',function(){
//      broadcast(new TwoFactorMandatoryUpdation(['two_factor_mandatory_status'=>true]));
//  });
Route::prefix('auth')->group(function () {
    Route::post('login', [Auth\AuthController::class, 'login']);
    Route::post('fundings/login', [Auth\AuthController::class, 'fundings_login']);
    Route::post('register', [Auth\AuthController::class, 'register']);
    Route::post('reset-password', [Auth\AuthController::class, 'postResetPassword']);
    Route::post('password/email', [Auth\AuthController::class, 'postResetEmail']);
    Route::post('two-factor-challenge', [Auth\AuthController::class, 'postTwoFactorChallenge']);
    Route::post('login-by-recovery-code', [Auth\AuthController::class, 'postLoginByRecoveryCode']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->namespace('Admin')->group(function () {
        Route::get('/activity-log/records', [UserActivityLogController::class, 'getRecords']);
    });
    Route::prefix('auth')->group(function () {
        Route::get('check', [Auth\AuthController::class, 'check']);
        Route::post('token-check', [Auth\AuthController::class, 'token']);
        Route::post('token', [Auth\AuthController::class, 'token']);
        Route::post('revoke-token', [Auth\AuthController::class, 'refreshToken']);
        Route::post('logout', [Auth\AuthController::class, 'logout']);
        Route::post('update', [Auth\AuthController::class, 'update']);
    });

    Route::prefix('merchant')->namespace('Api')->group(function () {
        Route::post('merchant-details', [MerchantController::class, 'postMerchantDetails']);

        Route::get('beamsToken', [PusherController::class, 'getBeamsToken']);
        Route::get('beams-token', [PusherController::class, 'getBeamsToken']);

        Route::post('payments', [MerchantController::class, 'postPayments']);

        Route::post('merchant-graph', [MerchantController::class, 'postMerchantGraph']);

        Route::post('latest-payments', [MerchantController::class, 'postLatestPayments']);

        Route::post('requestMoreMoney', [MerchantController::class, 'postRequestMoreMoney']);
        Route::post('request-more-money-status-update', [MerchantController::class, 'postRequestMoreMoneyStatusUpdate']);
       Route::post('call-crm-api', [MerchantController::class, 'postCallCrmApi']);
       Route::post('merchant-money-requests', [MerchantController::class, 'postMerchantMoneyRequests']);
       
        
        Route::post('credit-card-payment', [MerchantController::class, 'postCreditCardPayment']);
        Route::post('requestPayOff', [MerchantController::class, 'postRequestPayOff']);

        Route::post('request-more-money', [MerchantController::class, 'postRequestMoreMoney']);
        Route::post('request-pay-off', [MerchantController::class, 'postRequestPayOff']);

        Route::post('statements', [MerchantController::class, 'postStatements']);
        Route::post('change-merchant', [MerchantController::class, 'postChangeMerchant']);

        Route::post('read-update', [NotificationController::class, 'postReadUpdate']);
        Route::post('notification-list', [NotificationController::class, 'postList']);
        Route::post('notification-count', [NotificationController::class, 'postCount']);
        Route::post('check-two-factor', [MerchantController::class, 'postCheckTwoFactor']);
        Route::post('disable-two-factor', [MerchantController::class, 'postDisableTwoFactor']);
        Route::post('enable-two-factor-details', [MerchantController::class, 'postEnableTwoFactorDetails']);
        Route::post('connect-phone', [MerchantController::class, 'postConnectPhone']);
        Route::post('faq/{app?}', [MerchantController::class, 'postFaqApp']);
    });

    Route::prefix('investor')->namespace('Api')->group(function () {
        Route::post('create', [InvestorController::class, 'postCreate']);
        Route::get('beams-token', [PusherController::class, 'getBeamsToken']);
        Route::get('edit-investor', [InvestorController::class, 'getEditInvestor']);
        Route::get('two-factor-mandatory', [InvestorController::class, 'getTwoFactorMandatory']);
        Route::post('update-investor', [InvestorController::class, 'postUpdateInvestor']);
        Route::get('graph-filter', [InvestorController::class, 'getGraphFilter']);
        Route::post('chart-values', [InvestorController::class, 'postChartValues']);
        Route::post('download-chart', [InvestorController::class, 'postDownloadChart']);

        Route::post('read-update', [NotificationController::class, 'postReadUpdate']);
        Route::post('dashboard', [InvestorController::class, 'postDashboard'])->middleware(VisitLog::class);
        Route::post('investment-waterflow', [InvestorController::class, 'postAdvancePlusInvestments']);
        Route::post('notification-list', [NotificationController::class, 'postList']);
        Route::post('notification-count', [NotificationController::class, 'postCount']);
        Route::post('clear-notifications', [NotificationController::class, 'postClearNotifications']);
        Route::post('banks', [InvestorController::class, 'postBanks']);
        Route::post('bank-create', [InvestorController::class, 'postBankCreate']);
        Route::post('bank-update', [InvestorController::class, 'postBankUpdate']);
        Route::post('bank-update/{id}', [InvestorController::class, 'postBankUpdate']);
        Route::post('bank-delete', [InvestorController::class, 'postBankDelete']);
        Route::post('bank-default-update/{id}', [InvestorController::class, 'postDefaultBankUpdate']);

        Route::post('payment-report', [InvestorController::class, 'postPaymentReport']);
        Route::post('investment-report', [InvestorController::class, 'postInvestmentReport']);
        Route::post('transaction-report', [InvestorController::class, 'postTransactionReport']);
        Route::post('default-rate-merchant-report', [InvestorController::class, 'postDefaultRateMerchantReport']);

        Route::post('statement', [InvestorController::class, 'postStatement']);
        Route::post('investor-merchant-list', [InvestorController::class, 'postInvestorMerchantList']);
        Route::post('investor-merchant-view', [InvestorController::class, 'postInvestorMerchantView']);
        Route::post('investor-merchant-payment-view', [InvestorController::class, 'postInvestorMerchantPaymentView']);
        Route::post('faq', [InvestorController::class, 'postFaq']);
        Route::post('faq/{app?}', [InvestorController::class, 'postFaqApp']);
        Route::post('merchants-list', [InvestorController::class, 'postMerchantsList']);
        Route::post('investor-chart', [InvestorController::class, 'postInvestorChart']);
        Route::post('check-two-factor', [InvestorController::class, 'postCheckTwoFactor']);
        Route::post('enable-two-factor-details', [InvestorController::class, 'postEnableTwoFactorDetails']);
        Route::post('connect-phone', [InvestorController::class, 'postConnectPhone']);
        Route::post('disable-two-factor', [InvestorController::class, 'postDisableTwoFactor']);
        Route::post('investor-ach-request-send', [InvestorController::class, 'postInvestorAchRequestSend']);
        Route::post('marketplace', [InvestorController::class, 'postMarketplace']);
        Route::post('marketplace-filters', [InvestorController::class, 'postMarketplaceFilters']);
        Route::post('marketplace-fund', [InvestorController::class, 'postMarketplaceFund']);
        Route::post('marketplace-documents', [InvestorController::class, 'postMarketplaceDocuments']);
        Route::post('investor-details', [InvestorController::class, 'postInvestorDetails']);
        Route::post('transaction-details', [InvestorController::class, 'postTransactionDetails']);
        Route::post('update-user', [InvestorController::class, 'postUpdateUser']);

        Route::post('download-investor-merchant-list', [InvestorController::class, 'postDownloadInvestorMerchantList']);
        Route::post('sub-status-list', [InvestorController::class, 'postSubStatusList']);
        Route::post('merchant-filter', [InvestorController::class, 'postMerchantFilter']);
        Route::post('report', [InvestorController::class, 'postReport']);
        Route::post('payment-report-details', [InvestorController::class, 'postPaymentReportDetails']);
        Route::post('collection-notes', [InvestorController::class, 'collectionNotes']);
    });
    Route::get('investor/beamsToken', [Api\PusherController::class, 'getBeamsToken']);
});

Route::middleware('auth:sanctum')->prefix('admin')->namespace('Api')->group(function () {
    Route::namespace('Admin')->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('re-assign', [UserController::class, 'getReAssign']);
            Route::post('re-assign', [UserController::class, 'postReAssign']);
            Route::get('investors', [UserController::class, 'getInvestors']);
            Route::get('assigned-investors', [UserController::class, 'getAssignedInvestors']);
            Route::get('investor-for-owner', [UserController::class, 'getInvestorsForOwner']);
            Route::get('company-wise-investors', [UserController::class, 'getCompanyWiseInvestors']);
            Route::get('all-investors', [UserController::class, 'getAllInvestors']);
            Route::get('merchants', [UserController::class, 'getMerchants']);
            Route::get('investor-admins', [UserController::class, 'getInvestorAdmins']);
            Route::get('companies', [UserController::class, 'getCompanies']);
            Route::get('percentage-deal-graph', [UserController::class, 'getPercentageDealGraph']);
            Route::post('pie-chart-values', [UserController::class, 'postPieChartValues']);
            Route::post('merchant-status-data', [UserController::class, 'postMerchantStatusData']);
            Route::post('merchant-status', [UserController::class, 'postMerchantStatus']);
        });

        Route::controllers([
           // 'bank' => 'BankController',
        ]);

        Route::prefix('merchant-log')->group(function () {
            Route::get('status', [MerchantLogController::class, 'getStatus']);
            Route::post('investor-mail', [StatementController::class, 'postInvestorMail']);
        });

        Route::prefix('statement')->group(function () {
            Route::post('delete', [StatementController::class, 'postDelete']);
            Route::post('investor-mail', [StatementController::class, 'postInvestorMail']);
        });

        Route::prefix('filter')->group(function () {
            Route::get('merchant', [FilterController::class, 'getMerchant']);
            Route::get('investor', [FilterController::class, 'getInvestor']);
            Route::get('lender', [FilterController::class, 'getLender']);
            Route::get('sub-status', [FilterController::class, 'getSubStatus']);
            Route::get('advance-type', [FilterController::class, 'getAdvanceType']);
            Route::get('company', [FilterController::class, 'getCompany']);
            Route::get('investor-type', [FilterController::class, 'getInvestorType']);
            Route::get('rcode', [FilterController::class, 'getRcode']);
            Route::get('transaction-type', [FilterController::class, 'getTransactionType']);
            Route::get('transaction-category', [FilterController::class, 'getTransactionCategory']);
            Route::get('label', [FilterController::class, 'getLabel']);
            Route::get('industry', [FilterController::class, 'getIndustry']);
            Route::get('sub-status-flag', [FilterController::class, 'getSubStatusFlag']);
            Route::get('overpayment', [FilterController::class, 'getOverpayment']);
            Route::get('days', [FilterController::class, 'getDays']);
        });

        Route::prefix('report')->group(function () {
            Route::post('default-rate', [ReportController::class, 'postDefaultRate']);
            Route::post('default-rate-columns', [ReportController::class, 'postDefaultRateColumns']);

            Route::post('default-rate-merchant', [ReportController::class, 'postDefaultRateMerchant']);
            Route::post('default-rate-merchant-columns', [ReportController::class, 'postDefaultRateMerchantColumns']);

            Route::post('delinquent', [ReportController::class, 'postDelinquent']);
            Route::post('delinquent-columns', [ReportController::class, 'postDelinquentColumns']);

            Route::post('payment-left', [ReportController::class, 'postPaymentLeft']);
            Route::post('payment-left-columns', [ReportController::class, 'postPaymentLeftColumns']);

            Route::post('delinquent-lender', [ReportController::class, 'postDelinquentLender']);
            Route::post('delinquent-lender-columns', [ReportController::class, 'postDelinquentLenderColumns']);

            Route::post('profitability2', [ReportController::class, 'postProfitability2']);
            Route::post('profitability2-columns', [ReportController::class, 'postProfitability2Columns']);

            Route::post('profitability3', [ReportController::class, 'postProfitability3']);
            Route::post('profitability3-columns', [ReportController::class, 'postProfitability3Columns']);

            Route::post('profitability4', [ReportController::class, 'postProfitability4']);
            Route::post('profitability4-columns', [ReportController::class, 'postProfitability4Columns']);

            Route::post('investment', [ReportController::class, 'postInvestment']);
            Route::post('investment-columns', [ReportController::class, 'postInvestmentColumns']);

            Route::post('investor-assignment', [ReportController::class, 'postInvestorAssignment']);
            Route::post('investor-assignment-columns', [ReportController::class, 'postInvestorAssignmentColumns']);

            Route::post('investor-re-assignment', [ReportController::class, 'postInvestorReAssignment']);
            Route::post('investor-re-assignment-columns', [ReportController::class, 'postInvestorReAssignmentColumns']);

            Route::post('liquidity', [ReportController::class, 'postLiquidity']);
            Route::post('liquidity-columns', [ReportController::class, 'postLiquidityColumns']);

            Route::post('payment', [ReportController::class, 'postPayment']);
            Route::post('payment-columns', [ReportController::class, 'postPaymentColumns']);

            Route::post('transaction', [ReportController::class, 'postTransaction']);
            Route::post('transaction-columns', [ReportController::class, 'postTransactionColumns']);

            Route::post('accrued-interest', [ReportController::class, 'postAccruedInterest']);
            Route::post('accrued-interest-columns', [ReportController::class, 'postAccruedInterestColumns']);

            Route::post('debit-investor', [ReportController::class, 'postDebitInvestor']);
            Route::post('debit-investor-columns', [ReportController::class, 'postDebitInvestorColumns']);

            Route::post('equity-investor', [ReportController::class, 'postEquityInvestor']);
            Route::post('equity-investor-columns', [ReportController::class, 'postEquityInvestorColumns']);

            Route::post('portfolio-earning', [ReportController::class, 'postPortfolioEarning']);
            Route::post('portfolio-earning-columns', [ReportController::class, 'postPortfolioEarningColumns']);

            Route::post('overpayment', [ReportController::class, 'postOverpayment']);
            Route::post('overpayment-columns', [ReportController::class, 'postOverpaymentColumns']);

            Route::post('velocity-profitability', [ReportController::class, 'postVelocityProfitability']);
            Route::post('velocity-profitability-columns', [ReportController::class, 'postVelocityProfitabilityColumns']);

            Route::post('merchant-per-diff', [ReportController::class, 'postMerchantPerDiff']);
            Route::post('merchant-per-diff-columns', [ReportController::class, 'postMerchantPerDiffColumns']);

            Route::post('anticipated-payment', [ReportController::class, 'postAnticipatedPayment']);
            Route::post('anticipated-payment-columns', [ReportController::class, 'postAnticipatedPaymentColumns']);
        });
    });
});

Route::middleware('auth-download-api')->prefix('admin')->namespace('Api\Admin')->group(function () {
    Route::prefix('report')->group(function () {
        Route::post('default-rate-download', [ReportController::class, 'postDefaultRateDownload']);
        Route::post('default-rate-merchant-download', [ReportController::class, 'postDefaultRateMerchantDownload']);
        Route::post('delinquent-download', [ReportController::class, 'postDelinquentDownload']);
        Route::post('profitability2-download', [ReportController::class, 'postDelinquentDownload']);
        Route::post('profitability3-download', [ReportController::class, 'postProfitability3Download']);

        Route::post('profitability4-download', [ReportController::class, 'postProfitability4Download']);
        Route::post('investment-download', [ReportController::class, 'postInvestmentDownload']);

        Route::post('investor-assignment-download', [ReportController::class, 'postInvestorAssignmentDownload']);
        Route::post('investor-re-assignment-download', [ReportController::class, 'postInvestorReAssignmentDownload']);
        Route::post('liquidity-download', [ReportController::class, 'postLiquidityDownload']);
        Route::post('payment-download', [ReportController::class, 'postPaymentDownload']);
        Route::post('transaction-download', [ReportController::class, 'postTransactionDownload']);
        Route::post('accrued-interest-download', [ReportController::class, 'postAccruedInterestDownload']);
        Route::post('debit-investor-download', [ReportController::class, 'postDebitInvestorDownload']);
        Route::post('equity-investor-download', [ReportController::class, 'postEquityInvestorDownload']);
        Route::post('overpayment-download', [ReportController::class, 'postOverpaymentDownload']);
        Route::post('velocity-profitability-download', [ReportController::class, 'postVelocityProfitabilityDownload']);
        Route::post('merchant-per-diff-download', [ReportController::class, 'postMerchantPerDiffDownload']);
        Route::post('anticipated-payment-download', [ReportController::class, 'postAnticipatedPaymentDownload']);
        Route::post('portfolio-earning-download', [ReportController::class, 'postPortfolioEarningDownload']);
    });
});

Route::middleware('auth-download-api')->prefix('investor')->namespace('Api\Investor')->group(function () {
    Route::prefix('download')->group(function () {
        Route::get('merchant-list', [DownloadController::class, 'getMerchantList']);
        Route::get('investment-report', [DownloadController::class, 'getInvestmentReport']);
        Route::get('investment-transaction-report', [DownloadController::class, 'getInvestorTransactionReport']);
        Route::get('default-rate-merchant-report', [DownloadController::class, 'getDefaultRateMerchantReport']);
        Route::get('payment-report', [DownloadController::class, 'getPaymentReport']);
        Route::get('report', [DownloadController::class, 'getReport']);
        Route::get('statement/{id}', [DownloadController::class, 'getStatement']);
        Route::get('document/{id}', [DownloadController::class, 'getDocument']);
    });
});

Route::post('/numbers', [TestController::class, 'find_numbers']);

Route::get('/api_test', [TestController::class, 'api_test']);

//-------- Route for Auditor

Route::post('get-users', [Api\UserController::class, 'getUsers']);

Route::middleware(['basicAuth'])->group(function () {
    Route::post('merchant_create', [Api\UserController::class, 'merchantCreate']);
    Route::post('merchant_update', [Api\UserController::class, 'merchantUpdate']);
    Route::post('merchant_bank_account_update', [Api\UserController::class, 'merchantBankAccountUpdate']);
    Route::post('merchant_bank_account_create', [Api\UserController::class, 'merchantBankAccountCreate']);
    Route::post('merchant_bank_account_delete', [Api\UserController::class, 'merchantBankAccountDelete']);
    Route::post('merchant_add_payment', [Api\UserController::class, 'merchantAddPayment']);
    Route::post('mail', [Api\UserController::class, 'mail']);
    Route::post('getMerchantDetails', [Api\UserController::class, 'getMerchantDetails']);
    Route::post('update_CRMID', [Api\UserController::class, 'update_CRMIDAction']);
    Route::post('merchantPaymentDetails', [Api\UserController::class, 'merchantPaymentDetailsAction']);
    Route::post('add_merchant_notes', [Api\UserController::class, 'addMerchantNotes']);

    //investors

    Route::post('get_participants/', [Api\UserController::class, 'getParticipants']);
    Route::post('map_participants/', [Api\UserController::class, 'mapParticipants']);
    Route::post('update_investor', [Api\UserController::class, 'update_investor']);
    Route::post('create_investor', [Api\UserController::class, 'create_investor']);
    Route::post('assign_participants', [Api\UserController::class, 'assign_participants']);
});

Route::fallback(function () {
    return response()->json([
     'error' => 'Invalid request for api.', ], 404);
});
