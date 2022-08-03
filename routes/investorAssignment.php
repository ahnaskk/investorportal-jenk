<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BillsController;
use App\Http\Controllers\Admin\InvestorController;
use App\Http\Controllers\Admin\MailboxController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SubadminController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvestorTransactionController;
use App\Http\Controllers\LenderController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MerchantBatchesController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TestController;
use  Illuminate\Support\Facades\Route;

/* if (env('APP_ENV') === 'production') {
  URL::forceSchema('https');
  }
 */

Route::get('/', [function () {
    return redirect('login');
}]);
Route::get('/test', [TestController::class, 'test']);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::name('admin::')->prefix('admin')->middleware('admin')->namespace('Admin')->group(function () {
    Route::get('/permssion_denied', [AdminUserController::class, 'permissionDenied']);
    Route::get('/emailConfig', [AdminUserController::class, 'config_email'])->name('email-config');
    Route::post('/emailConfig', [AdminUserController::class, 'config_email'])->name('email-config');
    Route::get('/rateConfig', [AdminUserController::class, 'config_rate'])->name('rate-config');
    Route::post('/rateConfig', [AdminUserController::class, 'config_rate'])->name('rate-config');
    Route::get('/table_repire', [MerchantController::class, 'table_repire']);
    Route::get('/mailbox', [MailboxController::class, 'index']);
    Route::get('/mailbox/{id}', [MailboxController::class, 'view']);
    Route::get('/getInvestorAdmin', [AdminUserController::class, 'getInvestorAdmin']);

    Route::name('dashboard::')->prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/view/{id}', [DashboardController::class, 'view'])->name('view');
        Route::get('/details/{id}', [DashboardController::class, 'details'])->name('details');
    });
    /* Pament */

    Route::name('notes::')->prefix('notes')->group(function () {
        Route::get('{id}/update/', [NotesController::class, 'update_s'])->name('update_s'); //only for single
        /* for multiple notes, disabled */
        Route::get('{id}/create/', [NotesController::class, 'create'])->name('create');
        Route::get('{id}/lists/', [NotesController::class, 'index'])->name('lists');
        Route::get('{id}/edit', [NotesController::class, 'edit'])->name('edit');
        Route::post('{id}/delete', [NotesController::class, 'delete'])->name('delete');
        Route::post('{id}/create', [NotesController::class, 'storeCreate'])->name('storeCreate');
        Route::post('{id}/update', [NotesController::class, 'update'])->name('update');
        Route::get('{id}/data', [NotesController::class, 'rowData'])->name('data');
    });

    Route::name('bills::')->prefix('bills')->group(function () {
        Route::get('update/', [BillsController::class, 'update_s'])->name('update_s'); //only for single
        Route::get('create/', [BillsController::class, 'create'])->name('create');
        Route::get('/', [BillsController::class, 'index'])->name('lists');
        Route::get('edit/{id}', [BillsController::class, 'edit'])->name('edit');
        Route::post('delete/{id}', [BillsController::class, 'delete'])->name('delete');
        Route::post('create', [BillsController::class, 'storeCreate'])->name('storeCreate');
        Route::post('update/{id}', [BillsController::class, 'update'])->name('update');
        Route::get('data', [BillsController::class, 'rowData'])->name('data');
        Route::post('export', [BillsController::class, 'export'])->name('export');
    });

    Route::name('vdistribution::')->prefix('vdistribution')->group(function () {
        Route::get('/', [InvestorTransactionController::class, 'vdistributions'])->name('lists');
        Route::get('data', [InvestorTransactionController::class, 'vRowData'])->name('data');
        Route::get('edit/{id}', [InvestorTransactionController::class, 'vEdit'])->name('edit');
        Route::post('update/{id}', [InvestorTransactionController::class, 'vUpdate'])->name('update');
        Route::post('export', [InvestorTransactionController::class, 'vExport'])->name('export');
        Route::get('/createVdistribution', [InvestorTransactionController::class, 'createVdistributions'])->name('createVdistribution');
        Route::post('/createVdistribution', [InvestorTransactionController::class, 'storeVdistributions'])->name('storeVdistribution');
        Route::get('update/', [BillsController::class, 'update_s'])->name('update_s'); //only for single

        /* for multiple notes, disabled */
        Route::get('create/', [BillsController::class, 'create'])->name('create');
        Route::post('delete/{id}', [BillsController::class, 'delete'])->name('delete');
        Route::post('create', [BillsController::class, 'storeCreate'])->name('storeCreate');
    });
    Route::name('merchant_investor::')->prefix('merchant_investor')->group(function () {
        Route::get('/create/{id}', [MerchantController::class, 'create_investor'])->name('create');
        Route::get('/create/', [MerchantController::class, 'create_investor'])->name('create');
        Route::get('/edit/{id}', [MerchantController::class, 'edit_investor'])->name('edit');
        Route::post('/update/', [MerchantController::class, 'update_investor'])->name('update');
        Route::post('/create/', [MerchantController::class, 'store_investor'])->name('create');
        Route::get('/assignInvestor/{id}', [MerchantController::class, 'assign_investor_based_on_liquidity'])->name('assign-investor');
        Route::get('/assignInvestor/', [MerchantController::class, 'assign_investor_based_on_liquidity'])->name('assign-investor');
        Route::post('/assignInvestor/', [MerchantController::class, 'assign_investor_based_on_liquidity'])->name('assign-investor');
        Route::get('/view', [MerchantController::class, 'index'])->name('view');
        Route::post('/delete/{id}', [MerchantController::class, 'delete_investor'])->name('delete');
        Route::post('/delete_investments', [MerchantController::class, 'delete_multi_investment'])->name('delete_investments');
        Route::post('/delete', [PaymentController::class, 'delete_multi_payment'])->name('multi_delete');
        Route::get('{mid}/documents/{iid}', [MerchantController::class, 'investorDocuments'])->name('document');
        Route::get('{mid}/documents/{iid}/view/{id}', [MerchantController::class, 'viewInvestorDoc'])->name('document::view');
        Route::post('{mid}/documents/{iid}', [MerchantController::class, 'investorDocumentUpload'])->name('document::upload-docs');

        /* Document uploader for merchant-investor */
        Route::post('{mid}/documents/{iid}/{docid}/update', [MerchantController::class, 'investorDocumentUpdate'])->name('document::update-docs');
        Route::post('{mid}/documents/{iid}/{docid}/delete', [MerchantController::class, 'investorDocumentDelete'])->name('document::delete-docs');
        Route::get('/all_documents', [MerchantController::class, 'getAllInvestorDocument'])->name('all_documents');
        Route::get('/documents_upload/{iid}', [MerchantController::class, 'uploadInvestorDocument'])->name('documents_upload');
        Route::post('/documents_upload/{iid}', [MerchantController::class, 'investorDocumentUploadByAdmin'])->name('documents_upload::upload-docs-admin');
        Route::post('/documents_upload/{iid}/{docid}/delete', [MerchantController::class, 'investorDocumentDeleteByAdmin'])->name('documents_upload::delete-docs');
        Route::get('/documents_upload/{iid}/view/{id}', [MerchantController::class, 'viewInvestorDocument'])->name('documents_upload::view');
    });

    Route::name('export::')->prefix('export')->group(function () {
        //admin reverse portfolio
        Route::post('/merchant-list', [ExportController::class, 'merchantList'])->name('merchant::list');
        Route::post('/merchant-details', [ExportController::class, 'merchantDetails'])->name('merchant::details');
        Route::post('/general-report', [ExportController::class, 'generalReport'])->name('general::report');
    });

    Route::name('merchants::')->prefix('merchants')->group(function () {
        Route::name('requests::')->prefix('requests')->group(function () {
            Route::get('/view/{id}', [MerchantController::class, 'requests'])->name('requests');
            Route::post('/approve/{id}', [MerchantController::class, 'requestsApprove'])->name('approve');
            Route::get('/delete/{id}', [MerchantController::class, 'requestsDelete'])->name('delete');
            //approve
        });
        Route::get('/requests_data/{mid}', [MerchantController::class, 'rowDataRequests'])->name('requests_data');
        Route::get('/', [MerchantController::class, 'index'])->name('index');
        Route::get('/index2', [MerchantController::class, 'index2'])->name('index2');
        Route::get('/create', [MerchantController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [MerchantController::class, 'edit'])->name('edit');
        Route::post('/delete/{id}', [MerchantController::class, 'delete'])->name('delete');
        Route::post('/create', [MerchantController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/update', [MerchantController::class, 'update'])->name('update');
        Route::post('/investor_status', [MerchantController::class, 'investorMerchantStatus'])->name('investorMerchantStatus');
        Route::get('/data', [MerchantController::class, 'rowData'])->name('data');
        Route::get('/merchant_data/{merchant_id}', [MerchantController::class, 'rowDataMerchant'])->name('merchant_data');
        Route::get('{mid}/documents', [MerchantController::class, 'marketplaceDocuments'])->name('document');
        Route::post('{mid}/documents', [MerchantController::class, 'marketplaceDocumentUpload'])->name('document::upload-docs');
        Route::get('{mid}/documents/{docid}', [MerchantController::class, 'viewMarketplaceDoc'])->name('document::view');
        Route::post('{mid}/documents/{docid}/update', [MerchantController::class, 'marketplaceDocumentUpdate'])->name('document::update-docs');
        Route::post('{mid}/documents/{docid}/delete', [MerchantController::class, 'marketplaceDocumentDelete'])->name('document::delete-docs');
        Route::get('/view/{id}', [MerchantController::class, 'view'])->name('view');
        Route::get('/export', [MerchantController::class, 'exportForm'])->name('export-deals');
        Route::post('/export', [MerchantController::class, 'exportDeals'])->name('export-deals');
        Route::get('/export2', [MerchantController::class, 'exportForm2'])->name('export-deals2');
        Route::post('/export2', [MerchantController::class, 'exportDeals2'])->name('export-deals2');
        Route::get('/re-assign', [MerchantController::class, 're_assign'])->name('re-assign');
        /* Change investment to other investor */
    });

    Route::name('sub_status::')->prefix('sub_status')->group(function () {
        Route::get('/', [StatusController::class, 'index'])->name('index');
        Route::get('/create', [StatusController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [StatusController::class, 'edit'])->name('edit');
        Route::post('/delete/{id}', [StatusController::class, 'delete'])->name('delete');
        Route::post('/create', [StatusController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/update', [StatusController::class, 'update'])->name('update');
        Route::get('/data', [StatusController::class, 'rowData'])->name('data');
    });

    Route::name('merchant_batches::')->prefix('merchant_batches')->group(function () {
        Route::get('/', [MerchantBatchesController::class, 'index'])->name('index');
        Route::get('/create', [MerchantBatchesController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [MerchantBatchesController::class, 'edit'])->name('edit');
        Route::post('/delete/{id}', [MerchantBatchesController::class, 'delete'])->name('delete');
        Route::post('/create', [MerchantBatchesController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/update', [MerchantBatchesController::class, 'update'])->name('update');
        Route::get('/data', [MerchantBatchesController::class, 'rowData'])->name('data');
    });

    //admin investor management
    Route::name('investors::')->prefix('investors')->group(function () {
        Route::get('/', [InvestorController::class, 'index'])->name('index');
        Route::get('/data', [InvestorController::class, 'rowData'])->name('data');
        Route::get('/create', [InvestorController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [InvestorController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [InvestorController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [InvestorController::class, 'delete'])->name('delete');
        Route::post('/create', [InvestorController::class, 'storeCreate'])->name('storeCreate');
        Route::get('/portfolio/{id}', [InvestorController::class, 'portfolio'])->name('portfolio');
        Route::post('/portfolio', [InvestorController::class, 'portfolio'])->name('portfolio-filter');
        Route::get('/bank/{id}', [InvestorController::class, 'bank'])->name('bank');
        Route::get('/documents/{id}', [InvestorController::class, 'documents'])->name('documents');
        Route::post('/updatebank', [InvestorController::class, 'updateBank'])->name('updateBank');
        Route::get('/portfolio', [InvestorController::class, 'portfolioLists'])->name('portfolioLists');
        Route::get('/transaction-report', [InvestorController::class, 'transactions'])->name('transactionreport');
        Route::post('/transaction-report', [InvestorController::class, 'transactionReportDownload'])->name('transactionreportdownload');

        Route::name('transaction::')->prefix('transactions')->group(function () {
            Route::get('/{id}/create', [InvestorTransactionController::class, 'create'])->name('create');
            Route::post('/{id}/export', [InvestorTransactionController::class, 'export'])->name('export');
            Route::get('/{id}', [InvestorTransactionController::class, 'index'])->name('index');
            Route::get('/{id}/{tid}/edit', [InvestorTransactionController::class, 'edit'])->name('edit');
            Route::post('/{id}/{tid}/update', [InvestorTransactionController::class, 'update'])->name('update');
            Route::post('/{id}/store', [InvestorTransactionController::class, 'store'])->name('store');
            Route::get('/{id}/{tid}/delete', [InvestorTransactionController::class, 'delete'])->name('delete');
            Route::get('/{id}/{tid}/status_change', [InvestorTransactionController::class, 'status_change'])->name('status_change');
        });
    });

    //admin investor management
    Route::name('branch_managers::')->prefix('branch_manager')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/data', [BranchController::class, 'rowData'])->name('data');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [BranchController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [BranchController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [BranchController::class, 'delete'])->name('delete');
        Route::post('/create', [BranchController::class, 'storeCreate'])->name('storeCreate');
    });

    Route::name('lenders::')->prefix('lender')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_lenders'])->name('create_lenders');
        Route::get('/', [AdminUserController::class, 'view_lenders'])->name('show_lenders');
        Route::get('/edit/{id}', [AdminUserController::class, 'editLenders'])->name('edit_lender');
        Route::post('/delete_lender/{id}/{type}', [AdminUserController::class, 'deleteUsers'])->name('delete_lender');
    });
    Route::name('editors::')->prefix('editor')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_editors'])->name('create_editors');
        Route::get('/', [AdminUserController::class, 'view_editors'])->name('show_editors');
        Route::get('/edit/{id}', [AdminUserController::class, 'editEditors'])->name('edit_editors');
        Route::post('/delete_editors/{id}/{type}', [AdminUserController::class, 'delete_editors'])->name('delete_editors');
    });

    //admin sub-admin

    Route::name('sub_admins::')->prefix('sub_admins')->group(function () {
        Route::get('/', [SubadminController::class, 'index'])->name('index');
        Route::get('/data', [SubadminController::class, 'rowData'])->name('data');
        Route::get('/create', [SubadminController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [SubadminController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SubadminController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [SubadminController::class, 'delete'])->name('delete');
        Route::post('/create', [SubadminController::class, 'storeCreate'])->name('storeCreate');
    });

    Route::name('lenders::')->prefix('lender')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_lenders'])->name('create_lenders');
        Route::get('/', [AdminUserController::class, 'view_lenders'])->name('show_lenders');
        Route::get('/edit/{id}', [AdminUserController::class, 'editLenders'])->name('edit_lender');
        Route::post('/delete_lender/{id}/{type}', [AdminUserController::class, 'deleteUsers'])->name('delete_lender');
    });

    Route::name('editors::')->prefix('editor')->group(function () {
        Route::get('/create', [AdminUserController::class, 'create_editors'])->name('create_editors');
        Route::get('/', [AdminUserController::class, 'view_editors'])->name('show_editors');
        Route::get('/edit/{id}', [AdminUserController::class, 'editEditors'])->name('edit_editors');
        Route::post('/delete_editors/{id}/{type}', [AdminUserController::class, 'delete_editors'])->name('delete_editors');
    });

    Route::name('admins::')->prefix('admin')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/data', [AdminUserController::class, 'rowData'])->name('data');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [AdminUserController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [AdminUserController::class, 'delete'])->name('delete');
        Route::post('/create', [AdminUserController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/save_lender_data', [AdminUserController::class, 'saveLenderData'])->name('save_lender_data');
        Route::get('/lenderdata', [AdminUserController::class, 'getallLenders'])->name('lenderdata');
        Route::post('/update_lender/{id}', [AdminUserController::class, 'updateLenders'])->name('update_lender');
        Route::get('/editordata', [AdminUserController::class, 'getallEditorsData'])->name('editordata');
        Route::post('/save_editor_data', [AdminUserController::class, 'saveEditorData'])->name('save_editor_data');
        Route::post('/update_editor/{id}', [AdminUserController::class, 'updateEditor'])->name('update_editor');
    });

    Route::name('payments::')->prefix('payment')->group(function () {
        Route::get('/create/', [PaymentController::class, 'create'])->name('create');
        Route::get('/create/{merchant_id}', [PaymentController::class, 'create'])->name('createForMerchant');
        Route::post('/store', [PaymentController::class, 'store'])->name('store');
        // Route::post('/store' , ['as' => 'store' , 'uses' => [PaymentController::class, 'store']]);
        Route::get('/open-items', [PaymentController::class, 'openItems'])->name('openitems');
        Route::get('/history', [PaymentController::class, 'history'])->name('history');
        Route::get('/data', [PaymentController::class, 'rowData'])->name('data');
        Route::post('/delete/{id}', [PaymentController::class, 'delete'])->name('delete');
    });

    /* reports */

    Route::name('reports::')->prefix('reports')->group(function () {
        Route::get('/collection', [ReportController::class, 'collection'])->name('collection');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::post('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::post('/payment-export', [ReportController::class, 'paymentExport'])->name('payment-export');
        Route::post('/investor-performance-export', [ReportController::class, 'investorPerformanceExport'])->name('investor-performance-export');
        Route::post('/lender-performance-export', [ReportController::class, 'lenderPerformanceExport'])->name('lender-performance-export');
        Route::get('/investor', [ReportController::class, 'investorReport'])->name('investor');
        Route::post('/investor-export', [ReportController::class, 'investorExport'])->name('investor-export');
        Route::get('/lender-performance', [ReportController::class, 'lenderPerformance'])->name('lender-performance');
        Route::get('/lender-data', [ReportController::class, 'lenderPerformanceData'])->name('lender-data');
        Route::get('/reassignReport', [ReportController::class, 'reAssignmentHistory'])->name('get-reassign-report');
        //Route::get('/showReport', ['as' => 'get-reassign-report', 'uses' => [ReportController::class, 'reAssignmentData']]);
        Route::get('/subadmin-investment', [ReportController::class, 'subadminInvestmentReport'])->name('subadmin-investment');
        Route::get('/subadmin-payment', [ReportController::class, 'subadminPaymentReport'])->name('subadmin-payment');
        Route::get('/investor-performance', [ReportController::class, 'investorPerformance'])->name('investor-performance');
        Route::get('/investor-data', [ReportController::class, 'investorPerformanceData'])->name('investor-data');
        Route::get('/investorAssignment', [ReportController::class, 'investorAssignmentReport'])->name('get-investor-assign-report');
    });
});

//investor route goes here
Route::name('investor::')->prefix('investor')->middleware('investor')->namespace('Investor')->group(function () {
    Route::get('/mailbox', [MailboxController::class, 'index']);
    Route::get('/mailbox/{id}', [MailboxController::class, 'view']);
    Route::get('/viewDocuments', [InvestorController::class, 'index']);
    Route::get('/get_documents', [InvestorController::class, 'getInvestorDocument'])->name('get_documents');
    Route::get('/documents_upload/{iid}/view/{id}', [InvestorController::class, 'viewInvestorDocument'])->name('documents_upload::view');

    /* Marketplace */
    Route::get('/mailbox', [MailboxController::class, 'index']);
    Route::get('/mailbox/{id}', [MailboxController::class, 'view']);
    Route::get('/viewDocuments', [InvestorController::class, 'index']);
    Route::get('/get_documents', [InvestorController::class, 'getInvestorDocument'])->name('get_documents');
    Route::get('/documents_upload/{iid}/view/{id}', [InvestorController::class, 'viewInvestorDocument'])->name('documents_upload::view');
    Route::get('/view_all_transactions', [InvestorController::class, 'transaction_view'])->name('view_all_transactions');

    /*Transactions*/
    Route::name('transactions::')->prefix('transactions ')->group(function () {
        Route::get('get_transactions', [InvestorController::class, 'getTransactions'])->name('get_transactions');
    });

    /*Marketplace*/

    Route::name('marketplace::')->prefix('marketplace')->group(function () {
        Route::get('{mid}/documents', [MarketplaceController::class, 'listdocs'])->name('document');
        Route::get('{mid}/documents/{id}', [MarketplaceController::class, 'viewDoc'])->name('document::view');
        Route::get('/index', [DashboardController::class, 'page'])->name('index'); //todo_dec
        Route::get('/marketplace', [MarketplaceController::class, 'list'])->name('index'); //todo_dec
        Route::post('funds_request', [MarketplaceController::class, 'funds_request'])->name('funds_request');
    });

    /* Portfolio */

    Route::name('dashboard::')->prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/view/{id}', [DashboardController::class, 'view'])->name('view');
        Route::get('/view/{id}/documents', [DashboardController::class, 'documents'])->name('documents');
        Route::post('/view/{id}/documents', [DashboardController::class, 'documentUpload'])->name('upload-docs');
        Route::post('/view/{id}/documents/{docid}/update', [DashboardController::class, 'documentUpdate'])->name('update-docs');
        Route::post('/view/{id}/documents/{docid}/delete', [DashboardController::class, 'documentDelete'])->name('delete-docs');
        Route::get('/view/{id}/documents/{docid}/view', [DashboardController::class, 'viewDoc'])->name('view-doc');
        Route::get('/details/{id}', [DashboardController::class, 'details'])->name('details');
    });

    Route::name('export::')->prefix('export')->group(function () {
        Route::post('/merchant-list', [ExportController::class, 'merchantList'])->name('merchant::list');
        Route::post('/merchant-details', [ExportController::class, 'merchantDetails'])->name('merchant::details');
        Route::post('/general-report', [ExportController::class, 'generalReport'])->name('general::report');
    });

    Route::get('/open-items', [DashboardController::class, 'openItems'])->name('openitems');
    Route::name('report::')->prefix('report')->group(function () {
        Route::get('/general', [ReportController::class, 'general'])->name('general');
    });

    Route::name('statements::')->prefix('report')->group(function () {
        Route::get('/weekly-statement', [StatementController::class, 'weekly'])->name('weekly');
        Route::get('/weekly-statement/{id}', [StatementController::class, 'viewWeeklyReportDocument'])->name('weekly-view');
    });
});
Route::name('lender::')->prefix('lender')->namespace('Lender')->group(function () {
    Route::get('/dashboard', [LenderController::class, 'dashboard'])->name('dashboard'); //todo_dec
});

//branch_manager route goes here
Route::name('branch::')->prefix('branch')->middleware('branch_manager')->namespace('Investor')->group(function () {
    Route::get('/dashboard', [BranchManagerController::class, 'dashboard'])->name('dashboard'); //todo_dec

    Route::name('marketplace::')->prefix('marketplace')->group(function () {
        Route::get('/', [BranchManagerController::class, 'index'])->name('index'); //todo_dec
        Route::get('/create', [BranchManagerController::class, 'create'])->name('create');
        Route::get('/edit/{id}', [BranchManagerController::class, 'edit'])->name('edit');
        Route::post('/delete/{id}', [BranchManagerController::class, 'delete'])->name('delete');
        Route::post('/create', [BranchManagerController::class, 'storeCreate'])->name('storeCreate');
        Route::post('/update/{id}', [BranchManagerController::class, 'update'])->name('update');
        Route::get('/data', [BranchManagerController::class, 'rowData'])->name('data');
    });
});
