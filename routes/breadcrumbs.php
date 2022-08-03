<?php

/* Accounts */

// Dashboard
Breadcrumbs::for('admin::dashboard::index', function ($trail) {
    $trail->push('Dashboard', route('admin::dashboard::index'));
});

// Dashboard > All Account
Breadcrumbs::for('admin::investors::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Account', route('admin::investors::index'));
});

//Dashboard > All Account > Create Account
Breadcrumbs::for('investorcreate', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('Create Account');
});

//Dashboard > All Account > Portfolio
Breadcrumbs::for('portfolio', function ($trail, $investor) {
    $trail->parent('admin::investors::index');
    $trail->push('Portfolio', route('admin::investors::portfolio', $investor->id));
});
Breadcrumbs::for('Prefreturn', function ($trail, $investors) {
    $trail->parent('Pref_return_list',$investors);
    $trail->push('Add/Edit Pref Return', route('admin::investors::edit-pref-return', $investors->id));
});
Breadcrumbs::for('ReservedLiquidity', function ($trail, $investors) {
    $trail->parent('reserved_liquidity_list',$investors);
    $trail->push('Add/Edit Reserved Liquidity', route('admin::investors::create-reserve-liquidity', $investors->id));
});



//Dashboard > All Account > Portfolio >Transfer to velocity
Breadcrumbs::for('transfer_to_velocity', function ($trail, $Investor) {
    $trail->parent('portfolio', $Investor);
    $trail->push('Investor Ach Debit Request', route('admin::investors::achRequest', $Investor->id));
});
//Dashboard > All Account > Portfolio >Transfer to bank
Breadcrumbs::for('transfer_to_bank', function ($trail, $Investor) {
    $trail->parent('portfolio', $Investor);
    $trail->push('Investor Ach Credit Request', route('admin::investors::achCreditRequest', $Investor->id));
});

//Dashboard > All Account > Portfolio >Pref Return 
Breadcrumbs::for('Pref_return_list', function ($trail, $investors) {
    $trail->parent('portfolio', $investors);
    $trail->push('Pref Return', route('admin::investors::investor-pref-return', $investors->id));
});

//Dashboard > All Account > Portfolio >Reserved Liquidity 
Breadcrumbs::for('reserved_liquidity_list', function ($trail, $investors) {
    $trail->parent('portfolio', $investors);
    $trail->push('Reserved Liquidity', route('admin::investors::investor-reserve-liquidity', $investors->id));
});
//Dashboard > All Account > Portfolio >Credit Card(Investor Credit Card Payment)
Breadcrumbs::for('credit_card', function ($trail, $Investor) {
    $trail->parent('portfolio', $Investor);
    $trail->push('Investor Credit Card Payment', route('admin::investors::creditcard_payment', $Investor->id));
});
//Dashboard > All Account > Transactions
Breadcrumbs::for('transactions', function ($trail, $this_investor) {
    $trail->parent('admin::investors::index', $this_investor);
    $trail->push('Transactions', route('admin::investors::transaction::index', $this_investor->id));
});

//Dashboard > All Account > AdvancePlusInvestments
Breadcrumbs::for('AdvancePlusInvestments', function ($trail, $this_investor) {
    $trail->parent('admin::investors::index', $this_investor);
    $trail->push('Advance Plus Investments Report', route('admin::reports::AdvancePlusInvestments', $this_investor->id));
});


//Dashboard > All Account > Transactions >Create
Breadcrumbs::for('create_transaction', function ($trail, $Investor) {
    $trail->parent('transactions', $Investor);
    $trail->push('Create Transaction', route('admin::investors::transaction::create', $Investor->id));
});
//Dashboard > All Account > Transactions >Edit
Breadcrumbs::for('edit_transaction', function ($trail, $Investor) {
    $trail->parent('transactions', $Investor);
    $trail->push('Edit Transaction', route('admin::investors::transaction::create', $Investor->id));
});
//Dashboard > All Account > Documents
Breadcrumbs::for('documents', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('Documents');
});

//Dashboard > All Account > Investor bank
Breadcrumbs::for('Investorbank', function ($trail, $investor) {
    $trail->parent('admin::investors::index', $investor);
    $trail->push('Bank Details', route('admin::investors::bank_details', $investor->id));
});
//Dashboard > All Account > Investor bank > Create bank details
Breadcrumbs::for('create_investor_bank', function ($trail, $investor) {
    $trail->parent('Investorbank', $investor);
    $trail->push('Create Bank Details');
});
//Dashboard > All Account > Investor bank > Edit bank details
Breadcrumbs::for('edit_investor_bank', function ($trail, $investor) {
    $trail->parent('Investorbank', $investor);
    $trail->push('Edit Bank Details');
});
//Dashboard > All Account > Edit
Breadcrumbs::for('investoredit', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('Edit');
});

//Dashboard >Account > Generated PDF/CSV
Breadcrumbs::for('admin::generated-pdf-csv', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('Generated PDF/CSV', route('admin::generated-pdf-csv'));
});

//Dashboard >Account > Generate Statement for Investors
Breadcrumbs::for('admin::pdf_for_investors', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('Generate Statement for Investors', route('admin::pdf_for_investors'));
});

//Dashboard >Account > Generated PDF/CSV >View
Breadcrumbs::for('generatedPdfView', function ($trail) {
    $trail->parent('admin::generated-pdf-csv');
    $trail->push('Investor Syndication Report');
});

//Dashboard >Accounts > Investors Faq
Breadcrumbs::for('InvestorFAQList', function ($trail) {
    $trail->parent('admin::investors::index');
    $trail->push('All Faqs', route('admin::investors::faq.index'));
});

// Dashboard >Accounts > Investors Faqs >Create Faq
Breadcrumbs::for('investorsCreateFAQ', function ($trail) {
    $trail->parent('InvestorFAQList');
    $trail->push('Create Faq');
});

//Dashboard >Accounts > Investors Faqs >Edit Faq
Breadcrumbs::for('investorEditFAQ', function ($trail) {
    $trail->parent('InvestorFAQList');
    $trail->push('Edit Faq');
});

//Dashboard > Merchants > merchant Faqs
Breadcrumbs::for('merchantFaq', function ($trail) {
    $trail->parent('admin::merchants::index');
    $trail->push('All Faqs');
});
// Dashboard >Merchants > Merchants Faqs >Create Faq
Breadcrumbs::for('merchantFaqCreate', function ($trail) {
    $trail->parent('merchantFaq');
    $trail->push('Create Faq');
});

//Dashboard >Merchants > Merchants Faqs >Edit Faq
Breadcrumbs::for('merchantFaqEdit', function ($trail) {
    $trail->parent('merchantFaq');
    $trail->push('Edit Faq');
});

/* Collection Users */

//Dashboard >All Collection Users
Breadcrumbs::for('admin::collection_users::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Collection Users', route('admin::collection_users::index'));
});

//Dashboard >All Collection Users >Create
Breadcrumbs::for('collectionsUsersCreate', function ($trail) {
    $trail->parent('admin::collection_users::index');
    $trail->push('Create Collection User');
});
//Dashboard >All Collection Users >Edit
Breadcrumbs::for('collectionsUsersEdit', function ($trail) {
    $trail->parent('admin::collection_users::index');
    $trail->push('Edit Collection User');
});

//Dashboard >All companies
Breadcrumbs::for('admin::sub_admins::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Companies', route('admin::sub_admins::index'));
});

// Dashboard > All companies ->Create companies
Breadcrumbs::for('admin::sub_admins::create', function ($trail) {
    $trail->parent('admin::sub_admins::index');
    $trail->push('Create Companies', route('admin::sub_admins::create'));
});

//Dashboard > All companies > Edit companies
Breadcrumbs::for('admin::collection_users::edit', function ($trail) {
    $trail->parent('admin::sub_admins::index');
    $trail->push('Edit Companies');
});
//Dashboard >Admin user
Breadcrumbs::for('admin::admins::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Admin User', route('admin::admins::index'));
});

// Dashboard > Admin user ->Create Admin user
Breadcrumbs::for('create_admin_user', function ($trail) {
    $trail->parent('admin::admins::index');
    $trail->push('Create Admin User');
});

//Dashboard > Admin user > Edit Admin user
Breadcrumbs::for('edit_admin_user', function ($trail) {
    $trail->parent('admin::admins::index');
    $trail->push('Edit Admin User');
});
//Dashboard >All Editors
Breadcrumbs::for('admin::editors::show_editors', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Editors', route('admin::editors::show_editors'));
});

// Dashboard > All Editors ->Create Editors
Breadcrumbs::for('create_editors', function ($trail) {
    $trail->parent('admin::editors::show_editors');
    $trail->push('Create');
});

//Dashboard > All Editors> Edit Editors
Breadcrumbs::for('edit_editors', function ($trail) {
    $trail->parent('admin::editors::show_editors');
    $trail->push('Edit');
});
//Dashboard >All Lenders
Breadcrumbs::for('admin::lenders::show_lenders', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Lenders', route('admin::lenders::show_lenders'));
});

//Dashboard >All lenders >Create
Breadcrumbs::for('admin::lenders::create_lenders', function ($trail) {
    $trail->parent('admin::lenders::show_lenders');
    $trail->push('Create', route('admin::lenders::create_lenders'));
});
// Dashboard >All lenders >Edit
Breadcrumbs::for('lender_edit', function ($trail) {
    $trail->parent('admin::lenders::show_lenders');
    $trail->push('Edit');
});
// Dashboard >All lenders >View
Breadcrumbs::for('lender_view', function ($trail) {
    $trail->parent('admin::lenders::show_lenders');
    $trail->push('View');
});

// Dashboard >All lenders >Lender Settings
Breadcrumbs::for('lenderSettings', function ($trail) {
    $trail->parent('admin::lenders::show_lenders');
    $trail->push('Lender Settings');
});

//Dashboard >All Viewers
Breadcrumbs::for('admin::viewers::show-viewer', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Viewers', route('admin::viewers::show-viewer'));
});

//Dashboard >All Viewers >Create
Breadcrumbs::for('admin::viewers::create-viewer', function ($trail) {
    $trail->parent('admin::viewers::show-viewer');
    $trail->push('Create', route('admin::viewers::create-viewer'));
});
// Dashboard >All Viewers >Edit
Breadcrumbs::for('viewEdit', function ($trail) {
    $trail->parent('admin::viewers::show-viewer');
    $trail->push('Edit');
});

//Dashboard >Users and Roles
Breadcrumbs::for('admin::roles::show-user-role', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Users and Roles', route('admin::roles::show-user-role'));
});

//Dashboard >Users and Roles >Create
Breadcrumbs::for('admin::roles::create-user', function ($trail) {
    $trail->parent('admin::roles::show-user-role');
    $trail->push('Create', route('admin::roles::create-user'));
});
 // Dashboard >Users and Roles >Edit
Breadcrumbs::for('userRolesEdit', function ($trail) {
    $trail->parent('admin::roles::show-user-role');
    $trail->push('Edit');
});

Breadcrumbs::for('merchantUser', function ($trail) {
    $trail->parent('admin::merchants::merchantUser');
    $trail->push('Edit');
});

//Dashboard >Roles and Permission
Breadcrumbs::for('admin::roles::show-role', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Roles and Permissions', route('admin::roles::show-role'));
});

//Dashboard >Roles and Permission >Create
Breadcrumbs::for('admin::roles::create-role', function ($trail) {
    $trail->parent('admin::roles::show-role');
    $trail->push('Create', route('admin::roles::create-role'));
});
  // Dashboard >Roles and Permission >Permissions
Breadcrumbs::for('rolesPermissions', function ($trail) {
    $trail->parent('admin::roles::show-role');
    $trail->push('Permissions');
});

//Dashboard >Modules
Breadcrumbs::for('admin::roles::show-modules', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Modules', route('admin::roles::show-modules'));
});

//Dashboard >Modules >Create
Breadcrumbs::for('admin::roles::create-module', function ($trail) {
    $trail->parent('admin::roles::show-modules');
    $trail->push('Create', route('admin::roles::create-module'));
});

//Dashboard >Modules >Edit
Breadcrumbs::for('Edit_module', function ($trail) {
    $trail->parent('admin::roles::show-modules');
    $trail->push('Edit');
});

//Dashboard >User Firewall
Breadcrumbs::for('admin::firewall::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('User Firewall', route('admin::firewall::index'));
});

//Dashboard >User Firewall >Edit
Breadcrumbs::for('firewallEdit', function ($trail) {
    $trail->parent('admin::firewall::index');
    $trail->push('Edit');
});

//Dashboard >All Merchants
Breadcrumbs::for('admin::merchants::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Merchants', route('admin::merchants::index'));
});

//Dashboard >All Merchants > view
Breadcrumbs::for('merchantView', function ($trail, $merchant) {
    $trail->parent('admin::merchants::index');
    $trail->push('Merchant Details', route('admin::merchants::view', $merchant->id));
});
//Dashboard >All Merchants > view ->Documents upload
Breadcrumbs::for('merchantDocumentsupload', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Documents upload', route('admin::merchants::view', $merchant->id));
});
//Dashboard >All Merchants > view ->Merchant Investor Edit
Breadcrumbs::for('merchantInvestorEdit', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Merchant Investor Edit', route('admin::merchants::view', $merchant->id));
});

//Dashboard >All Merchants > view ->Notes
Breadcrumbs::for('merchantNotes', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Notes');
});
//Dashboard >All Merchants > view ->Assign Investors Based On Liquidity
Breadcrumbs::for('AssignInvestorsBasedOnLiquidity', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Assign Investors Based On Liquidity');
});
//Dashboard >All Merchants > view ->Bank Details List
Breadcrumbs::for('merchantBankAccounts', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Bank Details List', route('admin::merchants::bank.index', $merchant->id));
});
//Dashboard >All Merchants > view ->Bank Details List >Create Bank Account
Breadcrumbs::for('merchantCreateBankAccounts', function ($trail, $merchant) {
    $trail->parent('merchantBankAccounts', $merchant);
    $trail->push('Create Bank Account');
});
//Dashboard >All Merchants > view ->Bank Details List >Edit Bank Account
Breadcrumbs::for('merchantEditBankAccounts', function ($trail, $merchant) {
    $trail->parent('merchantBankAccounts', $merchant);
    $trail->push('Edit Bank Account');
});
//Dashboard >All Merchants > view >ACH Terms
Breadcrumbs::for('Payment_term', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('ACH Terms');
});
//Dashboard >All Merchants > view >Add Payment
Breadcrumbs::for('addPayment', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Add Payment');
});
//Dashboard >All Merchants > view > Merchant Activity Log
Breadcrumbs::for('merchantLog', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Merchant Activity Log');
});
//Dashboard >All Merchants > view > Merchant Story
Breadcrumbs::for('merchantStory', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('Merchant Story');
});
//Dashboard >All Merchants > view > Merchant FAQ
Breadcrumbs::for('merchantFAQ', function ($trail, $merchant) {
    $trail->parent('merchantView', $merchant);
    $trail->push('All Faqs', route('admin::merchants::merchantFaq.faq.index', $merchant->id));
});
//Dashboard >All Merchants > view > Merchant FAQ > Create FAQ
Breadcrumbs::for('merchantCreateFAQ', function ($trail, $merchant) {
    $trail->parent('merchantFAQ', $merchant);
    $trail->push('Create New FAQ');
});
//Dashboard >All Merchants > view > Merchant FAQ > Edit FAQ
Breadcrumbs::for('merchantEditFAQ', function ($trail, $merchant) {
    $trail->parent('merchantFAQ', $merchant);
    $trail->push('Edit FAQ');
});
//Dashboard >All Merchants > Requests
Breadcrumbs::for('merchantRequests', function ($trail) {
    $trail->parent('admin::merchants::index');
    $trail->push('Requests');
});
//Dashboard >All Merchants > Edit
Breadcrumbs::for('merchantEdit', function ($trail) {
    $trail->parent('admin::merchants::index');
    $trail->push('Edit');
});
//Dashboard >All Merchants > Create
Breadcrumbs::for('merchantCreate', function ($trail) {
    $trail->parent('admin::merchants::index');
    $trail->push('Create');
});

//Dashboard >Graph
Breadcrumbs::for('admin::percentageDeal', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Graph', route('admin::percentageDeal'));
});
//Dashboard >Change to Default
Breadcrumbs::for('admin::change_merchant_status', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Change Merchant Status', route('admin::change_merchant_status'));
});
//Dashboard >Change Advanced Status
Breadcrumbs::for('admin::change_advanced_status', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Change To Advance completed Status', route('admin::change_advanced_status'));
});
//Dashboard >Generate PDF For Merchants
Breadcrumbs::for('admin::merchants-statements-create', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Generate PDF For Merchants', route('admin::merchants-statements-create'));
});

//Dashboard >Generated PDF For Merchants
Breadcrumbs::for('admin::merchants-statements', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Generated Statement Manager', route('admin::merchants-statements'));
});

//Dashboard >Investor Marketing Offers
Breadcrumbs::for('admin::investorMarketOfferList', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Marketing Offers', route('admin::investorMarketOfferList'));
});
//Dashboard >Investor Marketing Offers >Create
Breadcrumbs::for('investor_marketing_offers_create', function ($trail) {
    $trail->parent('admin::investorMarketOfferList');
    $trail->push('Create');
});
 //Dashboard >Investor Marketing Offers >Edit
Breadcrumbs::for('investor_marketing_offers_edit', function ($trail) {
    $trail->parent('admin::investorMarketOfferList');
    $trail->push('Edit');
});

//Dashboard >Merchant Marketing Offers
Breadcrumbs::for('admin::merchantMarketOfferList', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant Marketing Offers', route('admin::merchantMarketOfferList'));
});
//Dashboard >Merchant Marketing Offers >Create
Breadcrumbs::for('merchant_marketing_offers_create', function ($trail) {
    $trail->parent('admin::merchantMarketOfferList');
    $trail->push('Create');
});
// // //Dashboard >Merchant Marketing Offers >Edit
Breadcrumbs::for('merchant_marketing_offers_edit', function ($trail) {
    $trail->parent('admin::merchantMarketOfferList');
    $trail->push('Edit');
});
//Dashboard >Transaction Report
Breadcrumbs::for('admin::investors::transactionreport', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Transaction Report', route('admin::investors::transactionreport'));
});
// //Dashboard >Transaction Report >Create
Breadcrumbs::for('admin::bills::create', function ($trail) {
    $trail->parent('admin::investors::transactionreport');
    $trail->push('Create Bills', route('admin::bills::create'));
});
//Dashboard >Import Bills
Breadcrumbs::for('admin::bills::import_bill', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Import Bills', route('admin::bills::import_bill'));
});
//Dashboard >Reconciliation Request
Breadcrumbs::for('admin::merchants::reconcilation-request', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Reconciliation Request', route('admin::merchants::reconcilation-request'));
});
//Dashboard >Velocity Distributions
Breadcrumbs::for('admin::vdistribution::lists', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Velocity Distributions', route('admin::vdistribution::lists'));
});
// //Dashboard >Velocity Distributions >Create
Breadcrumbs::for('admin::vdistribution::createVdistribution', function ($trail) {
    $trail->parent('admin::vdistribution::lists');
    $trail->push('Create', route('admin::vdistribution::createVdistribution'));
});
//Dashboard >Velocity Distributions >Edit
Breadcrumbs::for('velocityDistributionEdit', function ($trail) {
    $trail->parent('admin::vdistribution::lists');
    $trail->push('Edit');
});
//Dashboard >Lender Payment Generation
Breadcrumbs::for('admin::payments::lender-payment-generation', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Lender Payment Generation');
});
//Dashboard >Lender Payment Generation
Breadcrumbs::for('admin::merchants::investor_transactions', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Transactions');
});
//Dashboard >Pending Transactions
Breadcrumbs::for('pendingTransactions', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Pending Transactions');
});
//Dashboard >Send ACH
Breadcrumbs::for('admin::payments::ach-payment.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Send Merchant ACH');
});
//Dashboard >ACH Fees
Breadcrumbs::for('admin::payments::ach-fees.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant ACH Fees');
});
//Dashboard >ACH Status Check
Breadcrumbs::for('admin::payments::ach-requests.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant ACH Status Check');
});
//Dashboard >Investor ACH Status Check
Breadcrumbs::for('admin::payments::investor-ach-requests.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor ACH Status Check');
});
//Dashboard >Syndication payments
Breadcrumbs::for('admin::investors::syndication-payments', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push(' Investor Syndication Payments');
});
//Dashboard >Liquidity Log
Breadcrumbs::for('admin::reports::liquidity-log', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Liquidity Log');
});
//Dashboard >Merchant Liquidity Log
Breadcrumbs::for('admin::reports::liquidity-log-merchant', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant Liquidity Log');
});
//Dashboard >Merchant Status Log
Breadcrumbs::for('admin::merchant_status_log', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant Status Log');
});
//Dashboard >User Activity Log
Breadcrumbs::for('admin::activity-log.get.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('User Activity Log');
});
Breadcrumbs::for('admin::permission-log.get.index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Permission Log');
});
//Dashboard >Investor Transaction Log
Breadcrumbs::for('investor_transaction_log', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Transaction Log');
});
//Dashboard >Messages Log
Breadcrumbs::for('admin::messages::lists', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Messages Log');
});
//Dashboard >Mail Log
Breadcrumbs::for('admin::merchants::mail-log', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Mail Log');
});
//Dashboard >Templates
Breadcrumbs::for('admin::template::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Templates', route('admin::template::index'));
});

 //Dashboard > Templates >Create Template
Breadcrumbs::for('create_template', function ($trail) {
    $trail->parent('admin::template::index');
    $trail->push('Create');
});
 // Dashboard > Templates >Edit
Breadcrumbs::for('edit_template', function ($trail) {
    $trail->parent('admin::template::index');
    $trail->push('Edit');
});
// Dashboard > Advance Settings
Breadcrumbs::for('admin::settings::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Advance Settings');
});

// Dashboard > Advance Settings
Breadcrumbs::for('admin::settings::system_settings', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('System Settings');
});

// Dashboard > Re-assign
Breadcrumbs::for('re-assign', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Re-assign');
});
//Dashboard >All Status
Breadcrumbs::for('admin::sub_status::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Status', route('admin::sub_status::index'));
});

 //Dashboard > All Status >Add status
Breadcrumbs::for('add_status', function ($trail) {
    $trail->parent('admin::sub_status::index');
    $trail->push('Add Status');
});
 //Dashboard > All Status > Edit
Breadcrumbs::for('edit_status', function ($trail) {
    $trail->parent('admin::sub_status::index');
    $trail->push('Edit');
});
//Dashboard >All Label
Breadcrumbs::for('admin::label::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Label', route('admin::label::index'));
});

 //Dashboard > All Label >Add Label
Breadcrumbs::for('add_label', function ($trail) {
    $trail->parent('admin::label::index');
    $trail->push('Add Label');
});
 //Dashboard > All Label > Edit
Breadcrumbs::for('edit_label', function ($trail) {
    $trail->parent('admin::label::index');
    $trail->push('Edit');
});

//Dashboard >All Label
Breadcrumbs::for('admin::sub_status_flag::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('All Sub Status Flag', route('admin::sub_status_flag::index'));
});

 //Dashboard > All Label >Add Label
Breadcrumbs::for('add_sub_status_flag', function ($trail) {
    $trail->parent('admin::sub_status_flag::index');
    $trail->push('Add Sub Status Flag');
});
 //Dashboard > All Label > Edit
Breadcrumbs::for('edit_sub_status_flag', function ($trail) {
    $trail->parent('admin::sub_status_flag::index');
    $trail->push('Edit');
});


//Dashboard > Calender For Holidays
Breadcrumbs::for('admin::fullcalender', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Calender For Holidays');
});
//Dashboard >Liquidity Adjuster
Breadcrumbs::for('admin::admins::liquidity_adjuster', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Liquidity Adjuster', route('admin::admins::liquidity_adjuster'));
});

 //Dashboard > Liquidity Adjuster >Edit
Breadcrumbs::for('edit_liquidity_adjuster', function ($trail) {
    $trail->parent('admin::admins::liquidity_adjuster');
    $trail->push('Edit');
});

 //Dashboard > Default Rate Report
Breadcrumbs::for('admin::reports::default-rate-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Default Rate Report');
});
 //Dashboard > Default Rate Report(Merchant)
Breadcrumbs::for('admin::reports::default-rate-merchant-report-data', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Default Rate Report(Merchant)');
});
  //Dashboard > Delinquent Report
Breadcrumbs::for('admin::reports::delinquent-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Delinquent Report');
});
 //Dashboard > Payments Left Report
Breadcrumbs::for('admin::reports::payment-left-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Payments Left Report');
});
 //Dashboard > Lender Report
Breadcrumbs::for('admin::reports::lender-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Lender Report');
});
 //Dashboard > Profitability2
Breadcrumbs::for('admin::reports::profitability2', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Profitability Report');
});
//Dashboard > Profitability3
Breadcrumbs::for('admin::reports::profitability3', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Profitability Report');
});
//Dashboard > Profitability21
Breadcrumbs::for('admin::reports::profitability21', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Profitability Report');
});
//Dashboard > Profitability4
Breadcrumbs::for('admin::reports::profitability4', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Profitability Report');
});
//Dashboard > Investment Report
Breadcrumbs::for('admin::reports::investor', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investment Report');
});
//Dashboard > Commission Report
Breadcrumbs::for('admin::reports::upsell-commission', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Upsell Commission Report');
});

//Dashboard > Investor Assignment
Breadcrumbs::for('admin::reports::get-investor-assign-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Assignment');
});
//Dashboard > Investor Reassignment
Breadcrumbs::for('admin::reports::get-reassign-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Investor Reassignment');
});
//Dashboard > Liquidity Report
Breadcrumbs::for('admin::reports::liquidity-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Liquidity Report');
});
//Dashboard > Payments
Breadcrumbs::for('admin::reports::payments', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Payment Report');
});
//Dashboard > Revenue Recognition
Breadcrumbs::for('admin::merchants::export-deals2', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Revenue Recognition');
});
//Dashboard >Accrued Roi Report
Breadcrumbs::for('admin::reports::investor_interest_accured_report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Accrued Pref Return');
});
//Dashboard >Debt Investor Report
Breadcrumbs::for('admin::reports::investor-profit-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Debt Investor Report');
});
//Dashboard >Equity Investor Report
Breadcrumbs::for('admin::reports::equity-investor-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Equity Investor Report');
});
// //Dashboard >Total Portfolio Earnings
Breadcrumbs::for('admin::reports::dept-investor-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Total Portfolio Earnings');
});
 //Dashboard >Overpayment Report
Breadcrumbs::for('admin::reports::overpayment-report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Overpayment Report');
});
//Dashboard >Merchant Per Diff Report
Breadcrumbs::for('admin::reports::merchant_per_diff', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Merchant Per Diff Report');
});
//Dashboard >Velocity Profitability
Breadcrumbs::for('admin::reports::velocity-profitability', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Velocity Profitability');
});
//Dashboard >TaxReport
Breadcrumbs::for('admin::reports::tax_report', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Tax Report');
});
 //Dashboard >Anticipated Payment Report
Breadcrumbs::for('admin::reports::anticipated-payment', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Anticipated Payment Report', route('admin::reports::anticipated-payment'));
});
//Dashboard >Fees
Breadcrumbs::for('admin::reports::fees', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Fee Report');
});
//Dashboard >Logs
Breadcrumbs::for('admin::logs::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Logs');
});
//Dashboard >Visitor
Breadcrumbs::for('admin::visitor::index', function ($trail) {
    $trail->parent('admin::dashboard::index');
    $trail->push('Visitor');
});
