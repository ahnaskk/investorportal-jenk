<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BillsController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CarryForwardConroller;
use App\Http\Controllers\Admin\CollectionUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FaqMerchantController;
use App\Http\Controllers\Admin\InvestorController;
use App\Http\Controllers\Admin\InvestorTransactionController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\SubStatusFlagController;
use App\Http\Controllers\Admin\MailboxController;
use App\Http\Controllers\Admin\MarketOfferController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\MerchantBatchesController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\MerchantUserController;
use App\Http\Controllers\Admin\MerchantStatementController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NotesController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PennyAdjustmentController;
use App\Http\Controllers\Admin\ReconcileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\SubadminController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserActivityLogController;
use App\Http\Controllers\Admin\PermissionLogController;
use App\Http\Controllers\Admin\MerchantBankController;
use App\Http\Controllers\AdminViewController;
use App\Http\Controllers\Auth\FirewallController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommandsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Investor\BranchManagerController;
use App\Http\Controllers\Investor\DashboardController as InvestorDashboardController;
use App\Http\Controllers\Investor\ExportController as InvestorExportController;
use App\Http\Controllers\Investor\InvestorController as InvestorInvestorController;
use App\Http\Controllers\Investor\MailboxController as InvestorMailboxController;
use App\Http\Controllers\Investor\MarketplaceController as InvestorMarketplaceController;
use App\Http\Controllers\Investor\ReportController as InvestorReportController;
use App\Http\Controllers\Investor\StatementController as InvestorStatementController;
use App\Http\Controllers\InvestorController as InvController;
use App\Http\Controllers\Lender\LenderController;
use App\Http\Controllers\MerchantController as MerController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TwoFactorAuthenticatedSessionController;
use App\Mail\MailtrapExample;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

//Ip Whitelist API
Route::name('admin::firewall::')->prefix('admin')->middleware('auth', 'whitelist')->group(function () {
    Route::any('/firewall', [FirewallController::class, 'index'])->name('index')->middleware('auth_allow:Firewall,View');
    Route::get('/firewall/users', [FirewallController::class, 'getallUsersData'])->name('usersdata');
    Route::get('/firewall/roles', [FirewallController::class, 'getallRolesData'])->name('rolesdata');
    Route::get('/firewall/{id}', [FirewallController::class, 'show'])->name('view')->middleware('auth_allow:Firewall,Edit');
    Route::get('/firewall/roles/{id}', [FirewallController::class, 'show_roles'])->name('viewroles')->middleware('auth_allow:Firewall,Edit');
    Route::post('/firewall/add', [FirewallController::class, 'store'])->name('add');
    Route::post('/firewall/addtoroles', [FirewallController::class, 'storeRoles'])->name('addtoroles');
    Route::post('/firewall/delete', [FirewallController::class, 'destroy'])->name('delete');
});

if (config('app.env') == 'production') {
    // URL::forceScheme('https');
}

 Route::get('/oneTimeData', [function () {
     $files = Storage::disk('public')->files('oneTimeData');

     if ($files) {
         foreach ($files as $key => $value) {
             echo '<a href='.asset('storage/'.$value).' download='.$value.'>'.$value.'</a> <br>';
         }
     }
 }]);

Route::get('/', [function () {
    return redirect('login');
}]);
Route::get('/home', [function () {
    return redirect('login');
}]);
Route::get('/login-by-recovery-key', [LoginController::class, 'loginByRecoveryKey'])->name('login-by-recovery-key');

Route::get('/reconciliation-status/{id}', [MerController::class, 'reconciliationStatus'])->name('reconciliationStatus');
Route::get('/reset-db-1', [HomeController::class, 'resetDbAction'])->name('reset-db-1');

/* --------------- Test query url start -----------------------*/
Route::get('/pdf-view', [TestController::class, 'pdfView'])->name('pdf-view');
Route::get('/actual_payment_left', [TestController::class, 'actualPaymentLeft'])->name('actual_payment_left');
Route::get('rundb', [TestController::class, 'rundb'])->name('rundb');
Route::get('test_stripe', [TestController::class, 'test_stripe'])->name('test_stripe');
Route::get('/test-html-view', [TestController::class, 'htmlView'])->name('html-view');
Route::get('/test-vp-payments', [TestController::class, 'testVpPayments'])->name('test-vp-payments');
Route::get('/update-max-participant-fund', [TestController::class, 'updateMaxParticipantFund'])->name('update-max-participant-fund');
Route::get('/coming_soon', [TestController::class, 'coming_soon'])->name('coming_soon');


Route::name('calicut78io/debug::')->prefix('calicut78io/debug')->group(function () {



    Route::get('/balance-difference', [TestController::class, 'balance_difference'])->name('balance-difference');
    Route::get('/check-all-balance-zero', [TestController::class, 'check_all_balance_zero'])->name('check-all-balance-zero');
    Route::get('/adjust-merchants-company-funded-amount', [TestController::class, 'AdjustMerchantsCompanyFundedAmount'])->name('adjust-merchants-company-funded-amount');
    Route::get('/', [TestController::class, 'url_list'])->name('index');
    Route::get('/mail-logs', [TestController::class, 'mail_log'])->name('mail-logs');
    Route::post('/mail-log', [TestController::class, 'mail_log'])->name('mail-log');
    Route::get('/update-management-fee', [TestController::class, 'updateManagementFee'])->name('update-management-fee');
    Route::get('/resend-reconciliation', [TestController::class, 'resendReconciliation'])->name('resend-reconciliation');
    Route::get('/rrqst', [TestController::class, 'reconcilationRequest'])->name('rrqst');
    Route::get('/profitPrincipalChange', [TestController::class, 'profitPrincipalChange'])->name('profitPrincipalChange');
    Route::get('/merchantunderWritingStatusChangeToJson', [HomeController::class, 'merchantUnderWritingStatusChangeToJsonAction'])->name('merchantunderWritingStatusChangeToJson');
    Route::get('/lenderUnderWritingFeeChangeToJson', [HomeController::class, 'lenderUnderWritingFeeChangeToJsonAction'])->name('lenderUnderWritingFeeChangeToJson');
    Route::get('/liquidity', [TestController::class, 'liquidityAction'])->name('liquidity');
    Route::get('/overpaymentMerchants', [TestController::class, 'overpaymentMerchants'])->name('overpaymentMerchants');
    Route::get('/missingInvestorsPayments', [TestController::class, 'missingInvestorsPayments'])->name('missingInvestorsPayments');
    Route::get('/overpaymentsforinvestors', [TestController::class, 'overpaymentsforinvestors'])->name('overpaymentsforinvestors');
    Route::get('/changeStatus', [TestController::class, 'changeStatusAction'])->name('changeStatus');
    Route::get('/moveDefaultDate', [TestController::class, 'moveDefaultDatetoMerchantAction'])->name('moveDefaultDate');
    Route::get('/testing', [TestController::class, 'test_merchant'])->name('testing');
    Route::get('/testFire', [TestController::class, 'testFire'])->name('testFire');
    Route::get('/pending_merchants', [TestController::class, 'pending_merchants'])->name('pending_merchants');
    Route::get('/merchantsStatusChangedtoAdvancedCompletedForLess', [TestController::class, 'merchantsStatusChangedtoAdvancedCompletedForLess']);
    Route::get('/merchantsStatusChangedtoActive', [TestController::class, 'merchantsStatusChangedtoActive'])->name('merchantsStatusChangedtoActive');
    Route::get('/changeFactorRate', [TestController::class, 'changeFactorRate'])->name('changeFactorRate');
    Route::get('/updateStatusDateForMerchant', [TestController::class, 'updateStatusDateForMerchant'])->name('updateStatusDateForMerchant');
    Route::get('/advanceToLessStatusChange', [TestController::class, 'advanceToLessStatusChange'])->name('advanceToLessStatusChange');
    Route::get('/perDiffMerchants', [TestController::class, 'perDiffMerchants'])->name('perDiffMerchants');
    Route::get('/changeOldtoNewFactorRate', [TestController::class, 'changeOldtoNewFactorRate'])->name('changeOldtoNewFactorRate');
    Route::get('/profitPrincipalChange', [TestController::class, 'profitPrincipalChange'])->name('profitPrincipalChange');
    Route::get('/complete-percentage', [TestController::class, 'checkCompletePercentage'])->name('complete-percentage');
    Route::get('/merchantsBalance', [TestController::class, 'merchantsBalance'])->name('merchantsBalance');
    Route::get('/merchantsBalanceUpdate', [TestController::class, 'merchantsBalanceUpdate'])->name('merchantsBalanceUpdate');
    Route::get('/merchantsOverpaymentsList', [TestController::class, 'merchantsOverpaymentsList'])->name('merchantsOverpaymentsList');
    Route::get('/advancedressignedInvestors', [TestController::class, 'advancedressignedInvestors'])->name('advancedressignedInvestors');
    Route::get('/settledressignedInvestors', [TestController::class, 'settledressignedInvestors'])->name('settledressignedInvestors');
    Route::get('/reasignedbasedInvestors', [TestController::class, 'reasignedbasedInvestors'])->name('reasignedbasedInvestors');
    Route::get('/advancedCompletedMerchants', [TestController::class, 'advancedCompletedMerchants'])->name('advancedCompletedMerchants');
    Route::get('/settledMerchants', [TestController::class, 'settledMerchants'])->name('settledMerchants');
    Route::get('/zerooverpayments', [TestController::class, 'zerooverpayments'])->name('zerooverpayments');
    Route::get('/testFire1', [TestController::class, 'testFire1'])->name('testFire1');
    Route::get('/inBetweenPaymentMerchants', [TestController::class, 'inBetweenPaymentMerchants'])->name('inBetweenPaymentMerchants');
    Route::get('/labelUpdation', [TestController::class, 'labelUpdation'])->name('labelUpdation');
    Route::get('/assignedDateUpdation', [TestController::class, 'assignedDateUpdation'])->name('assignedDateUpdation');
    Route::get('/syndicateInvestors', [TestController::class, 'syndicateInvestors'])->name('syndicateInvestors');
    Route::get('/merchantsCompanyAmountUpdation', [TestController::class, 'merchantsCompanyAmountUpdation'])->name('merchantsCompanyAmountUpdation');

    Route::get('lessthan100per', [TestController::class, 'lessthan100per'])->name('lessthan100per');
    Route::get('investor-payment', [TestController::class, 'getUpdateInvestorPayments'])->name('investor-payment');
    Route::get('investor-principal', [TestController::class, 'getUpdateInvestorPrincipal'])->name('investor-principal');
    Route::get('/changePaymentDeleteDescripton', [TestController::class, 'changePaymentDeleteDescripton'])->name('changePaymentDeleteDescripton');
    Route::get('updateOldFactorRate', [TestController::class, 'updateOldFactorRate'])->name('updateOldFactorRate');
    Route::get('allAutoinvestchangetoLabels', [TestController::class, 'allAutoinvestchangetoLabels'])->name('allAutoinvestchangetoLabels');
    Route::get('merchantsHaveNoRoleList', [TestController::class, 'merchantsHaveNoRoleList'])->name('merchantsHaveNoRoleList');
    Route::get('merchantsHaveNoRoleChanged', [TestController::class, 'merchantsHaveNoRoleChanged'])->name('merchantsHaveNoRoleChanged');
    Route::get('merchantsCompanyAmountZeroList', [TestController::class, 'merchantsCompanyAmountZeroList'])->name('merchantsCompanyAmountZeroList');
    Route::get('profitToCarry', [TestController::class, 'profitToCarry'])->name('profitToCarry');
    Route::get('Data1', [TestController::class, 'Data1'])->name('Data1');
    Route::get('Data2', [TestController::class, 'Data2'])->name('Data2');
    Route::get('deleteEmptyTerms', [TestController::class, 'deleteEmptyTerms'])->name('deleteEmptyTerms');
    Route::get('changeRcodeNotes', [TestController::class, 'changeRcodeNotes'])->name('changeRcodeNotes');
    Route::get('importView', [TestController::class, 'importView'])->name('importView');
    Route::get('updateLastStatusupdatedDate', [TestController::class, 'moveCreatedAtToMerchantLastStatusDate'])->name('moveCreatedAtToMerchantLastStatusDate');

    Route::get('CompanyAmountShareDiffrence', [TestController::class, 'CompanyAmountShareDiffrence'])->name('CompanyAmountShareDiffrence');
    Route::get('InvestorRTRShareDiffrence', [TestController::class, 'InvestorRTRShareDiffrence'])->name('InvestorRTRShareDiffrence');
    Route::get('InvestorRTRShareDiffrenceGroup', [TestController::class, 'InvestorRTRShareDiffrenceGroup'])->name('InvestorRTRShareDiffrenceGroup');
    Route::get('NetEffectForPrinciplaProfitMngmentFeeAndShare', [TestController::class, 'NetEffectForPrinciplaProfitMngmentFeeAndShare'])->name('NetEffectForPrinciplaProfitMngmentFeeAndShare');

    Route::get('currentInvested', [TestController::class, 'currentInvestedForInvestors'])->name('currentInvestedForInvestors'); 
    Route::get('UserMetaVsMerchantUserVsPayment', [TestController::class, 'UserMetaVsMerchantUserVsPayment'])->name('UserMetaVsMerchantUserVsPayment'); 
    Route::get('CTDForInvestors', [TestController::class, 'CTDForInvestors'])->name('CTDForInvestors'); 
    Route::get('data_info', [TestController::class, 'data_info'])->name('data_info'); 
    Route::get('addNameToLog', [TestController::class, 'addNameToActivityLog'])->name('addNameToActivityLog');
    Route::get('merchant_details', [TestController::class, 'merchant_deatils_updation'])->name('merchant_deatils_updation'); 

    Route::get('/table_repair', [TestController::class, 'table_repair'])->name('table_repair');
    Route::get('/payment_repair', [TestController::class, 'payment_repair'])->name('payment_repair');
    Route::get('/payment_left_repair', [TestController::class, 'payment_left_repair'])->name('payment_left_repair');
    Route::get('/payment-difference', [TestController::class, 'paymentDifference'])->name('payment-difference');
    Route::get('/view-complete-percentage', [TestController::class, 'viewCompletePercentage'])->name('view-complete-percentage');
    Route::get('/update-underwriting-fee-percentage', [TestController::class, 'updatingUnderwritingFeePercentage'])->name('update-underwriting-fee-percentage');
    
    Route::get('/generate-historical-data' , [TestController::class, 'GenerateHistoricalData'])->name('generate-historical-data');
    Route::post('/generate-historical-data', [TestController::class, 'GenerateHistoricalData'])->name('generate-historical-data-submit');    
    Route::get('CheckAll', [TestController::class, 'CheckAll'])->name('CheckAll');    

    Route::get('/update_last_payemnt_date', [TestController::class, 'update_last_payment_date'])->name('update-last-payment-date');

    
});

Route::post('import', [TestController::class, 'import'])->name('crm.import');

/* --------------- Test query url  end -----------------------*/

Auth::routes();
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
// Admin section route start

Route::name('admin::')->prefix('admin')->middleware('auth', 'whitelist')->group(function () {

    /**
     * User Activity Log Routes
     */
    Route::get('/carryforwards', [ReportController::class, 'profitCarryForwardReport'])->name('get-profit-carryforwards');
    Route::post('/carryforwards', [ReportController::class, 'profitCarryForwardReport'])->name('get-profit-carryforwards-data');
    Route::post('/carryforwards/deletemultiple', [CarryForwardConroller::class, 'deletemultiple'])->name('carryforwards.deletemultiple');
    Route::post('/carryforwards/deletemultiple_filter', [CarryForwardConroller::class, 'deletemultiple_filter'])->name('carryforwards.deletemultiple_filter');
    Route::resource('/carryforward', CarryForwardConroller::class);
    Route::get('/fullcalender', [AdminUserController::class, 'fullcalender'])->name('fullcalender');
    Route::get('/gitpull', [AdminUserController::class, 'gitpull'])->name('gitpull');
    Route::post('/Postgitpull', [AdminUserController::class, 'Postgitpull'])->name('Postgitpull');
    Route::any('/run-commands', [AdminUserController::class, 'run_commands'])->name('run-commands');
    Route::get('/two-factor-authentication', [AdminUserController::class, 'twoFactorAuthSettings'])->name('two-factor-authentication');
    Route::post('/two-factor-auth-settings', [AdminUserController::class, 'postTwoFactorAuthSettings'])->name('two-factor-auth-settings');
    Route::get('/save-recovery-key', [AdminUserController::class, 'saveRecoveryKey'])->name('save-recovery-key');
    Route::get('/enable-two-factor-auth', [AdminUserController::class, 'enableTwoFactorAuth'])->name('enable-two-factor-auth');
    Route::get('/recovery-key-pdf-view', [AdminUserController::class, 'recoveryKeyPdfView'])->name('recovery-key-pdf-view');
    Route::get('/activity-log', [UserActivityLogController::class, 'getIndex'])->name('activity-log.get.index');
    Route::get('/permission-log', [PermissionLogController::class, 'getIndex'])->name('permission-log.get.index');
    Route::get('/permission-log/records', [PermissionLogController::class, 'getRecords'])->name('permission-log.get.records');
    Route::get('/investor-transaction-log', [UserActivityLogController::class, 'getInvestorTransactionLog']);
    Route::get('/activity-log/records', [UserActivityLogController::class, 'getRecords'])->name('activity-log.get.records');
    Route::get('/permssion_denied', [AdminUserController::class, 'permissionDenied'])->name('permssion_denied');
    Route::get('/activity_log', [AdminUserController::class, 'activityLogAction'])->name('activity_log')->middleware('auth_allow:Activity Log,View');
    Route::get('/log_data', [AdminUserController::class, 'logDataAction'])->name('log_data');
    Route::get('/re-assign', [AdminUserController::class, 're_assign'])->middleware('auth_allow:Settings Re-assign,View')->name('get-re-assign');
    Route::get('/get-investor-data', [ReportController::class, 'getInvesterData'])->name('get-investor-data');
    Route::get('/get-commission-data', [ReportController::class, 'getCommissionData'])->name('get-commission-data');

    Route::get('/get-payment-data', [ReportController::class, 'getPaymentData'])->name('get-payment-data');
    Route::get('/duplicate-db', [AdminUserController::class, 'duplicateDb'])->name('get-duplicate-db')->middleware('auth_allow:Settings Duplicate DB,View');
    Route::post('/duplicate-db', [AdminUserController::class, 'duplicateDbAction'])->name('duplicate-db');
    Route::post('/change-db', [AdminUserController::class, 'changeDbAction'])->name('change-db');
    Route::get('/reset-db', [AdminUserController::class, 'resetDbAction'])->name('reset-db');
    Route::get('/merchantMarketOfferList', [MarketOfferController::class, 'merchantMarketOfferList'])->name('merchantMarketOfferList')->middleware('auth_allow:Marketing Offers,View');
    Route::get('/investorMarketOfferList', [MarketOfferController::class, 'investorMarketOfferList'])->name('investorMarketOfferList')->middleware('auth_allow:Marketing Offers,View');
    Route::get('/addEditMerchantsOffers', [MarketOfferController::class, 'addEditMerchantsOfferAction'])->name('addEditMerchantsOffers');
    Route::get('/addEditInvestorsOffers', [MarketOfferController::class, 'addEditInvestorsOfferAction'])->name('addEditInvestorsOffers');
    Route::get('/merchant_market_offer_data', [MarketOfferController::class, 'merchantMarketOfferDataAction'])->name('merchant_market_offer_data');
    Route::get('/investor_market_offer_data', [MarketOfferController::class, 'investorMarketOfferDataAction'])->name('investor_market_offer_data');
    Route::post('/addUpdateMerchantMarketOffer', [MarketOfferController::class, 'addUpdateMerchantMarketOfferAction'])->name('addUpdateMerchantMarketOffer');
    Route::post('/addUpdateInvestorMarketOffer', [MarketOfferController::class, 'addUpdateInvestorMarketOfferAction'])->name('addUpdateInvestorMarketOffer');

    //->middleware('viewer');
    Route::post('/merchant_delete_Offers/{id}', [MarketOfferController::class, 'merchantDeleteOfferAction'])->name('merchant_delete_Offers'); //->middleware('viewer');
    Route::post('/investor_delete_Offers/{id}', [MarketOfferController::class, 'investorDeleteOfferAction'])->name('investor_delete_Offers');

    Route::get('/reconcile', [ReconcileController::class, 'reconcile'])->name('reconcile');
    Route::get('/reconcile/create', [ReconcileController::class, 'create'])->name('reconcile-create')->middleware('auth_allow:Reconcile,Create');
    Route::get('/getInvestors', [AdminUserController::class, 'getInvestors'])->name('getInvestors');
    Route::get('/getAssignedInvestors', [AdminUserController::class, 'getAssignedInvestors'])->name('getAssignedInvestors');
    Route::get('/getInvestorsforOwner', [AdminUserController::class, 'getInvestorsforOwner'])->name('getInvestorsforOwner');
    Route::post('/getCompanyWiseInvestors', [AdminUserController::class, 'getCompanyWiseInvestors'])->name('getCompanyWiseInvestors');
    Route::post('/getMerchantsForAgentFee', [AdminUserController::class, 'getMerchantsForAgentFee'])->name('getMerchantsForAgentFee');
    Route::post('/updateTwoFactorMandatoryStatus', [AdminUserController::class, 'updateTwoFactorMandatoryStatus'])->name('updateTwoFactorMandatoryStatus');
    Route::post('/getRoleUsers', [AdminUserController::class, 'getRoleUsers'])->name('getRoleUsers');
    Route::get('/getAllInvestors', [AdminUserController::class, 'getAllInvestors'])->name('getAllInvestors');
    Route::get('/getMerchants', [AdminUserController::class, 'getMerchants'])->name('getMerchants');
    Route::post('/getSelect2Merchants', [MerchantController::class, 'getSelect2Merchants'])->name('getSelect2Merchants');
    Route::post('/getSelect2Investors', [InvestorController::class, 'getSelect2Investors'])->name('getSelect2Investors');
    Route::post('/getSelect2MerchantsWithDeleted', [MerchantController::class, 'getSelect2MerchantsWithDeleted'])->name('getSelect2MerchantsWithDeleted');
    Route::get('/getInvestorAdmins', [AdminUserController::class, 'getInvestorAdmins'])->name('getInvestorAdmins');
    Route::get('/getCompanies', [AdminUserController::class, 'getCompanies'])->name('getCompanies');
    Route::get('/reconcile/create/{id}', [ReconcileController::class, 'lcreate'])->name('get-reconcile_create'); //->middleware('viewer');
    Route::post('/reconcile/create/{id}', [ReconcileController::class, 'store'])->name('reconcile_store');
    Route::post('/re-assign', [AdminUserController::class, 'post_re_assign'])->name('re-assign'); //->middleware('viewer');
    Route::get('/percentage_deal', [AdminUserController::class, 'percentage_deal_graph'])->name('percentageDeal')->middleware('auth_allow:Merchant Graph,View');

    Route::get('/bank', [AdminUserController::class, 'admin_bank_accounts'])->name('create_bank')->middleware('auth_allow:Bank Details,Create');
    Route::get('/bank/edit/{id}', [AdminUserController::class, 'edit_admin_bank_accounts'])->name('edit_bank')->middleware('auth_allow:Bank Details,Edit');
    Route::post('/storebank', [AdminUserController::class, 'storeBankDetails'])->name('storeBank')->middleware('auth_allow:Bank Details,Create');
    Route::post('/updatebank/{id}', [AdminUserController::class, 'updateBankDetails'])->name('updateBank')->middleware('auth_allow:Bank Details,Edit');
    Route::get('/viewbank', [AdminUserController::class, 'view_bank_details'])->name('view-bank')->middleware('auth_allow:Bank Details,View');
    Route::get('/bankdata', [AdminUserController::class, 'getAdminBankaccountDetails'])->name('bankdata');
    Route::post('/delete_bank/{id}', [AdminUserController::class, 'deleteBankAccount'])->name('delete_bank'); //->middleware('viewer');
    Route::post('/delete_bank_details/{id}', [InvestorController::class, 'deleteBankAccountDetails'])->name('delete_bank_details');

    Route::get('/lenderActivation', [AdminUserController::class, 'enable_disable_lender'])->name('get-lender-activation')->middleware('auth_allow:Lenders,Edit');
    Route::post('/lenderActivation', [AdminUserController::class, 'enable_disable_lender'])->name('lender-activation');
    Route::post('/enableDisableLender', [AdminUserController::class, 'updateLenderEnableDisable'])->name('change-lender-status');
    Route::post('/update-graph', [AdminUserController::class, 'getPiechartValues'])->name('update-graph')->middleware('auth_allow:Merchant Graph,View');
    Route::post('/download-graph', [AdminUserController::class, 'downloadPiechartValues'])->name('download-graph');

    Route::get('/generatedPdfCsv', [AdminUserController::class, 'generatedCsvPdfManager'])->name('generated-pdf-csv'); //->middleware('viewer');
    Route::get('/get/generatedfile/{file}', [AdminUserController::class, 'generatedFileLoader'])->name('generated-file'); //->middleware('viewer');
    Route::post('/delete_statements', [AdminUserController::class, 'delete_statements'])->name('delete_statements'); //->middleware('viewer');
    Route::post('/send_mail_to_investors', [AdminUserController::class, 'send_mail_to_investors'])->name('send_mail_to_investors'); //->middleware('viewer');

    Route::get('/change_merchant_status', [AdminUserController::class, 'change_merchant_status'])->name('get-change_merchant_status')->middleware('auth_allow:Merchants,Edit');
    Route::post('/change_merchant_status', [AdminUserController::class, 'merchant_status_change'])->name('change_merchant_status');
    Route::post('/merchant_status_check', [AdminUserController::class, 'merchantStatusCheckAction'])->name('merchant_status_check');

    /****************************/

    Route::get('/change_advanced_status', [AdminUserController::class, 'change_advanced_status'])->name('get-change_advanced_status')->middleware('auth_allow:Merchants,Edit');
    Route::post('/change_advanced_status', [AdminUserController::class, 'changeAdvancedStatusAction'])->name('change_advanced_status');
    Route::post('/advanced_status_check', [AdminUserController::class, 'advanced_status_check'])->name('advanced_status_check');

    /****************************/

    Route::get('/merchant_status_log', [AdminUserController::class, 'merchant_status_log'])->name('merchant_status_log')->middleware('auth_allow:Merchant Status Log,View');
    Route::get('/pdf_for_investors', [AdminUserController::class, 'generatedPdfForInvestors'])->name('pdf_for_investors')->middleware('auth_allow:Generate PDF,Create');

    Route::post('/generate_pdf_preview', [AdminUserController::class, 'generatePdfPreview'])->name('generate_pdf_preview');
    Route::post('/send_mail_to_investor', [AdminUserController::class, 'sendMailToInvestor'])->name('send_mail_to_investor');
    Route::post('/send_investor_portal', [AdminUserController::class, 'sendInvestorPortal'])->name('send_investor_portal');

    /***********Merchant Statement Section*****************/
    Route::get('/pdf_for_merchants', [MerchantStatementController::class, 'create'])->name('merchants-statements-create')->middleware('auth_allow:Generate statement,Create');
    Route::post('/generate_pdf_for_merchants', [MerchantStatementController::class, 'store'])->name('merchants-statements-generate')->middleware('auth_allow:Generate statement,Create');
    Route::get('/generated_pdf_merchants', [MerchantStatementController::class, 'index'])->name('merchants-statements')->middleware('auth_allow:Generate statement,View');
    Route::post('/delete_statements_merchants', [MerchantStatementController::class, 'destroy'])->name('merchants-statements-delete')->middleware('auth_allow:Merchants,Delete');
    Route::post('/view_statements_merchants/{id}', [MerchantStatementController::class, 'show'])->name('merchants-statements-view')->middleware('auth_allow:Merchants,View');

    /***********Merchant Statement Section End*****************/

  
    Route::get('/mailbox', [MailboxController::class, 'index'])->name('mailbox')->middleware('auth_allow:MailBox,View');
    Route::get('/mailbox/{id}', [MailboxController::class, 'view'])->name('mailbox-id');
    Route::get('/getInvestorAdmin', [AdminUserController::class, 'getInvestorAdmin'])->name('getInvestorAdmin');

    Route::name('sub_status::')->prefix('sub_status')->middleware('auth')->group(function () {
        Route::get('/', [StatusController::class, 'index'])->name('index')->middleware('auth_allow:Settings Sub Status,View');
        Route::get('/create', [StatusController::class, 'create'])->name('create'); //->middleware('viewer');
    Route::get('/edit/{id}', [StatusController::class, 'edit'])->name('edit'); //->middleware('viewer');
    Route::post('/delete/{id}', [StatusController::class, 'delete'])->name('delete'); //->middleware('viewer');
    Route::post('/create', [StatusController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
    Route::post('/update', [StatusController::class, 'update'])->name('update'); //->middleware('viewer');
    Route::get('/data', [StatusController::class, 'rowData'])->name('data');
    });

    Route::name('label::')->prefix('label')->middleware('auth')->group(function () {
        Route::get('/', [LabelController::class, 'index'])->name('index')->middleware('auth_allow:Settings Label,View');
        Route::get('/create', [LabelController::class, 'create'])->name('create'); //->middleware('viewer');
        Route::get('/edit/{id}', [LabelController::class, 'edit'])->name('edit'); //->middleware('viewer');
        Route::post('/delete/{id}', [LabelController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [LabelController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
        Route::post('/update', [LabelController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::get('/data', [LabelController::class, 'rowData'])->name('data');
    });

       Route::name('sub_status_flag::')->prefix('sub_status_flag')->middleware('auth')->group(function () {
        Route::get('/', [SubStatusFlagController::class, 'index'])->name('index')->middleware('auth_allow:Settings Label,View');
        Route::get('/create', [SubStatusFlagController::class, 'create'])->name('create'); 
        Route::get('/edit/{id}', [SubStatusFlagController::class, 'edit'])->name('edit'); 
        Route::post('/delete/{id}', [SubStatusFlagController::class, 'delete'])->name('delete'); 
        Route::post('/create', [SubStatusFlagController::class, 'storeCreate'])->name('storeCreate'); 
        Route::post('/update', [SubStatusFlagController::class, 'update'])->name('update'); 
        Route::get('/data', [SubStatusFlagController::class, 'rowData'])->name('data');
    });

    Route::name('settings::')->prefix('settings')->middleware('auth')->group(function () {
        Route::get('/', [SettingController::class, 'settingUpdateAction'])->name('index')->middleware('auth_allow:Settings Advanced,View');
        Route::post('/', [SettingController::class, 'settingUpdateAction'])->name('settings.update');

        Route::get('/system_settings', [SettingController::class, 'systemSettingUpdate'])
        ->name('system_settings')->middleware('auth_allow:System Settings,View');
        Route::post('/systemupdate', [SettingController::class, 'systemSettingUpdateAction'])->name('systemupdate');
        Route::post('/paymentmodeupdate', [SettingController::class, 'paymentModeUpdateAction'])->name('paymentmodeupdate');
        Route::post('/revertdatemodeupdateaction', [SettingController::class, 'revertDateModeUpdateAction'])->name('revertdatemodeupdateaction');
        Route::post('/twofactorrequiredupdation', [SettingController::class, 'twoFactorRequiredUpdation'])->name('twofactorrequiredupdation');

        Route::post('/accounts-view-status-update', [SettingController::class, 'accountViewStatusUpdate'])->name('accounts-view-status-update');

    });

    /* get all invetsor admins */

    Route::get('/marketplace', [MarketplaceController::class, 'list'])->name('marketplace')->middleware('auth_allow:Marketplace,View');
    Route::get('{mid}/documents', [MarketplaceController::class, 'listdocs'])->name('document');

    Route::name('dashboard::')->prefix('dashboard')->middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('get-index');
        Route::post('/data', [DashboardController::class, 'postDashboard'])->name('data-index');
        Route::post('/transaction', [DashboardController::class, 'postDashboardTransaction'])->name('dashboard-transaction');
        Route::post('/company', [DashboardController::class, 'postCompanyDashboard'])->name('company');
        Route::get('/old', [DashboardController::class, 'oldDashboard'])->name('old');
        Route::post('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/view/{id}', [DashboardController::class, 'view'])->name('view');
        Route::get('/details/{id}', [DashboardController::class, 'details'])->name('details');
    });
    /* Pament */

    Route::name('notes::')->prefix('notes')->middleware('auth')->group(function () {
        Route::get('{id}/update/', [NotesController::class, 'update_s'])->name('update_s')->middleware('auth_allow:Notes,View'); //only for single

        /* for multiple notes, disabled */
        Route::get('{id}/create/', [NotesController::class, 'create'])->name('create'); //->middleware('viewer');
        Route::get('{id}/lists/', [NotesController::class, 'index'])->name('lists');
        Route::get('{id}/edit', [NotesController::class, 'edit'])->name('edit'); //->middleware('viewer');
        Route::post('{id}/delete', [NotesController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('{id}/create', [NotesController::class, 'storeCreate'])->name('storeCreate');
        Route::post('{id}/update', [NotesController::class, 'update'])->name('update');
        Route::get('{id}/data', [NotesController::class, 'rowData'])->name('data');
    });

    Route::name('bills::')->prefix('bills')->middleware('auth')->group(function () {
        Route::get('update/', [BillsController::class, 'update_s'])->name('update_s'); //only for single
        Route::get('/accountSelect', [BillsController::class, 'accountSelect'])->name('accountSelect');
        Route::get('import_bill/', [BillsController::class, 'import_bill'])->name('import_bill')->middleware('auth_allow:Transactions,Download');
        Route::get('create/', [BillsController::class, 'create'])->name('create')->middleware('auth_allow:Transactions,Create');
        Route::get('/', [BillsController::class, 'index'])->name('lists')->middleware('auth_allow:Transactions,View');
        Route::get('edit/{id}', [BillsController::class, 'edit'])->name('edit'); //->middleware('viewer');
        Route::post('delete', [BillsController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('create', [BillsController::class, 'storeCreate'])->name('storeCreate')->middleware('auth_allow:Transactions,Create');
        Route::post('update/{id}', [BillsController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::get('data', [BillsController::class, 'rowData'])->name('data')->middleware('auth_allow:Transactions,View');
        Route::post('export', [BillsController::class, 'export'])->name('export');
        Route::post('/csvupload', [BillsController::class, 'uploadBillCsv'])->name('csvupload');
        Route::post('/csvprocess', [BillsController::class, 'csvProcess'])->name('csvprocess');
    });

    Route::name('messages::')->prefix('messages')->middleware('auth')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('lists')->middleware('auth_allow:Message,View');
        Route::post('/', [MessageController::class, 'index'])->name('tableData')->middleware('auth_allow:Message,View');
        Route::post('/send', [MessageController::class, 'send'])->name('send')->middleware('auth_allow:Message,Edit');
        Route::get('/send/{id}', [MessageController::class, 'send'])->name('singleSend')->middleware('auth_allow:Message,Edit');
        Route::get('/sendSample', [MessageController::class, 'sendSample'])->name('sample')->middleware('auth_allow:Message,Edit');
    });

    Route::name('vdistribution::')->prefix('vdistribution')->middleware('auth')->group(function () {
        Route::get('update/', [BillsController::class, 'update_s'])->name('update_s'); //->middleware('viewer'); //only for single

        /* for multiple notes, disabled */
        Route::get('create/', [BillsController::class, 'create'])->name('create'); //->middleware('viewer');
        Route::post('delete/{id}', [BillsController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('create', [BillsController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
    });

    Route::name('merchant_investor::')->prefix('merchant_investor')->middleware('auth')->group(function () {

        Route::get('/create/{id}', [MerchantUserController::class, 'create_investor'])->name('create-investor');
        Route::post('filtered-investor', [MerchantUserController::class, 'filteredInvestor'])->name('filtered-investor');

        /* assigned investor to merchant */
        Route::get('/create/', [MerchantController::class, 'create_investor'])->name('get-create'); //->middleware('viewer');
        Route::get('/edit/{id}', [MerchantUserController::class, 'edit_investor'])->name('edit'); //->middleware('viewer');
        Route::get('/view_investor/{id}', [MerchantController::class, 'view_investor'])->name('view_investor'); //->middleware('viewer');
        Route::post('/update/', [MerchantUserController::class, 'update_investor'])->name('update');
        Route::post('/create/', [MerchantUserController::class, 'store_investor'])->name('create'); //->middleware('viewer');
        Route::get('/assignPayment/', [MerchantUserController::class, 'assign_based_on_payment'])->name('assign-based-on-payment');
        Route::post('/assignPayment/', [MerchantUserController::class, 'assign_based_on_payment'])->name('assign-payment');
        Route::get('/assignInvestor/', [MerchantUserController::class, 'assign_investor_based_on_liquidity'])->name('assign-investor-based-on-liquidity'); //->middleware('viewer');
        Route::post('/assignInvestor/', [MerchantUserController::class, 'assign_investor_based_on_liquidity'])->name('assign-investor');
        Route::get('/view', [MerchantController::class, 'index'])->name('view');
        Route::post('/updateInvetment', [MerchantController::class, 'updateInvetment'])->name('updateInvetment');
        /* merchant view */
        Route::post('/delete/{id}', [MerchantUserController::class, 'delete_investor'])->name('delete'); //->middleware('viewer');
        Route::post('/delete_investments', [MerchantUserController::class, 'delete_multi_investment'])->name('delete_investments'); //->middleware('viewer');
        Route::get('/add_merchant_investment_transaction/{id}', [MerchantController::class, 'addMerchantInvestmentTransaction'])->name('addMerchantInvestmentTransaction'); //->middleware('viewer');
        Route::get('/add_merchant_payment_transaction/{id}', [MerchantController::class, 'addMerchantPaymentTransaction'])->name('addMerchantPaymentTransaction'); //->middleware('viewer');
        Route::post('/delete', [PaymentController::class, 'delete_multi_payment'])->name('multi_delete'); //->middleware('viewer');
        Route::get('{mid}/documents/{iid}', [MerchantController::class, 'investorDocuments'])->name('document');
        Route::get('{mid}/documents/{iid}/view/{docid}', [MerchantController::class, 'viewInvestorDoc'])->name('document::view');
        Route::post('{mid}/documents/{iid}', [MerchantController::class, 'investorDocumentUpload'])->name('document::upload-docs');
        /* Document uploader for merchant-investor */
        Route::post('{mid}/documents/{iid}/{docid}/update', [MerchantController::class, 'investorDocumentUpdate'])->name('document::update-docs');
        Route::post('{mid}/documents/{iid}/{docid}/delete', [MerchantController::class, 'investorDocumentDelete'])->name('document::delete-docs');
        Route::get('/all_documents', [MerchantController::class, 'getAllInvestorDocument'])->name('all_documents');
        Route::post('check-company-share', [MerchantController::class, 'checkCompanyshare'])->name('check-company-share');

        /*

        Investor Document upload

        */

        Route::get('/documents_upload/{iid}', [MerchantController::class, 'uploadInvestorDocument'])->name('documents_upload')->middleware('auth_allow:Investors,View');
        Route::post('/documents_upload/{iid}', [MerchantController::class, 'investorDocumentUploadByAdmin'])->name('documents_upload::upload-docs-admin'); //upload
        Route::post('/merchant_documents_upload/{mid}', [MerchantController::class, 'marketplaceDocumentUpload'])->name('merchant_documents_upload::merchant-upload-docs-admin'); //upload
        Route::post('/documents_upload/{iid}/{docid}/delete', [MerchantController::class, 'investorDocumentDeleteByAdmin'])->name('documents_upload::delete-docs'); //delete
        Route::get('/documents_upload/{iid}/view/{docid}', [MerchantController::class, 'viewInvestorDocument'])->name('documents_upload::view');
        //;//->middleware('viewer'); //view
        Route::post('/documents_upload/{iid}/{docid}/update', [MerchantController::class, 'documentInvestmentUpdate'])->name('documents_upload::update-idocs'); //upload
    });

    /* Admin Merchants routes start */
    Route::name('merchants::')->prefix('merchants')->middleware('auth')->group(function () {
        Route::get('faq/datatable', [FaqController::class, 'datatable'])->name('get-faq.datatable');
        Route::resource('/faq', FaqController::class)->middleware('auth_allow:FAQ,View');
        Route::get('FactorRateMerchnatUserUpdate/{merchant_id}', [MerchantController::class, 'FactorRateMerchnatUserUpdate'])->name('FactorRateMerchnatUserUpdate');
        Route::get('assign-investor/{id}', [MerchantUserController::class, 'assignInvestor'])->name('assign-investor');
        Route::post('/list-investor-for-assign', [MerchantUserController::class, 'listInvestorForAssign'])->name('list_investor_for_assign');
        Route::post('/assign-investor-to-merchant', [MerchantUserController::class, 'assignInvestorToMerchant'])->name('assign-investor-to-merchant');
        Route::post('/delete-participant-row', [MerchantController::class, 'deleteParticipantRow'])->name('delete-participant-row');
        Route::post('/update-assign-investor-session', [MerchantController::class, 'updateAssignInvestorSession'])->name('update-assign-investor-session');
        Route::post('/cancel-all-participant-row', [MerchantController::class, 'cancelAllParticipantRow'])->name('cancel-all-participant-row');
        Route::post('/list-investors-based-on-liquidity', [MerchantController::class, 'listInvestorsBasedOnLiquidity'])->name('list-investors-based-on-liquidity');
        
        Route::get('FactorRateMerchnatUserUpdate/{merchant_id}/{factor_rate}', [MerchantController::class, 'FactorRateMerchnatUserUpdate'])->name('getFactorRateMerchnatUserUpdate');
        Route::post('NetAmountCalculation', [PaymentController::class, 'NetAmountCalculation'])->name('NetAmountCalculation');
        Route::get('/test', [MerchantController::class, 'ttmail'])->name('requests');
        Route::get('/activity-logs/{id}', [UserActivityLogController::class, 'activity_logs']);
        Route::get('/payoffLetterForMerchants/{id}', [MerchantController::class, 'payoff_letter_for_merchants'])->name('payoffLetterForMerchants');
        Route::get('/records', [UserActivityLogController::class, 'getMerchantsRecords'])->name('records');
        Route::get('/update-max-participant-fund/{id}', [MerchantController::class, 'updateMaxParticipantFund'])->name('update-max-participant-fund');
        Route::get('/adjust-investor-funded-amount/{id}', [MerchantController::class, 'AdjustInvestorFundedAmount'])->name('adjust-investor-funded-amount');
        Route::get('/update-max-participant-fund/{id}', [MerchantController::class, 'updateMaxParticipantFund'])->name('update-max-participant-fund');
        Route::any('/investor-transactions', [MerchantController::class, 'investorTransactions'])->name('investor_transactions');
        Route::post('/investor-transactions-store', [MerchantController::class, 'investorTransactionsStore'])->name('investor_transactions_store');
        Route::get('/adjust-company-funded-amount/{id}', [MerchantController::class, 'AdjustCompanyFundedAmount'])->name('adjust-company-funded-amount');
        
        Route::name('requests::')->prefix('requests')->middleware('auth')->group(function () {
            Route::get('/view/{id}', [MerchantController::class, 'requests'])->name('requests')->middleware('auth_allow:Merchants,View');
            Route::post('/approve/{id}', [MerchantController::class, 'requestsApprove'])->name('approve');
            Route::get('/delete/{id}', [MerchantController::class, 'requestsDelete'])->name('delete'); //->middleware('viewer');
      //approve
        });
        Route::name('Investment::')->prefix('Investment')->middleware('auth')->group(function () {
            Route::name('LiquidityBased::')->prefix('LiquidityBased')->middleware('auth')->group(function () {
                Route::get('/{id}', [MerchantController::class, 'LiquidityBasedInvestment'])->name('Page')->middleware('auth_allow:Merchants,View');
                Route::post('/Share', [MerchantController::class, 'LiquidityBasedShare'])->name('Share')->middleware('auth_allow:Merchants,View');
                Route::post('/Assign', [MerchantController::class, 'AssignLiquidityBasedShare'])->name('Assign')->middleware('auth_allow:Merchants,View');
                Route::post('/RejectedList', [MerchantController::class, 'LiquidityBasedRejectedList'])->name('RejectedList')->middleware('auth_allow:Merchants,View');
            });
            Route::name('PaymentBased::')->prefix('PaymentBased')->middleware('auth')->group(function () {
                Route::get('/{id}', [MerchantController::class, 'PaymentBasedInvestment'])->name('Page')->middleware('auth_allow:Merchants,View');
                Route::post('/Share', [MerchantController::class, 'PaymentBasedShare'])->name('Share')->middleware('auth_allow:Merchants,View');
                Route::post('/CompanyShare', [MerchantController::class, 'PaymentBasedCompanyShare'])->name('CompanyShare')->middleware('auth_allow:Merchants,View');
                Route::post('/Assign', [MerchantController::class, 'AssignPaymentBasedShare'])->name('Assign')->middleware('auth_allow:Merchants,View');
            });//pending not Developed
        });

        Route::get('merchantuserroledata', [MerchantController::class, 'getallMerchantUserRolesData'])->name('merchantuserroledata');
        Route::get('/show-merchant-users', [MerchantController::class, 'view_merchant_user_roles'])->name('show-merchant-users')->middleware('auth_allow:Users,View');
        Route::post('/selectMerchants', [MerchantController::class, 'selectMerchant'])->name('selectMerchants');
        Route::get('/reconcilation-request', [ReconcileController::class, 'reconcilationRequest'])->name('reconcilation-request')->middleware('auth_allow:Reconciliation,View');;
        Route::post('/reconcilation-request-download', [ReconcileController::class, 'reconciliationRequestDownload'])->name('reconcilation-request-download');
        Route::get('/mail-log', [ReconcileController::class, 'mailLog'])->name('mail-log')->middleware('auth_allow:Mail Log,View');
        Route::post('/mail-log-download', [ReconcileController::class, 'mailLogDownload'])->name('mail-log-download')->middleware('auth_allow:Mail Log,View');
        Route::post('/change-substatus', [MerchantController::class, 'changeSubStatus'])->name('change-substatus'); //->middleware('viewer');
        Route::post('/change-substatus-flag', [MerchantController::class, 'changeSubStatusFlag'])->name('change-substatus-flag');
        Route::post('/company_investors', [MerchantController::class, 'companyInvestors'])->name('company_investors');
        Route::get('/requests_data/{mid}', [MerchantController::class, 'rowDataRequests'])->name('requests_data');
        Route::get('/', [MerchantController::class, 'index'])->name('index')->middleware('auth_allow:Merchants,View');
        Route::get('/index2', [MerchantController::class, 'index2'])->name('index2');
        Route::get('/create', [MerchantController::class, 'create'])->name('create')->middleware('auth_allow:Merchants,Create');
        Route::get('/edit/{id}', [MerchantController::class, 'edit'])->name('edit')->middleware('auth_allow:Merchants,Edit');
        Route::post('/user', [MerchantController::class, 'postVerifyEmail'])->name('verify-email')->middleware('auth_allow:Merchants,Edit');
        Route::post('/delete/{id}', [MerchantController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [MerchantController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
        Route::post('/update', [MerchantController::class, 'update'])->name('update'); //->middleware('viewer');

        Route::post('/getLiquidity', [MerchantController::class, 'getInvestorLiquidityByCreator'])->name('get-liquidity');
        Route::post('/getPercenatgeFromVelocity', [MerchantController::class, 'getPercenatgeFromVelocity'])->name('get-percentage-velocity');
        Route::post('/calculateVelocitiesByPercentage', [MerchantController::class, 'calculateVelocitiesByPercentage'])->name('sub-number');
        Route::post('/checkVelocityWithMaxFund', [MerchantController::class, 'checkVelocityWithMaxFund'])->name('check-velocity');

        Route::post('/investor_status', [MerchantUserController::class, 'investorMerchantStatus'])->name('investorMerchantStatus');

        Route::post('/merchantNotes', [NotesController::class, 'merchantNotes'])->name('merchantNotes');
        Route::post('/addNotes', [NotesController::class, 'addNotes'])->name('addNotes');
        Route::post('/payment-check-for-merchant', [MerchantController::class, 'paymentCheckForMerchant'])->name('payment-check-for-merchant');
        Route::get('/merchant_data/{merchant_id}/{company_id}/{investor_id}', [MerchantController::class, 'rowDataMerchant'])->name('merchant_data');
        Route::get('{mid}/documents', [MerchantController::class, 'marketplaceDocuments'])->name('document')->middleware('auth_allow:Merchants,Edit');
        Route::get('date_wise_investor_payment/{mid}', [MerchantController::class, 'date_wise_investor_payment'])->name('date_wise_investor_payment');
        
        Route::post('{mid}/documents', [MerchantController::class, 'marketplaceDocumentUpload'])->name('document::upload-docs');
        Route::get('{mid}/documents/{docid}', [MerchantController::class, 'viewMarketplaceDoc'])->name('document::view');
        Route::post('{mid}/documents/{docid}/update', [MerchantController::class, 'marketplaceDocumentUpdate'])->name('document::update-docs');
        Route::post('{mid}/documents/{docid}/delete', [MerchantController::class, 'marketplaceDocumentDelete'])->name('document::delete-docs');
        Route::get('/view/{id}', [MerchantController::class, 'view'])->name('view')->middleware('auth_allow:Merchants,View');
        Route::get('/user/{id}', [MerchantController::class, 'merchantUser'])->name('merchantUser')->middleware('auth_allow:Merchants,View');
        Route::post('TransactionData', [TransactionController::class, 'TransactionData'])->name('TransactionData')->middleware('auth_allow:Merchants,View');
        Route::any('/story/{id}', [MerchantController::class, 'story'])->name('story');
        Route::get('{merchant_id}/faq/datatable', [FaqMerchantController::class, 'datatable'])->name('faq.datatable');
        Route::resource('{merchant_id}/faq', FaqMerchantController::class, ['as' => 'merchantFaq'])->middleware('auth_allow:FAQ,View');
        Route::get('/creditcard-payment/{id}', [MerchantController::class, 'creditcard_payment'])->name('creditcard_payment')->middleware('auth_allow:Credit Card Payment,Edit');
        Route::post('/creditcard-payment/{id}', [PaymentController::class, 'process_stripe_payment'])->name('process_stripe_payment_merchant')->middleware('auth_allow:Credit Card Payment,View');
        Route::get('/export', [MerchantController::class, 'exportForm'])->name('get-export-deals');
        Route::post('/export', [MerchantController::class, 'exportDeals'])->name('export-deals');
        Route::get('/export2', [MerchantController::class, 'exportForm2'])->name('get-export-deals2')->middleware('auth_permit:Revenue Recognition Report');
        Route::post('/export2', [MerchantController::class, 'exportDeals2'])->name('export-deals2');
        Route::get('/re-assign', [MerchantController::class, 're_assign'])->name('re-assign');
        Route::post('/undo-reassign', [MerchantController::class, 'undo_re_assign'])->name('undo-reassign');
        Route::post('/update-agent-fee', [MerchantController::class, 'update_agent_fee'])->name('update-agent-fee');
        Route::post('/undo-section', [MerchantController::class, 'undo_section'])->name('undo-section');
        Route::get('/auto_company_filter', [InvestorController::class, 'auto_company_filter'])->name('auto_company_filter');
        Route::get('/company_filter', [InvestorController::class, 'company_filter'])->name('company_filter');
      
      
        Route::get('/merchant-payment', [MerchantController::class, 'merchantPayment'])->name('merchant-payment');

        /* Change investment to other investor */
        Route::get('{mid}/terms', [MerchantController::class, 'paymentTerms'])->name('payment-terms')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms', [MerchantController::class, 'storePaymentTerms'])->name('payment-terms-store')->middleware('auth_permit:Payment Term');
        Route::post('terms/date', [MerchantController::class, 'checkDate'])->name('payment-terms-date')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms/{id}/delete', [MerchantController::class, 'deleteTerm'])->name('payment-terms-delete')->middleware('auth_permit:Payment Term');
        Route::get('{mid}/terms/create', [MerchantController::class, 'createTerm'])->name('payment-terms-create')->middleware('auth_permit:Payment Term');
        Route::post('pause-payment', [MerchantController::class, 'pausePayment'])->name('payments.pause')->middleware('auth_permit:Payment Term');
        Route::post('resume-payment', [MerchantController::class, 'resumePayment'])->name('payments.resume')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms/update-payment', [MerchantController::class, 'updateTermPayment'])->name('payment-terms-update-payment')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms/{tid}/{id}/delete-payment', [MerchantController::class, 'deleteTermPayment'])->name('payment-terms-delete-payment')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms/delete-payments', [MerchantController::class, 'deleteACHPayments'])->name('payment-terms-delete-payments')->middleware('auth_permit:Payment Term');
        Route::post('{mid}/terms/add-payment', [MerchantController::class, 'addTermPayment'])->name('payment-terms-add-payment')->middleware('auth_permit:Payment Term');
        Route::post('terms/makeup-payment', [MerchantController::class, 'makeupPaymentTerms'])->name('payment-terms-makeup')->middleware('auth_permit:Payment Term');
        Route::get('/{merchant_id}/bank_accounts', [MerchantBankController::class, 'bank_details_list'])->name('bank.index')->middleware('auth_allow:Bank,View');
        Route::get('/{merchant_id}/bank_accounts/create', [MerchantBankController::class, 'createBankAccount'])->name('bank.create')->middleware('auth_allow:Bank,Create');
        Route::get('/{merchant_id}/bank_accounts/{id}', [MerchantBankController::class, 'editBankAccount'])->name('bank.edit')->middleware('auth_allow:Bank,Edit');
        Route::post('/{merchant_id}/bank_accounts', [MerchantBankController::class, 'updateBankAccount'])->name('bank.update')->middleware('auth_allow:Bank,Edit');
        Route::post('/{merchant_id}/bank_accounts/data', [MerchantBankController::class, 'bank_details_list'])->name('bank.data');
        Route::post('/{merchant_id}/bank_accounts/{id}/delete', [MerchantBankController::class, 'deleteBankAccount'])->name('bank.delete')->middleware('auth_allow:Bank,Edit');
        Route::post('/check-bank-account', [MerchantController::class, 'checkBankAccountsExist'])->name('bank.check')->middleware('auth_allow:Bank,Edit');
    });
    /* Admin Merchants routes end */

    Route::name('merchant_batches::')->prefix('merchant_batches')->middleware('auth')->group(function () {
        Route::get('/', [MerchantBatchesController::class, 'index'])->name('index')->middleware('auth_allow:Merchant Batches,View');
        Route::get('/create', [MerchantBatchesController::class, 'create'])->name('create')->middleware('auth_allow:Merchant Batches,Create');
        Route::get('/edit/{id}', [MerchantBatchesController::class, 'edit'])->name('edit')->middleware('auth_allow:Merchant Batches,Edit');
        Route::post('/delete/{id}', [MerchantBatchesController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [MerchantBatchesController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
        Route::post('/update', [MerchantBatchesController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::get('/data', [MerchantBatchesController::class, 'rowData'])->name('data');
    });

    //admin investor management

    Route::name('investors::')->prefix('investors')->middleware('auth')->group(function () {
        Route::get('faq/datatable', [FaqController::class, 'datatable'])->name('faq.datatable');
        Route::resource('/faq', FaqController::class)->middleware('auth_allow:FAQ,View');
        Route::get('/ctd-for-all-investors', [InvestorController::class, 'ctdForAllInvestor'])->name('ctd-for-all-investors');
        Route::get('/', [InvestorController::class, 'index'])->name('index')->middleware('auth_allow:Investors,View');
        Route::post('/', [InvestorController::class, 'index'])->name('post-index');
        Route::post('/investorFee', [AdminUserController::class, 'getInvestorManagementAndSyndFee'])->name('get_investor_fee');
        Route::post('/merchantFee', [AdminUserController::class, 'getMerchantFee'])->name('get_merchant_fee');

        Route::get('/bank_details/{id}', [InvestorController::class, 'bank_details_list'])->name('bank_details')->middleware('auth_allow:Bank,View');
        Route::get('/edit_bank/{id}', [InvestorController::class, 'edit_admin_bank_accounts'])->name('edit_bank_details')->middleware('auth_allow:Bank,Edit'); //
        Route::get('/bankdata/{id}', [InvestorController::class, 'getAdminBankaccountDetails'])->name('bankdata');
        Route::post('/selectType', [InvestorController::class, 'selectType'])->name('selectType');
        Route::post('/investorsLogList', [InvestorController::class, 'investorsLogList'])->name('investorsLogList');

        /* all investor list */

        Route::get('/data', [InvestorController::class, 'rowData'])->name('data');
        /* investor datatable list */

        Route::get('/create', [InvestorController::class, 'create'])->name('create')->middleware('auth_allow:Investors,Create');

        /* investor create page */

        Route::get('/edit/{id}', [InvestorController::class, 'edit'])->name('edit')->middleware('auth_allow:Investors,Edit');
        /* investor edit page */
        Route::get('/edit-pref-return/{id}', [InvestorController::class, 'editRoiRate'])->name('edit-pref-return')->middleware('auth_allow:Investors,Edit');
        Route::get('/edit-pref-return-data/{user_id}/{id}', [InvestorController::class, 'editRoiRateDetails'])->name('edit-pref-return-data')->middleware('auth_allow:Investors,Edit');

        Route::post('/save-roi-rate/{id}', [InvestorController::class, 'saveRoiRate'])->name('save-roi-rate');
        Route::post('/update-roi-rate/{id}', [InvestorController::class, 'updateRoiRate'])->name('update-roi-rate');

        Route::get('/investor-pref-return/{id}', [InvestorController::class, 'investorRoiRate'])->name('investor-pref-return')->middleware('auth_allow:Investors,Edit');
        Route::post('/delete-investor-roi-rate/{id}', [InvestorController::class, 'deleteInvestorRoiRate'])->name('delete-investor-roi-rate');
        
        Route::post('/check-date-for-roi-rate', [InvestorController::class, 'checkDateForRoiRate'])->name('check-date-for-roi-rate');

        Route::get('/achRequest/{id}', [InvestorController::class, 'achRequest'])->name('achRequest')->middleware('auth_allow:Investor Ach Debit,View');
        Route::post('/achRequest/{id}', [InvestorController::class, 'achRequest'])->name('achRequestSend')->middleware('auth_allow:Investor Ach Debit,Create');
        Route::get('/achRequest/Credit/{id}', [InvestorController::class, 'achCreditRequest'])->name('achCreditRequest')->middleware('auth_allow:Investor Ach Credit,View');
        Route::post('/achRequest/Credit/{id}', [InvestorController::class, 'achCreditRequest'])->name('achCreditRequestSend')->middleware('auth_allow:Investor Ach Credit,Create');

        Route::get('/syndicationReport/{id}', [InvestorController::class, 'investorSyndicationReport'])->name('syndication-report')->middleware('auth_allow:Syndication Payment,View');
        Route::get('/SyndicationPayments', [InvestorController::class, 'SyndicationPayments'])->name('syndication-payments')->middleware('auth_allow:Syndication Payment,View');
        Route::get('/SyndicationPaymentsTable', [InvestorController::class, 'SyndicationPaymentsTable'])->name('syndication-payments-tabledata')->middleware('auth_allow:Syndication Payment,View');
        Route::post('/sendSyndicationPayments', [InvestorController::class, 'sendSyndicationPayments'])->name('syndication-payments-send')->middleware('auth_allow:Syndication Payment,Create');
        Route::post('/sendSyndicationPaymentSingle', [InvestorController::class, 'sendSyndicationPaymentSingle'])->name('syndication-payments-send-single')->middleware('auth_allow:Syndication Payment,Create');
        Route::post('/changeAutoSyndicatePaymentStatus', [InvestorController::class, 'changeAutoSyndicatePaymentStatus'])->name('syndication-payments-auto-status')->middleware('auth_allow:Syndication Payment,Create');
        Route::post('/update/{id}', [InvestorController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::post('/delete/{id}', [InvestorController::class, 'delete'])->name('delete'); //->middleware('viewer');

        /* generate pdf/csv file */

        Route::post('/generatePdfCsv/{id}', [InvestorController::class, 'generatePdfCsvFile'])->name('generatePdfCsv'); //->middleware('viewer');

        /* investor delete  */


        Route::get('/create-reserve-liquidity/{id}', [InvestorController::class, 'createReserveLiquidity'])->name('create-reserve-liquidity')->middleware('auth_allow:Investors,Edit');
        Route::get('/edit-reserve-liquidity-data/{user_id}/{id}', [InvestorController::class, 'editReserveLiquidityDetails'])->name('edit-reserve-liquidity-data')->middleware('auth_allow:Investors,Edit');
        Route::post('/save-reserve-liquidity/{id}', [InvestorController::class, 'saveReserveLiquidity'])->name('save-reserve-liquidity');
        Route::post('/update-reserve-liquidity/{id}', [InvestorController::class, 'updateReserveLiquidity'])->name('update-reserve-liquidity');
        Route::get('/investor-reserve-liquidity/{id}', [InvestorController::class, 'investorReserveLiquidity'])->name('investor-reserve-liquidity')->middleware('auth_allow:Investors,Edit');
        Route::post('/delete-reserve-liquidity/{id}', [InvestorController::class, 'deleteInvestorReserveLiquidity'])->name('delete-reserve-liquidity');
        Route::post('/check-date-for-reserve-liquidity', [InvestorController::class, 'checkDateForReserveLiquidity'])->name('check-date-for-reserve-liquidity');

        Route::post('/create', [InvestorController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/portfolio-download', [InvestorController::class, 'portfolioDownload'])->name('portfolio-download'); //->middleware('viewer');
        Route::get('/portfolio/{id}', [InvestorController::class, 'portfolio'])->name('portfolio')->middleware('auth_allow:Investors,View');
        Route::get('/NewPortfolio/{id}', [InvestorController::class, 'NewPortfolio'])->name('NewPortfolio')->middleware('auth_allow:Investors,View');
        /* investor portfolio  */
        Route::post('/portfolio', [InvestorController::class, 'portfolio'])->name('portfolio-filter');
        Route::get('/bank/{id}', [InvestorController::class, 'bank'])->name('bank')->middleware('auth_allow:Investors,Edit');
        Route::get('/bankCreate/{id}', [InvestorController::class, 'bankEdit'])->name('bankCreate')->middleware('auth_allow:Bank,Create');
        Route::get('/documents/{id}', [InvestorController::class, 'documents'])->name('documents');
        Route::post('/updatebank', [InvestorController::class, 'updateBank'])->name('updateBank');
        Route::get('/portfolio', [InvestorController::class, 'portfolioLists'])->name('portfolioLists');
        Route::get('/liquidity_update/{id}', [InvestorController::class, 'liquidity_update'])->name('liquidity_update');
        Route::post('/investor_ach_request-edit/{id}', [InvestorController::class, 'investorAchCheck_edit_ajax']);
        Route::get('/transaction-report', [InvestorController::class, 'transactions'])->name('transactionreport')->middleware('auth_permit:Transaction Report');
        Route::post('/delete_transaction', [InvestorController::class, 'delete_transactions'])->name('delete_transactions');
        Route::post('/update_multiple_transaction', [InvestorTransactionController::class, 'update_multiple_transaction'])->name('update_multiple_transaction');
        Route::post('/transaction-report-records', [InvestorController::class, 'transactions'])->name('transaction-report-records')->middleware('auth_permit:Transaction Report');
        Route::post('/transaction-report', [InvestorController::class, 'transactionReportDownload'])->name('transactionreportdownload')->middleware('auth_allow:Transaction Report,Download');
        Route::post('/default-report', [ReportController::class, 'defaultReportDownload'])->name('defaultreportdownload')->middleware('auth_allow:Default Rate Report,Download');
        Route::name('transaction::')->prefix('transactions')->middleware('auth')->group(function () {
            Route::get('/{id}/create', [InvestorTransactionController::class, 'create'])->name('create')->middleware('auth_allow:Investors,Edit');
            /* investor transaction creation */
            Route::post('/{id}/export', [InvestorTransactionController::class, 'export'])->name('export');
            Route::get('/{id}', [InvestorTransactionController::class, 'index'])->name('index')->middleware('auth_allow:Investors,View');
            /* investor transaction list */

            Route::get('/{id}/{tid}/edit', [InvestorTransactionController::class, 'edit'])->name('edit');
            Route::post('/{id}/{tid}/update', [InvestorTransactionController::class, 'update'])->name('update');
            Route::post('/{id}/store', [InvestorTransactionController::class, 'store'])->name('store');
            Route::get('/{id}/{tid}/delete', [InvestorTransactionController::class, 'delete'])->name('delete');
            Route::get('/{id}/{tid}/status_change', [InvestorTransactionController::class, 'status_change'])->name('status_change');
        });
        Route::name('portfolio::')->prefix('portfolio')->middleware('auth')->group(function () {
            Route::post('MerchantData', [InvestorController::class, 'MerchantData'])->name('merchants')->middleware('auth_allow:Investors,View');
            Route::post('PaymentData', [InvestorController::class, 'PaymentData'])->name('payments')->middleware('auth_allow:Investors,View');
            Route::post('MerchantPaymentData/{id}', [InvestorController::class, 'MerchantPaymentData'])->name('merchant_payment')->middleware('auth_allow:Investors,View');
            Route::post('reassignment', [InvestorController::class, 'InvestorReAssignmentHistoryData'])->name('reassignment')->middleware('auth_allow:Investors,View');
            Route::post('MerchantReassignment/{id}', [InvestorController::class, 'InvestorReAssignmentMerchantHistoryData'])->name('merchant_reassignment')->middleware('auth_allow:Investors,View');
        });
    });

    // collection user section
    Route::name('collection_users::')->prefix('collection_users')->middleware('auth')->group(function () {
        Route::get('/', [CollectionUserController::class, 'index'])->name('index')->middleware('auth_allow:Collection Users,View');
        Route::get('/data', [CollectionUserController::class, 'rowData'])->name('data');
        Route::get('/create', [CollectionUserController::class, 'create'])->name('create')->middleware('auth_allow:Collection Users,Create');
        Route::get('/edit/{id}', [CollectionUserController::class, 'edit'])->name('edit')->middleware('auth_allow:Collection Users,Edit');
        Route::post('/update/{id}', [CollectionUserController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [CollectionUserController::class, 'delete'])->name('delete');
        Route::post('/create', [CollectionUserController::class, 'storeCreate'])->name('storeCreate');
    });

    // branch manager section
    //common
    Route::name('branch_managers::')->prefix('branch_manager')->middleware('auth')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index')->middleware('auth_allow:Branch Manager,View');
        Route::get('/data', [BranchController::class, 'rowData'])->name('data');
        Route::get('/create', [BranchController::class, 'create'])->name('create')->middleware('auth_allow:Branch Manager,Create');
        Route::get('/edit/{id}', [BranchController::class, 'edit'])->name('edit')->middleware('auth_allow:Branch Manager,Edit');
        Route::post('/update/{id}', [BranchController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::post('/delete/{id}', [BranchController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [BranchController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
    });

    Route::name('template::')->prefix('template')->middleware('auth')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index')->middleware('auth_allow:Template Management,View');
        Route::get('/data', [TemplateController::class, 'rowData'])->name('data');
        Route::get('/create', [TemplateController::class, 'create'])->name('create')->middleware('auth_allow:Template Management,Create');
        Route::get('/edit/{id}', [TemplateController::class, 'edit'])->name('edit')->middleware('auth_allow:Template Management,Edit');
        Route::post('/update/{id}', [TemplateController::class, 'update'])->name('update'); //->middleware('viewer');
        Route::post('/delete/{id}', [TemplateController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [TemplateController::class, 'storeCreate'])->name('storeCreate'); //->middleware('viewer');
        Route::post('/selectTemplate', [TemplateController::class, 'selectTemplate'])->name('selectTemplate'); //->middleware('viewer');
        Route::post('/selectType', [TemplateController::class, 'selectType'])->name('selectType'); //->middleware('viewer');
        Route::get('/getTheme', [TemplateController::class, 'getTheme']);
        Route::get('/send-sample/{id}', [TemplateController::class, 'sendSample'])->name('sample-email');
    });

    Route::middleware('auth')->group(function () {

        /////////----------------------
       Route::get('/data', [Admin\DynamicReportController::class, 'rowData'])->name('dynamic-report.data');
       Route::get('/report-data/{id}', [Admin\DynamicReportController::class, 'report_data'])->name('dynamic-report.report-data');

       // Route::post('/update/{id}', [Admin\DynamicReportController::class, 'update'])->name('dynamic-report.update');
       Route::resource('/dynamic-report', Admin\DynamicReportController::class);

        Route::post('/filter_operator', [Admin\DynamicReportController::class, 'filter_operator'])->name('dynamic-report.filter_operator');

        Route::post('/dynamic_report_export/{id}', [Admin\DynamicReportController::class, 'dynamic_report_export'])->name('dynamic_report_export');




        ///////////////////----------------





        Route::post('/investor-report-data', [Admin\DynamicReportInvestorController::class, 'report_data'])->name('dynamic-report-investor.report-data');
        Route::post('/investor-report-data-merchant', [Admin\DynamicReportInvestorController::class, 'report_data_merchant'])->name('dynamic-report-investor.report-data-merchant');
        Route::get('/investordata', [Admin\DynamicReportInvestorController::class, 'rowData'])->name('dynamic-report-investor.data');
        Route::resource('/dynamic-report-investor', Admin\DynamicReportInvestorController::class);


	    Route::post('/merchant-report-data', [Admin\DynamicReportMerchantController::class, 'report_data'])->name('dynamic-report-merchant.report-data');
	    Route::get('/merchantdata', [Admin\DynamicReportMerchantController::class, 'rowData'])->name('dynamic-report-merchant.data');
	    Route::resource('/dynamic-report-merchants', Admin\DynamicReportMerchantController::class);
    });

    /* lender section */

    Route::name('lenders::')->prefix('lender')->middleware('auth')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_lenders'])->name('create_lenders')->middleware('auth_allow:Lenders,Create');
        Route::get('/', [AdminUserController::class, 'view_lenders'])->name('show_lenders')->middleware('auth_allow:Lenders,View');
        Route::get('/edit/{id}', [AdminUserController::class, 'editLenders'])->name('edit_lender'); //->middleware('viewer');
        Route::post('/delete_lender/{id}/{type}', [AdminUserController::class, 'deleteUsers'])->name('delete_lender'); //->middleware('viewer');
        Route::post('/lenderFee', [AdminUserController::class, 'getLenderManagementAndSyndFee'])->name('get_lenders_fee');
    });

    /* editor section */

    Route::name('editors::')->prefix('editor')->middleware('auth')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_editors'])->name('create_editors'); //->middleware('viewer');
        Route::get('/', [AdminUserController::class, 'view_editors'])->name('show_editors');
        Route::get('/edit/{id}', [AdminUserController::class, 'editEditors'])->name('edit_editors'); //->middleware('viewer');
        Route::post('/delete_editors/{id}/{type}', [AdminUserController::class, 'delete_editors'])->name('delete_editors'); //->middleware('viewer');
    });

    Route::name('viewers::')->prefix('viewer')->middleware('auth')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_viewers'])->name('create-viewer')->middleware('auth_allow:Viewers,Create');
        Route::get('/', [AdminUserController::class, 'view_viewers'])->name('show-viewer')->middleware('auth_allow:Viewers,View');
        Route::get('/edit/{id}', [AdminUserController::class, 'editViewers'])->name('edit-viewers')->middleware('auth_allow:Viewers,Edit');
        Route::post('/delete-viewers/{id}/{type}', [AdminUserController::class, 'delete_viewers'])->name('delete-viewers'); //->middleware('viewer');
    });

    /* role section */
    Route::name('roles::')->prefix('role')->middleware('auth')->group(function () {
        Route::get('/', [AdminUserController::class, 'view_roles'])->name('show-role')->middleware('auth_allow:Roles,View');
        Route::get('/show-user-role', [AdminUserController::class, 'view_user_roles'])->name('show-user-role')->middleware('auth_allow:Users,View');
        Route::get('/edit/{id}', [AdminUserController::class, 'editPermissions'])->name('edit-permissions')->middleware('auth_allow:Permissions,Edit');
        Route::get('user-role/edit/{id}', [AdminUserController::class, 'editRoleUser'])->name('edit-role-user')->middleware('auth_allow:Merchants,Edit');
        Route::get('user-user-permissions/edit/{id}', [AdminUserController::class, 'editUserPermissions'])->name('edit-user-permissions')->middleware('auth_allow:Merchants,Edit');
        Route::get('/create-user', [AdminUserController::class, 'create_users'])->name('create-user')->middleware('auth_allow:Users,Create');
        Route::get('/show-modules', [AdminUserController::class, 'view_modules'])->name('show-modules')->middleware('auth_allow:Modules,View');
        Route::get('/create-module', [AdminUserController::class, 'create_modules'])->name('create-module')->middleware('auth_allow:Modules,Create');
        Route::get('/edit-module/{id}', [AdminUserController::class, 'edit_modules'])->name('edit-module')->middleware('auth_allow:Modules,Edit');
        Route::post('/delete-module/{id}/{type}', [AdminUserController::class, 'delete_module'])->name('delete-module'); //->middleware('viewer');
        Route::get('/create-role', [AdminUserController::class, 'create_roles'])->name('create-role')->middleware('auth_allow:Roles,Create');
    });

    /* investor admin section */

    Route::name('sub_admins::')->prefix('sub_admins')->middleware('auth')->group(function () {
        Route::get('/', [SubadminController::class, 'index'])->name('index')->middleware('auth_allow:Companies,View');
        Route::get('/data', [SubadminController::class, 'rowData'])->name('data');
        Route::get('/create', [SubadminController::class, 'create'])->name('create')->middleware('auth_allow:Companies,Create');
        Route::get('/edit/{id}', [SubadminController::class, 'edit'])->name('edit')->middleware('auth_allow:Companies,Edit');
        Route::post('/update/{id}', [SubadminController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [SubadminController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::post('/create', [SubadminController::class, 'storeCreate'])->name('storeCreate');
    });

    Route::name('lenders::')->prefix('lender')->middleware('auth')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_lenders'])->name('create_lenders')->middleware('auth_allow:Lenders,Create');
        Route::get('/', [AdminUserController::class, 'view_lenders'])->name('show_lenders')->middleware('auth_allow:Lenders,View');
        Route::get('/edit/{id}', [AdminUserController::class, 'editLenders'])->name('edit_lender')->middleware('auth_allow:Lenders,Edit');
        Route::post('/delete_lender/{id}/{type}', [AdminUserController::class, 'deleteUsers'])->name('delete_lender'); //->middleware('viewer');
        Route::get('/view/{id}', [AdminUserController::class, 'viewLenders'])->name('view_lender'); //->middleware('viewer');
    });

    Route::name('editors::')->prefix('editor')->middleware('auth')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_editors'])->name('create_editors')->middleware('auth_allow:Editors,Create');
        Route::get('/', [AdminUserController::class, 'view_editors'])->name('show_editors')->middleware('auth_allow:Editors,View');
        Route::get('/edit/{id}', [AdminUserController::class, 'editEditors'])->name('edit_editors')->middleware('auth_allow:Editors,Edit');
        Route::post('/delete_editors/{id}/{type}', [AdminUserController::class, 'delete_editors'])->name('delete_editors'); //->middleware('viewer');
    });

    /* Admin admins */
    Route::name('admins::')->prefix('admin')->middleware('auth')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index')->middleware('auth_allow:Admins,View');
        Route::get('/data', [AdminUserController::class, 'rowData'])->name('data');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [AdminUserController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [AdminUserController::class, 'delete'])->name('delete');
        Route::post('/create', [AdminUserController::class, 'storeCreate'])->name('storeCreate');

        Route::post('/save_lender_data', [AdminUserController::class, 'saveLenderData'])->name('save_lender_data');
        Route::get('/lenderdata', [AdminUserController::class, 'getallLenders'])->name('lenderdata');
        Route::get('/liquidity_adjuster', [AdminUserController::class, 'getLiquidityAdjuster'])->name('liquidity_adjuster');
        Route::post('/liquidity_adjuster', [AdminUserController::class, 'rowDataLiquidityAdjuster'])->name('rowDataLiquidityAdjuster');
        Route::get('/create_liquidity_adjuster/{id}', [AdminUserController::class, 'create_liquidity_adjuster'])->name('create_liquidity_adjuster');

        Route::post('/save_liquidity_adjuster', [AdminUserController::class, 'saveLiquidityAdjuster'])->name('save_liquidity_adjuster');
        Route::post('/update_lender/{id}', [AdminUserController::class, 'updateLenders'])->name('update_lender');
        Route::get('/editordata', [AdminUserController::class, 'getallEditorsData'])->name('editordata');
        Route::post('/save_editor_data', [AdminUserController::class, 'saveEditorData'])->name('save_editor_data');
        Route::post('/update_editor/{id}', [AdminUserController::class, 'updateEditor'])->name('update_editor');
        Route::post('/save_viewer_data', [AdminUserController::class, 'saveViewerData'])->name('save-viewer-data');
        Route::get('viewerdata', [AdminUserController::class, 'getallViewersData'])->name('viewerdata');
        Route::get('userroledata', [AdminUserController::class, 'getallUserRolesData'])->name('userroledata');
        Route::get('roledata', [AdminUserController::class, 'getallRolesData'])->name('roledata');
        Route::get('moduledata', [AdminUserController::class, 'getModuleData'])->name('moduledata');
        Route::post('/update_viewer/{id}', [AdminUserController::class, 'updateViewer'])->name('update_viewer');
        Route::post('/update_role/{id}', [AdminUserController::class, 'updateRole'])->name('update_role');
        Route::post('/update_role_user/{id}', [AdminUserController::class, 'updateRoleUser'])->name('update_role_user');
        Route::post('/save_user_role_data', [AdminUserController::class, 'saveUserRoleData'])->name('save_user_role_data');
        //, 'auth_allow:Users,Edit'
        Route::post('/update_user_role/{id}', [AdminUserController::class, 'updateUserRole'])->name('update_user_role')->middleware('auth_allow:Merchants,Edit');
        Route::post('/save_module_data', [AdminUserController::class, 'saveModuleData'])->name('save_module_data');
        Route::post('/update_module_data/{id}', [AdminUserController::class, 'updateModuleData'])->name('update_module_data');
        Route::post('/save_role_data', [AdminUserController::class, 'saveRoleData'])->name('save-role-data');
        Route::post('/copy_permission', [AdminUserController::class, 'copyPermission'])->name('copy_permission');
        Route::post('/copy_permission_to_user', [AdminUserController::class, 'copyPermissionToUser'])->name('copy_permission_to_user');

    });
    /* Admin admins End*/

    /* Admin admins End*/

    /* Admin payments */
    Route::name('payments::')->prefix('payment')->middleware('auth')->group(function () {
        Route::get('/lender_payment_generation', [PaymentController::class, 'lender_payment_generation'])->name('get-lender-payment-generation')->middleware('auth_allow:Payments,Create');
        Route::post('/lender_payment_generation', [PaymentController::class, 'lender_payment_generation'])->name('lender-payment-generation')->middleware('auth_allow:Payments,Create');

        Route::get('/PendingTransactions', [TransactionController::class, 'PendingTransactions'])->name('pending-transactions')->middleware('auth_allow:Payments,Create');
        Route::post('/PendingTransactions', [TransactionController::class, 'PendingTransactions'])->name('pending-transactions-data')->middleware('auth_allow:Payments,Create');
        Route::post('/ApproveTransactions', [TransactionController::class, 'ApproveTransactions'])->name('approve')->middleware('auth_allow:Payments,Create');
        Route::get('/ApproveTransaction/{id}', [TransactionController::class, 'ApproveTransactions'])->name('single-approve')->middleware('auth_allow:Payments,Create');

        Route::post('add_payments_for_lenders', [PaymentController::class, 'add_payments_for_lenders'])->name('add_payments_for_lenders');
        Route::post('manage_payments_for_lenders', [PaymentController::class, 'manage_payments_for_lenders'])->name('manage_payments_for_lenders');
        Route::get('/create/', [PaymentController::class, 'create'])->name('create');
        Route::post('shareCheck', [PaymentController::class, 'shareCheck'])->name('shareCheck');
        Route::get('/create/{merchant_id}', [PaymentController::class, 'create'])->name('createForMerchant')->middleware('auth_allow:Merchants,Edit');

        Route::post('/store', [PaymentController::class, 'store'])->name('store');
        Route::get('/reGeneratePayment/{id}/{type}', [PaymentController::class, 'reGeneratePayment'])->name('reGeneratePayment');
        Route::post('/revert-payment', [PaymentController::class, 'RevertPayment'])->name('RevertPayment');
        // Route::post('/store' , ['as' => 'store' , 'uses' => [PaymentController::class, 'store']]);
        Route::post('/paymentCheck', [PaymentController::class, 'paymentCheck'])->name('paymentCheck');
        Route::post('/lenderPaymentCheck', [PaymentController::class, 'lenderPaymentCheck'])->name('lenderPaymentCheck');

        Route::post('/debitPaymentLimit', [PaymentController::class, 'debitPaymentLimit'])->name('debitPaymentLimit');

        Route::post('/netPaymentSet', [PaymentController::class, 'netPaymentSet'])->name('netPaymentSet');
        Route::post('/netPaymentAll', [PaymentController::class, 'netPaymentAll'])->name('netPaymentAll');

        Route::post('/netPayment', [PaymentController::class, 'netPayment'])->name('netPayment');

        Route::get('/open-items', [PaymentController::class, 'openItems'])->name('openitems')->middleware('auth_allow:Payments,View');

        Route::get('/data', [PaymentController::class, 'rowData'])->name('data');

        Route::post('/delete/{id}', [PaymentController::class, 'delete'])->name('delete'); //->middleware('viewer');

        Route::post('/ach-payment-submit', [PaymentController::class, 'achConfirmationStore'])->name('ach-payment.store')->middleware('auth_permit:ACH');
        Route::get('/ach-payment', [PaymentController::class, 'achConfirmation'])->name('ach-payment.index')->middleware('auth_permit:ACH');
        Route::post('/ach-payment', [PaymentController::class, 'achConfirmation'])->name('ach-payment.data')->middleware('auth_permit:ACH');
        Route::post('/ach-payment-update', [PaymentController::class, 'updateAchpayments'])->name('ach-payment.update')->middleware('auth_permit:ACH');
        Route::post('/changeAutoAchStatus', [PaymentController::class, 'changeAutoAchStatusMerchant'])->name('ach-auto-status')->middleware('auth_permit:ACH');

        Route::get('investor/ach-requests', [PaymentController::class, 'investorAchRequests'])->name('investor-ach-requests.index')->middleware('auth_allow:Investor Ach,View');
        Route::post('investor/ach-requests-export', [PaymentController::class, 'investorAchRequestsExport'])->name('investor-ach-requests.export')->middleware('auth_allow:Investor Ach,View');
        Route::post('investor/ach-requests', [PaymentController::class, 'investorAchRequests'])->name('investor-ach-requests.datatable')->middleware('auth_allow:Investor Ach,View');
        Route::get('investor/ach-requests-check/{id}', [PaymentController::class, 'investorAchCheckSingleStatus'])->name('investor-ach-requests.check')->middleware('auth_allow:Investor Ach,Edit');
        Route::post('investor/ach-request-status-check-all', [PaymentController::class, 'investorAchCheckAchRequestStatus'])->name('investor-ach-requests-status.check-all')->middleware('auth_allow:Investor Ach,Edit');
        Route::get('investor/ach-pending-delete/{data}', [PaymentController::class, 'removeInvestorACHPendingVerification'])->name('investor-ach-status.delete-verification')->middleware('auth_allow:Investor Ach,Edit');
        Route::post('investor/ach-pending-delete', [PaymentController::class, 'removeInvestorACHPendingFunction'])->name('investor-ach-status.delete')->middleware('auth_allow:Investor Ach,Edit');

        Route::post('ach-requests', [PaymentController::class, 'achRequests'])->name('achRequests.datatable')->middleware('auth_permit:ACH');
        Route::get('/ach-requests', [PaymentController::class, 'achRequests'])->name('ach-requests.index')->middleware('auth_permit:ACH');
        Route::post('/ach-requests/download', [PaymentController::class, 'achRequestsExport'])->name('ach-requests.export')->middleware('auth_permit:ACH');
        Route::get('/ach-requests/{id}', [PaymentController::class, 'achCheckSingleStatus'])->name('ach-requests.view')->middleware('auth_permit:ACH');
        Route::post('/ach-requests/status', [PaymentController::class, 'achCheckStatusCsv'])->name('ach-requests.status')->middleware('auth_permit:ACH');

        Route::get('/ach-fees', [PaymentController::class, 'achFees'])->name('ach-fees.index')->middleware('auth_permit:ACH');
        Route::post('/ach-fees', [PaymentController::class, 'achFees'])->name('ach-fees.datatable')->middleware('auth_permit:ACH');
        Route::post('/ach-fees/download', [PaymentController::class, 'achFeesExport'])->name('ach-fees.export')->middleware('auth_permit:ACH');
    });
    /* Admin payments End */

    /*Admin reports */
    Route::name('reports::')->prefix('reports')->middleware('auth')->group(function () {

        Route::get('/InvestorLiquidityLog', [ReportController::class, 'InvestorLiquidityLog'])->name('InvestorLiquidityLog')->middleware('auth_allow:Investor  Liquidity Log,View');
        Route::post('/InvestorLiquidityLog', [ReportController::class, 'InvestorLiquidityLog'])->name('InvestorLiquidityLogData')->middleware('auth_allow:Investor  Liquidity Log,View');
        Route::post('/InvestorLiquidityLogDownload', [ReportController::class, 'InvestorLiquidityLogDownload'])->name('investor-liquidity-log-download');
        Route::get('/InvestorLiquidityLogCreate', [ReportController::class, 'InvestorLiquidityLogCreate'])->name('investor-liquidity-log-create');
        Route::get('/InvestorLiquidityLogTruncate', [ReportController::class, 'InvestorLiquidityLogTruncate'])->name('investor-liquidity-log-truncate');

        Route::get('/InvestorRTRBalanceLog', [ReportController::class, 'InvestorRTRBalanceLog'])->name('InvestorRTRBalanceLog')->middleware('auth_allow:Investor  RTR Balance Log,View');
        Route::post('/InvestorRTRBalanceLog', [ReportController::class, 'InvestorRTRBalanceLog'])->name('InvestorRTRBalanceLogData')->middleware('auth_allow:Investor  RTR Balance Log,View');
        Route::post('/InvestorRTRBalanceLogDownload', [ReportController::class, 'InvestorRTRBalanceLogDownload'])->name('investor-rtr-balance-log-download');
        Route::get('/InvestorRTRBalanceLogCreate', [ReportController::class, 'InvestorRTRBalanceLogCreate'])->name('investor-rtr-balance-log-create');
        Route::get('/InvestorRTRBalanceLogTruncate', [ReportController::class, 'InvestorRTRBalanceTruncate'])->name('investor-rtr-balance-log-truncate');

        // new routes
        Route::get('/arrayconsole', [ReportController::class, 'arrayConsole'])->name('arrayconsole');
        Route::post('/agreement', [MerchantController::class, 'investorAgreementDocs'])->name('agreement-docsp');

        // new routes
        Route::get('/reconcile', [ReportController::class, 'reconcileReport'])->name('reconcile_report')->middleware('auth_allow:Reconcile,View');
        Route::post('/delete/{id}', [ReconcileController::class, 'delete'])->name('delete'); //->middleware('viewer');
        Route::get('/collection', [ReportController::class, 'collection'])->name('collection');

        //  old payment report v1
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments')->middleware('auth_permit:Payment Report');
        Route::get('/agent-fee-report', [ReportController::class, 'agentFeeReport'])->name('agent-fee-report')->middleware('auth_permit:Agent Fee Report');
        Route::post('/payments', [ReportController::class, 'payments'])->name('payments-records')->middleware('auth_permit:Payment Report');
        
        Route::get('AdvancePlusInvestments/{id}/{label?}', [ReportController::class, 'advance_plus_investments'])->name('AdvancePlusInvestments')->middleware('auth_permit:Advance Plus Investments Report');
        
        // payment report v1
        Route::get('/overpayment-report', [ReportController::class, 'overPaymentReport'])->name('overpayment-report')->middleware('auth_permit:OverPayment Report');
        Route::post('/overpayment-report', [ReportController::class, 'overPaymentReport'])->name('overpayment-report-records')->middleware('auth_permit:OverPayment Report');
        Route::post('/payment-export', [ReportController::class, 'paymentExport'])->name('payment-export')->middleware('auth_allow:Payment Report,Download');
        Route::post('/assignment-export', [ReportController::class, 'investorAssignmentExport'])->name('investor-assignment-export')->middleware('auth_allow:Investor Assignment Report,Download');
        Route::post('/investor-performance-export', [ReportController::class, 'investorPerformanceExport'])->name('investor-performance-export');
        Route::get('/investor', [ReportController::class, 'investorReport'])->name('investor')->middleware('auth_permit:Investment Report');
        Route::post('/investor', [ReportController::class, 'investorReport'])->name('investor-records')->middleware('auth_permit:Investment Report');

        Route::get('/upsell-commission', [ReportController::class, 'commissionReport'])->name('upsell-commission')->middleware('auth_permit:Upsell Commission Report');
         Route::post('/commission', [ReportController::class, 'commissionReport'])->name('commission-records')->middleware('auth_permit:Upsell Commission Report');
        
        Route::get('/profitability', [ReportController::class, 'profitability_report'])->name('profitability');
        Route::get('/profitability2', [ReportController::class, 'profitabilityReport2'])->name('profitability2')->middleware('auth_permit:Profitability(65/20/15)');
        Route::post('/profitability2', [ReportController::class, 'profitabilityReport2'])->name('profitability2-records')->middleware('auth_permit:Profitability(65/20/15)');
        Route::post('/profitability2-export', [ReportController::class, 'profitability2Export'])->name('profitability2-export')->middleware('auth_permit:Profitability(65/20/15)');
        Route::post('/profitability3-export', [ReportController::class, 'profitability3Export'])->name('profitability3-export')->middleware('auth_permit:Profitability(50/30/20)');
        Route::post('/profitability21-export', [ReportController::class, 'profitability21Export'])->name('profitability21-export')->middleware('auth_permit:Profitability(50/30/20)');
        Route::post('/profitability4-export', [ReportController::class, 'profitability4Export'])->name('profitability4-export')->middleware('auth_permit:Profitability(50/50)');
        Route::get('/profitability3', [ReportController::class, 'profitabilityReport3'])->name('profitability3')->middleware('auth_permit:Profitability(50/30/20)');
        Route::post('/profitability3', [ReportController::class, 'profitabilityReport3'])->name('profitability3-records')->middleware('auth_permit:Profitability(50/30/20)');
        Route::get('/profitability21', [ReportController::class, 'profitabilityReport21'])->name('profitability21')->middleware('auth_permit:Profitability(50/30/20)');
        Route::post('/profitability21', [ReportController::class, 'profitabilityReport21'])->name('profitability21-records')->middleware('auth_permit:Profitability(50/30/20)');

        //profitability report
        Route::get('/profitability4', [ReportController::class, 'profitabilityReport4'])->name('profitability4')->middleware('auth_permit:Profitability(50/50)');
        Route::post('/profitability4', [ReportController::class, 'profitabilityReport4'])->name('profitability4-records')->middleware('auth_permit:Profitability(50/50)');
        Route::get('/InvestorAccruedPrefReturn_temp_disabled', [ReportController::class, 'investorInterestAccuredReport'])->name('investor_interest_accured_report')->middleware('auth_permit:Accrued Pre Return Report');
        Route::post('/investor-export', [ReportController::class, 'investorExport'])->name('investor-export')->middleware('auth_allow:Investment Report,Download');
         Route::post('/commission-export', [ReportController::class, 'commissionExport'])->name('commission-export')->middleware('auth_allow:Upsell Commission Report,Download');
        Route::get('/lender-data', [ReportController::class, 'lenderPerformanceData'])->name('lender-data');
        Route::get('/reassignReport', [ReportController::class, 'reAssignmentHistory'])->name('get-reassign-report')->middleware('auth_permit:Investor Reassignment Report');
        Route::post('/reassignReport', [ReportController::class, 'reAssignmentHistory'])->name('get-reassign-report-records')->middleware('auth_permit:Investor Reassignment Report');

        //Route::get('/showReport', ['as' => 'get-reassign-report', 'uses' => [ReportController::class, 'reAssignmentData']]);
        Route::get('/subadmin-investment', [ReportController::class, 'subadminInvestmentReport'])->name('subadmin-investment');
        Route::get('/subadmin-payment', [ReportController::class, 'subadminPaymentReport'])->name('subadmin-payment');
        Route::get('/investorAssignment', [ReportController::class, 'investorAssignmentReport'])->name('get-investor-assign-report')->middleware('auth_permit:Investor Assignment Report');
        Route::post('/investorAssignment', [ReportController::class, 'investorAssignmentReport'])->name('get-investor-assign-report-records')->middleware('auth_permit:Investor Assignment Report');
        Route::get('/liquidityReport', [ReportController::class, 'liquidityReport'])->name('liquidity-report')->middleware('auth_permit:Liquidity Report');
        Route::post('/liquidityReport', [ReportController::class, 'liquidityReport'])->name('liquidity-report-records')->middleware('auth_permit:Liquidity Report');
        Route::any('/liquidityLog', [ReportController::class, 'liquidity_log'])->name('liquidity-log')->middleware('auth_allow:Liquidity Log,View');
        Route::post('/liquidity-log-export', [ReportController::class, 'liquidityLogExport'])->name('liquidity-log-export')->middleware('auth_allow:Liquidity Log,Download');
        Route::get('/liquiditylogdata', [ReportController::class, 'getLiquidityLogDetails'])->name('liquidity-log-data');
        Route::any('/MerchantliquidityLog', [ReportController::class, 'merchant_liquidity_log'])->name('liquidity-log-merchant')->middleware('auth_allow:Merchant Liquidity Log,View');
        Route::get('/Merchantliquiditylogdata', [ReportController::class, 'getMerchantLiquidityLogDetails'])->name('merchant-liquidity-log-data');
        Route::get('/investorProfitReport', [ReportController::class, 'investorProfitReport'])->name('investor-profit-report')->middleware('auth_permit:Debt Investor Report');
        Route::post('/investor-profit-report', [ReportController::class, 'investorProfitReport'])->name('investor-profit-report-records')->middleware('auth_permit:Debt Investor Report');
        Route::post('/investor-interest-accured', [ReportController::class, 'investorInterestAccuredReport'])->name('investor-interest-accured-report-records')->middleware('auth_permit:Accrued Pre Return Report');
        Route::post('/investorInterestAccuredDetails', [ReportController::class, 'investorInterestAccuredDetails'])->name('investor-interest-accured-details');


        Route::get('/defaultRateReport', [ReportController::class, 'defaultRateReport'])->name('default-rate-report')->middleware('auth_permit:Default Rate Report');
        Route::post('/defaultRateReport', [ReportController::class, 'defaultRateReport'])->name('default-rate-report-records')->middleware('auth_permit:Default Rate Report');

        Route::get('/defaultRateMerchantReport', [ReportController::class, 'defaultRateMerchantReport'])->name('default-rate-merchant-report')->middleware('auth_permit:Default Rate Merchant Report');
        Route::post('/defaultRateMerchantReportData', [ReportController::class, 'defaultRateMerchantReportData'])->name('default-rate-merchant-report-data');
        Route::post('/def-merchant-rep-export', [ReportController::class, 'defaultRateMerchantReportExport'])->name('def-payment-rep-export')->middleware('auth_allow:Default Rate Merchant Report,Download');


        Route::get('/equityInvestorReport-update', [ReportController::class, 'equityInvestorReportUpdate'])->name('equity-investor-report-update')->middleware('auth_permit:Equity Investor Report');
        Route::get('/equityInvestorReport', [ReportController::class, 'equityInvestorReport'])->name('equity-investor-report')->middleware('auth_permit:Equity Investor Report');
        Route::post('/equity-investor-report', [ReportController::class, 'equityInvestorReport'])->name('equity-investor-report-records')->middleware('auth_permit:Equity Investor Report');

        Route::get('/totalPortfolioEarnings', [ReportController::class, 'totalPortfolioEarnings'])->name('dept-investor-report')->middleware('auth_permit:Total Portfolio Earnings');
        Route::post('/total-portfolio-earnings', [ReportController::class, 'totalPortfolioEarnings'])->name('dept-investor-report-records')->middleware('auth_permit:Total Portfolio Earnings');

        Route::get('/totalPortfolioEarnings-a', [ReportController::class, 'totalPortfolioEarnings_previous_copy'])->name('totalPortfolioEarnings-a');

        Route::post('/merchant-list-download', [MerchantController::class, 'merchantListDownload'])->name('merchant-list-download');
        Route::post('/investor-list-download', [InvestorController::class, 'investorListDownload'])->name('investor-list-download');
        Route::get('/payment-copy-report', [ReportController::class, 'paymentCopyReport'])->name('payment-copy-report');
        Route::get('/downlod-all-syndicate-payment-report', [ReportController::class, 'downloadAllSyndicatePaymentReport'])->name('downlod-all-syndicate-payment-report');

        //Velocity Profitability Report
        Route::get('/velocity-profitability', [ReportController::class, 'velocityProfitability'])->name('velocity-profitability')->middleware('auth_permit:Velocity Profitability Report');
        Route::post('/velocity-profitability', [ReportController::class, 'velocityProfitability'])->name('velocity-profitability-records')->middleware('auth_permit:Velocity Profitability Report');
        Route::post('/velocity-profitability-download', [ReportController::class, 'velocityProfitabilityDownload'])->name('velocity-profitability.download')->middleware('auth_permit:Velocity Profitability Report');

        Route::get('Tax' , [ReportController::class, 'TaxReport'])->name('TaxReport')->middleware('auth_permit:Tax Report');
        Route::post('Tax', [ReportController::class, 'TaxReport'])->name('TaxReportData')->middleware('auth_permit:Tax Report');
        Route::post('/tax-report-export', [ReportController::class, 'TaxReportExport'])->name('tax-report-export');
    });
    /*Admin reports End*/

    Route::get('jobs', [MailboxController::class, 'jobs'])->name('jobs');
    Route::get('failed_jobs', [MailboxController::class, 'failed_jobs'])->name('failed-jobs');

    Route::get('audit/{model}/{id}', [Admin\AuditController::class, 'index'])->name('audit');
});

  // Admin section route stop

  Route::name('PennyAdjustment::')->prefix('PennyAdjustment')->group(function () {
      Route::get('LiquidityDifference', [PennyAdjustmentController::class, 'LiquidityDifference'])->name('LiquidityDifference');
      Route::post('LiquidityDifference', [PennyAdjustmentController::class, 'LiquidityDifference'])->name('LiquidityDifferenceData');
      Route::get('UpdateLiquidityDifference', [PennyAdjustmentController::class, 'UpdateLiquidityDifference'])->name('UpdateLiquidityDifference');

      Route::get('MerchantValueDifference', [PennyAdjustmentController::class, 'MerchantValueDifference'])->name('MerchantValueDifference');
      Route::post('MerchantValueDifferenceData', [PennyAdjustmentController::class, 'MerchantValueDifference'])->name('MerchantValueDifferenceData');
      Route::get('UpdateMerchantValueRTRDifference', [PennyAdjustmentController::class, 'UpdateMerchantValueRTRDifference'])->name('UpdateMerchantValueRTRDifference');

      Route::get('CompanyAmountDifference', [PennyAdjustmentController::class, 'CompanyAmountDifference'])->name('CompanyAmountDifference');
      Route::post('CompanyAmountDifferenceData', [PennyAdjustmentController::class, 'CompanyAmountDifference'])->name('CompanyAmountDifferenceData');
      Route::get('UpdateCompanyAmountDifference', [PennyAdjustmentController::class, 'UpdateCompanyAmountDifference'])->name('UpdateCompanyAmountDifference');
      Route::get('UpdateInvestorBasedCompanyAmountDifference', [PennyAdjustmentController::class, 'UpdateInvestorBasedCompanyAmountDifference'])->name('UpdateInvestorBasedCompanyAmountDifference');
      Route::get('UpdateMerchantBasedCompanyAmountDifference', [PennyAdjustmentController::class, 'UpdateMerchantBasedCompanyAmountDifference'])->name('UpdateMerchantBasedCompanyAmountDifference');

      Route::get('ZeroParticipantAmount', [PennyAdjustmentController::class, 'ZeroParticipantAmount'])->name('ZeroParticipantAmount');
      Route::post('ZeroParticipantAmountData', [PennyAdjustmentController::class, 'ZeroParticipantAmount'])->name('ZeroParticipantAmountData');
      Route::get('RemoveZeroParticipantAmount', [PennyAdjustmentController::class, 'RemoveZeroParticipantAmount'])->name('RemoveZeroParticipantAmount');

      Route::get('FinalParticipantShare', [PennyAdjustmentController::class, 'FinalParticipantShare'])->name('FinalParticipantShare');
      Route::post('FinalParticipantShareData', [PennyAdjustmentController::class, 'FinalParticipantShare'])->name('FinalParticipantShareData');
      Route::get('RemoveZeroParticipantAmount', [PennyAdjustmentController::class, 'RemoveZeroParticipantAmount'])->name('RemoveZeroParticipantAmount');

      Route::get('MerchantInvestorShareDifference', [PennyAdjustmentController::class, 'MerchantInvestorShareDifference'])->name('MerchantInvestorShareDifference');
      Route::post('MerchantInvestorShareDifferenceData', [PennyAdjustmentController::class, 'MerchantInvestorShareDifference'])->name('MerchantInvestorShareDifferenceData');
      Route::get('UpdateMerchantInvestorShareDifference', [PennyAdjustmentController::class, 'UpdateMerchantInvestorShareDifference'])->name('UpdateMerchantInvestorShareDifference');

      Route::get('MerchantsFundAmountCheck', [PennyAdjustmentController::class, 'MerchantsFundAmountCheck'])->name('MerchantsFundAmountCheck');
      Route::post('MerchantsFundAmountCheckData', [PennyAdjustmentController::class, 'MerchantsFundAmountCheck'])->name('MerchantsFundAmountCheckData');

      Route::get('PennyInvestment', [PennyAdjustmentController::class, 'PennyInvestment'])->name('PennyInvestment');
      Route::post('PennyInvestmentData', [PennyAdjustmentController::class, 'PennyInvestment'])->name('PennyInvestmentData');
      Route::get('RemovePennyInvestment', [PennyAdjustmentController::class, 'RemovePennyInvestment'])->name('RemovePennyInvestment');

      Route::get('InvestmentAmountCheck', [PennyAdjustmentController::class, 'InvestmentAmountCheck'])->name('InvestmentAmountCheck');
      Route::post('InvestmentAmountCheckData', [PennyAdjustmentController::class, 'InvestmentAmountCheck'])->name('InvestmentAmountCheckData');
      Route::get('InvestmentAmountAdjuster', [PennyAdjustmentController::class, 'InvestmentAmountAdjuster'])->name('InvestmentAmountAdjuster');
      Route::get('InvestmentAmountAdjuster/{id}', [PennyAdjustmentController::class, 'InvestmentAmountAdjuster']);

      Route::get('MerchantRTRAndInvestorRtr', [PennyAdjustmentController::class, 'MerchantRTRAndInvestorRtr'])->name('MerchantRTRAndInvestorRtr');
      Route::post('MerchantRTRAndInvestorRtrData', [PennyAdjustmentController::class, 'MerchantRTRAndInvestorRtr'])->name('MerchantRTRAndInvestorRtrData');
      Route::get('AdjustInvestorRtr', [PennyAdjustmentController::class, 'AdjustInvestorRtr'])->name('AdjustInvestorRtr');
      Route::get('AdjustInvestorRtr/{id}', [PennyAdjustmentController::class, 'AdjustInvestorRtr'])->name('AdjustInvestorRtrId');
      Route::get('UpdateInvestorRtr', [PennyAdjustmentController::class, 'UpdateInvestorRtr'])->name('UpdateInvestorRtr');
      Route::get('UpdateInvestorRtr/{id}', [PennyAdjustmentController::class, 'UpdateInvestorRtr'])->name('UpdateInvestorRtrId');
  });

  Route::name('Merchant::')->prefix('Merchant')->group(function () {
      Route::name('Payment::')->prefix('Payment')->group(function () {
          Route::name('ExpectationVsGivenData::')->prefix('ExpectationVsGivenData')->group(function () {
              Route::post('table/{id}', [MerchantController::class, 'ExpectationVsGiven'])->name('TableData');
              Route::post('table/single/{id}', [MerchantController::class, 'ExpectationVsGivenSingle'])->name('SingleTableData');
          });
      });
  });
  Route::name('Log::')->prefix('Log')->middleware('auth')->group(function () {
      Route::get(''         , [Admin\LogController::class, 'index'])->name('page');
      Route::post('Table'   , [Admin\LogController::class, 'Table'])->name('Table');
      Route::get('download' , [Admin\LogController::class, 'download'])->name('download');
      Route::get('delete'   , [Admin\LogController::class, 'delete'])->name('delete');
      Route::get('deleteAll', [Admin\LogController::class, 'deleteAll'])->name('deleteAll');
  });
  Route::name('Visitor::')->prefix('Visitor')->middleware('auth')->group(function () {
      Route::get(''         , [Admin\VisitorController::class, 'index'])->name('page');
      Route::post('Table'   , [Admin\VisitorController::class, 'Table'])->name('Table');
  });
  //investor section route start
  Route::name('investor::')->prefix('investor')->middleware('investor', 'auth')->group(function () {
      //Route::get('/dashboard-data', [DashboardController::class, 'dashboard']);
      Route::get('/dashboard-data', [InvestorDashboardController::class, 'dashboard'])->name('dashboard-data');
      Route::get('/mailbox', [InvestorMailboxController::class, 'index'])->name('mailbox');
      Route::get('/mailbox/{id}', [InvestorMailboxController::class, 'view'])->name('mailbox-view');
      Route::get('/viewDocuments', [InvestorInvestorController::class, 'index']);
      Route::get('/get_documents', [InvestorInvestorController::class, 'getInvestorDocument'])->name('get_documents');
      Route::get('/documents_upload/{iid}/view/{id}', [InvestorInvestorController::class, 'viewInvestorDocument'])->name('documents_upload::view');
      Route::post('/portfolio-download', [InvestorDashboardController::class, 'portfolioDownload'])->name('portfolio-download');
      /* Marketplace */

      Route::get('/view_all_transactions', [InvestorInvestorController::class, 'transaction_view'])->name('view_all_transactions');
      /*Transactions*/

      Route::name('transactions::')->prefix('transactions ')->middleware('auth')->group(function () {
          Route::get('get_transactions', [InvestorInvestorController::class, 'getTransactions'])->name('get_transactions');
      });

      /*Marketplace*/

      Route::name('marketplace::')->prefix('marketplace')->middleware('auth')->group(function () {
          Route::get('{mid}/documents', [InvestorMarketplaceController::class, 'listdocs'])->name('document');
          Route::get('{mid}/documents/{docid}', [InvestorMarketplaceController::class, 'viewDoc'])->name('document::view');
          Route::get('/index', [InvestorDashboardController::class, 'page'])->name('index'); //todo_dec
          Route::get('/marketplace', [InvestorMarketplaceController::class, 'list'])->name('marketplace');
          Route::post('/funds_request', [InvestorMarketplaceController::class, 'funds_request'])->name('funds_request');
          Route::post('/participation_agreement', [InvestorMarketplaceController::class, 'funds_request_pdf'])->name('participation_agreement');
      });

      /* Portfolio */

      Route::name('dashboard::')->prefix('dashboard')->middleware('auth')->group(function () {
          Route::get('/', [InvestorDashboardController::class, 'index'])->name('index');
          Route::get('/view/{id}', [InvestorDashboardController::class, 'view'])->name('view');
          Route::get('/view/{id}/documents', [InvestorDashboardController::class, 'documents'])->name('documents');
          Route::post('/view/{id}/documents', [InvestorDashboardController::class, 'documentUpload'])->name('upload-docs');
          Route::post('/view/{id}/documents/{docid}/update', [InvestorDashboardController::class, 'documentUpdate'])->name('update-docs');
          Route::post('/view/{id}/documents/{docid}/delete', [InvestorDashboardController::class, 'documentDelete'])->name('delete-docs');
          Route::get('/view/{id}/documents/{docid}/view', [InvestorDashboardController::class, 'viewDoc'])->name('view-doc');
          Route::get('/details/{id}', [InvestorDashboardController::class, 'details'])->name('details');
      });

      Route::name('export::')->prefix('export')->middleware('auth')->group(function () {
          Route::post('/merchant-list', [InvestorExportController::class, 'merchantList'])->name('merchant::list');
          Route::post('/merchant-details', [InvestorExportController::class, 'merchantDetails'])->name('merchant::details');
          Route::post('/general-report', [InvestorExportController::class, 'generalReport'])->name('general::report');
      });

      Route::get('/open-items', [InvestorDashboardController::class, 'openItems'])->name('openitems');
      Route::name('report::')->prefix('report')->middleware('auth')->group(function () {
          Route::get('/general', [InvestorReportController::class, 'general'])->name('general');
          Route::get('/fee', [InvestorReportController::class, 'general'])->name('fee');
      });

      Route::name('statements::')->prefix('report')->middleware('auth')->group(function () {
          Route::get('/weekly-statement', [InvestorStatementController::class, 'weekly'])->name('weekly');
          Route::get('/weekly-statement/{id}', [InvestorStatementController::class, 'viewWeeklyReportDocument'])->name('weekly-view');
      });
      Route::post('/InvestorAchRequest/GetList', [InvestorInvestorController::class, 'InvestorAchRequest_get_list_ajax'])->name('InvestorAchRequest.GetList');
  });

  // investor section route stop

  // lender section route start

  Route::name('lender::')->prefix('lender')->middleware('auth')->group(function () {
      Route::get('/dashboard', [LenderController::class, 'dashboard'])->name('dashboard'); //todo_dec
  });

  // lender section route stop

  //branch_manager route start
  Route::name('branch::')->prefix('branch')->middleware('branch_manager', 'auth')->group(function () {
      Route::get('/dashboard', [BranchManagerController::class, 'dashboard'])->name('dashboard'); //todo_dec

      Route::name('marketplace::')->prefix('marketplace')->middleware('auth')->group(function () {
          Route::get('/', [BranchManagerController::class, 'index'])->name('index'); //todo_dec
          Route::get('/create', [BranchManagerController::class, 'create'])->name('create'); //->middleware('viewer');
          Route::get('/edit/{id}', [BranchManagerController::class, 'edit'])->name('edit'); //->middleware('viewer');
          Route::post('/delete/{id}', [BranchManagerController::class, 'delete'])->name('delete'); //->middleware('viewer');
          Route::post('/create', [BranchManagerController::class, 'storeCreate'])->name('storeCreate');
          Route::post('/update/{id}', [BranchManagerController::class, 'update'])->name('update');
          Route::get('/data', [BranchManagerController::class, 'rowData'])->name('data');
      });
  });
  Route::get('apitest', [TestController::class, 'apitest']);
  Route::get('timeformat', [TestController::class, 'timeformat']);
  Route::get('export-db', [CommandsController::class, 'export_db']);
  Route::get('tst', [CommandsController::class, 'tst']);

Route::prefix('fundings')->group(function () {
    Route::get('/', [Admin\FundingController::class, 'index']);
    Route::get('/logout', [Admin\FundingController::class, 'logout']);
    Route::get('/forgot-password', [Admin\FundingController::class, 'forgot_password']);
    Route::get('/login', [Admin\FundingController::class, 'login']);
    Route::post('/login', [Admin\FundingController::class, 'check_login']);
    Route::get('/signup', [Admin\FundingController::class, 'signup']);
    Route::post('/signup', [Admin\FundingController::class, 'doSignup']);
    Route::any('/marketplace/{industry}', [Admin\FundingController::class, 'marketplace']);
    Route::any('/marketplace', [Admin\FundingController::class, 'marketplace']);
    Route::any('/getMerchantDetails', [Admin\FundingController::class, 'getMerchantDetails'])->name('fundings.getMerchantDetails');
    Route::any('/postMarketplaceFund', [Admin\FundingController::class, 'postMarketplaceFund'])->name('fundings.postMarketplaceFund');
    Route::any('/postInvestorAchRequestSend', [Admin\FundingController::class, 'postInvestorAchRequestSend'])->name('fundings.postInvestorAchRequestSend');
    Route::get('/{id}/marketplace-details', [Admin\FundingController::class, 'marketplace_details']);
    Route::get('/make_payment/{id}/{amount}', [Admin\FundingController::class, 'make_credit_payment']);
    Route::post('/process_stripe_payment', [Admin\FundingController::class, 'process_stripe_payment_investor']);
    Route::any('/updatebank', [Admin\FundingController::class, 'updateBank']);
    Route::get('/about-us', [Admin\FundingController::class, 'about_us']);
    Route::get('/how-it-works', [Admin\FundingController::class, 'how_it_works']);
    Route::any('/contact-us', [Admin\FundingController::class, 'contact_us']);
    Route::any('/profile', [Admin\FundingController::class, 'profile']);
    Route::any('/privacy-policy', [Admin\FundingController::class, 'privacy_policy']);
    Route::any('/privacy-policy', [Admin\FundingController::class, 'privacy_policy']);
    Route::any('/terms-and-condition', [Admin\FundingController::class, 'terms_and_condition']);
});

  //todo move
  Route::get('pm/{id}/make-payment/{amount}', [Admin\PaymentController::class, 'make_payment'])->name('get-make-payment');
  Route::post('pm/make-payment', [Admin\PaymentController::class, 'make_payment'])->name('make-payment');
  Route::get('merchants/make-payment', [Admin\PaymentController::class, 'make_stripe_payment'])->name('merchant-make-stripe-payment');
  Route::get('investors/make-payment', [Admin\PaymentController::class, 'make_stripe_payment'])->name('investor-make-stripe-payment');
  Route::post('payment/process-stripe-payment', [Admin\PaymentController::class, 'process_stripe_payment'])->name('process-stripe-payment');
  Route::post('payment/process-stripe-payment-investor', [Admin\PaymentController::class, 'process_stripe_payment_investor'])->name('process-stripe-payment-investor');
  Route::get('payment/test', [Admin\PaymentController::class, 'postMarketplaceFund']);
  //branch_manager route stop

  // Route for merchant app in Web
  Route::get('/merchants/{any?}', [MerController::class, 'index'])->where('any', '.*');
  //  Route for investor side app
  Route::get('/investors/{any?}', [InvController::class, 'index'])->where('any', '.*');
  //  Route for investor side app
  Route::get('v/admin/{any?}', [AdminViewController::class, 'index'])->where('any', '.*');
  Route::get('/send-mail-random-number2323', function () {
      Mail::to('newuser@example.com')->send(new MailtrapExample());

      return 'A message has been sent to Mailtrap!';
  });

Route::middleware(config('fortify.middleware', ['web']))->group(function () {
    $enableViews = config('fortify.views', true);

    // Authentication...
    if ($enableViews) {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])
            ->middleware(['guest'])
            ->name('login');
    }

    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest',
            $limiter ? 'throttle:'.$limiter : null,
        ]))->name('two-factor-login');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout-two-factor');
    // Registration...
    if (Features::enabled(Features::registration())) {
        if ($enableViews) {
            Route::get('/register', [RegisteredUserController::class, 'create'])
                ->middleware(['guest'])
                ->name('register');
        }

        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware(['guest']);
    }

    // Email Verification...
    if (Features::enabled(Features::emailVerification())) {
        if ($enableViews) {
            Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware(['auth'])
                ->name('verification.notice');
        }

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['auth', 'signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth', 'throttle:6,1'])
            ->name('verification.send');
    }

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
            ->middleware(['auth'])
            ->name('user-profile-information.update');
    }

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put('/user/password', [PasswordController::class, 'update'])
            ->middleware(['auth'])
            ->name('user-password.update');
    }

    // Password Confirmation...
    if ($enableViews) {
        Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->middleware(['auth'])
            ->name('password.confirm');
    }

    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware(['auth'])
        ->name('password.confirmation');

    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware(['auth']);

    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {
        if ($enableViews) {
            //     Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
            //         ->middleware(['guest'])
            //         ->name('two-factor.login');
            // }
            Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->middleware(['guest'])
                ->name('two-factor.login');
        }
        Route::post('/two-factor-challenge-login', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest',
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
            ]));

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest',
                $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
            ]));

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? ['auth', 'password.confirm']
            : ['auth'];

        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware);

        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware);

        Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware);

        Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware);

        Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware);
    }
});
