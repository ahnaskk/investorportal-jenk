describe('All Merchants', () => {
    beforeEach(() => { 
        cy.viewport(2000, 1700)
        cy.login({ email:'1email.33433@iocod.com'})
        cy.visit('/admin/dashboard')
        cy.get('#cy_merchants').contains('Merchants').should('be.visible').click()
        cy.get('#cy_all_merchants').contains('All Merchants').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants') 

    })
    it('All Merchants Page', () => {
        
        cy.get('#lender_id').should('be.visible')
        cy.get('#owner').should('be.visible')
        cy.get('#user_id').should('be.visible')
        cy.get('#status_id').should('be.visible')
        cy.get('#late_payment').should('be.visible')
        cy.get('#advance_type').should('be.visible')
        cy.get('#date_start1').should('be.visible')
        cy.get('#date_end1').should('be.visible')
        cy.get('#request_m').should('be.visible')
        cy.get('#substatus_flag').should('be.visible')
        cy.get('#label').should('be.visible')
        cy.get('#bank_account').should('be.visible')
        cy.get('#payment_pause').should('be.visible')
        cy.get('#mode_of_payment').should('be.visible')
        cy.contains('Check For Merchant With Marketplace').should('be.visible')
        cy.contains('Not Started').should('be.visible')
        cy.contains('Paid Off').should('be.visible')
        cy.contains('Stop Payment').should('be.visible')
        cy.contains('Over Payment').should('be.visible')
        cy.contains('Not invested').should('be.visible')
        cy.get('#date_filter').should('be.visible')
        cy.get('#form_filter').should('be.visible')
        cy.contains('Add Merchant').should('be.visible')
        //filtering based on status
        cy.get('#status_id').should('be.visible').select('Active Advance',{force:true})
        cy.get('[value="Apply Filter"]').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants')
    })
    it('Checking AllMerchants page (Table buttons and links)', () => {
        
        cy.get('#dataTableBuilder').contains('Requests').should('be.visible').first().click({ force:true })
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/requests/view/')
        cy.contains('Merchant Lists').should('be.visible').click({ force:true })
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants')
        cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td:nth-child(14) > a:nth-child(2)').should('be.visible').first().click({ force:true })
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/edit/')
        cy.contains('View Merchant').should('be.visible')
        cy.contains('Update').should('be.visible')
        cy.contains('List Merchants').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants')
        cy.get('[title="Delete"]').should('be.visible').first().click({force:true})
        cy.on('window:confirm', () => false)
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants')
       
    })
    it('Merchants View Page', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td:nth-child(3) > a:nth-child(1)').first().click({ force:true })
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('#sub_status_id').should('be.visible')
        cy.get('#m_investor_id').should('be.visible')
        //Notes
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(1) > a').contains('Notes').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/notes/')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/notes/')
        cy.contains('View Merchant').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        //credit card
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(2) > a').contains('Credit Card').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/creditcard-payment/')
        cy.go('back')
        //bank
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(3) > a').contains('Bank').should('be.visible').click({force:true})
        cy.url().should('include','/bank_accounts')
        cy.contains('Create Bank Account ').should('be.visible').click({force:true})
        cy.url().should('include','/bank_accounts/create')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('include','/bank_accounts/create')
        cy.contains('Back to list').should('be.visible').click({force:true})
        cy.url().should('include','/bank_accounts')
        cy.go('back' )
        cy.contains('Merchant Details').click({force:true})
        //ach terms
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(4) > a').contains('ACH Terms').should('be.visible').click({force:true})
        cy.url().should('include', '/terms')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-body > div.heading > div.right > a').contains('View').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        //edit
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(5) > a').contains('Edit').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/edit/')
        cy.contains('View Merchant').should('be.visible')
        cy.contains('List Merchants').should('have.attr', 'href', 'https://investorportal.test/admin/merchants').should('be.visible')
        cy.get('[value="Update"]').contains('Update').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        //Add payment
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(6) > a').contains('Add Payment').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/payment/create/')
        cy.get('[value="Unselect"]').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/payment/create/')
        cy.get('[value="Select All Investors"]') .should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/payment/create/')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/payment/create/')
        cy.contains('View merchant').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
       //Audit log
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(8) > a').contains('Audit Log').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/audit/Merchant/')
        cy.go('back' )
        //Log
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(9) > a').contains('Log').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/activity-logs/')
        cy.get('[name="from_date1"]').should('be.visible')
        cy.get('[name="to_date1"]').should('be.visible')
        cy.get('[name="search_type"]').should('be.visible')
        cy.get('[name="search_action"]').should('be.visible')
        cy.get('#apply_filter').contains('Apply Filter').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/activity-logs/')
        cy.go('back' )
        //Pay off letter
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(10) > a').contains('PayOff Letter').should('be.visible')
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        // cy.task('downloads', 'cypress/downloads').then(before => {
        //     cy.get('div.merchant-btn-wrap > ul > li:nth-child(10) > a').contains('PayOff Letter').should('be.visible').click({force:true})
        //     // do the download 
        //     cy.task('downloads', 'cypress/downloads').then(after => {
        //       expect(after.length).to.be.eq(before.length +1)  
        //     })
        //   })
        //balance report
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(11) > a').contains('Balance Report').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        // upload docs
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(12) > a').contains('Upload Docs').should('be.visible').click({force:true})
        cy.url().should('include','/documents')
        cy.go('back' )
        //date wise investor payment
        cy.get('div.merchant-btn-wrap > ul > li:nth-child(13) > a').contains('Date Wise Investor Payment').should('be.visible')
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link active"]').contains('Investors').should('have.attr', 'href', '#InvestorTab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('Payments').should('have.attr', 'href', '#PaymentTableTab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('ACH Schedule Of Payments').should('have.attr', 'href', '#ACHTableTab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('Transaction').should('have.attr', 'href', '#TransactionTab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('Progress Level').should('have.attr', 'href', '#Principal_Porfit_LevelTab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('Expected Share And Given Share').should('have.attr', 'href', '#Expected_Share_And_Given_Tab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('[class="nav-link"]').contains('Expected RTR And Given RTR').should('have.attr', 'href', '#Expected_RTR_And_Given_Tab').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(1) > a').contains('Investors').click({force:true})
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(15) > a:nth-child(1)').should('be.visible').click({force:true})
        cy.url().should('include', '/documents/')
        cy.go('back' )
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(15) > a:nth-child(2)').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/merchant_investor/edit/')
        cy.contains('Go Back To Merchant View').should('be.visible').click({force:true})
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchants/view/')
    })
})
describe('Merchants Test without all mer', () => {    
    beforeEach(() => { 
        cy.viewport(2000, 1700)
        cy.login({ email:'1email.33433@iocod.com'})
        cy.visit('/admin/dashboard')
        cy.get('#cy_merchants').contains('Merchants').should('be.visible').click()
    })
    it('Create Merchant', ()=>{
        cy.contains('Create Merchants').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/create')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/create')
        cy.contains('List Merchants').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants')
    })
    it('Graph', ()=>{
        cy.contains('Graph').should('have.attr', 'href', 'https://investorportal.test/admin/percentage_deal').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
        cy.get('#date_start1').should('be.visible').click({force:true})
        cy.get('#date_end1').should('be.visible').click({force:true})
        cy.get('#attribute').should('be.visible')
        cy.get('#graph_value').should('be.visible')
        cy.get('#label').should('be.visible')
        cy.get('#lender').should('be.visible')
        cy.get('#owner').should('be.visible')
        cy.get('#invested_amount').should('be.visible')
        cy.get('#clearFilterBtn').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
        cy.get('#applyFilterBtn').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
        cy.get('#form_filter').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
        cy.get('#myChart_div').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/percentage_deal')
    })
    it('Change to Default', ()=>{
        cy.contains('Change to Default').should('have.attr', 'href', 'https://investorportal.test/admin/change_merchant_status').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_merchant_status')
        cy.get('#change_merchant_status').contains('Change Merchant Status').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_merchant_status#')
        cy.on('window:confirm', () => true)
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_merchant_status#')   
    })
    it('Change to Advanced status', ()=>{
        cy.contains('Change to Advanced Status').should('have.attr', 'href', 'https://investorportal.test/admin/change_advanced_status').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_advanced_status')
        cy.get('#change_advanced_status').contains('Change to Advance Completed Status').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_advanced_status#')
        cy.on('window:confirm', () => true)
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/change_advanced_status#')   
    })
    it('Generate Statement', ()=>{
        cy.get('[data-cy="cy_generate_statement"]').contains('Generate Statement').should('have.attr', 'href', 'https://investorportal.test/admin/pdf_for_merchants')
            .should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/pdf_for_merchants')
        cy.get('#date_end1').should('be.visible')
        cy.get('#merchants').should('be.visible')
        cy.get('#data_filter').get('[value="Generate PDF"]').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/pdf_for_merchants')
    })
    it('Generated Statement', ()=>{
        cy.contains('Generated Statement').should('have.attr', 'href', 'https://investorportal.test/admin/generated_pdf_merchants').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/generated_pdf_merchants')
        cy.get('#date_start1').should('be.visible')
        cy.get('#date_end1').should('be.visible')
        cy.get('[placeholder="Select Merchant(s)"]').should('be.visible')
        cy.get('#date_filter').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/generated_pdf_merchants')
        cy.get('#delete_multi_statment').should('be.visible').click({force:true})
        cy.on('window:confirm', () => true)
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/generated_pdf_merchants#')

    })
    it('FAQ', ()=>{
        cy.get('body > div.wrapper.demo > aside > section > ul > li.treeview.menu-open > ul > li:nth-child(8) > a')
        .contains('FAQ').should('have.attr', 'href', 'https://investorportal.test/admin/merchants/faq').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/faq')
        cy.contains('Create New').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/faq/create')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/faq/create')
        cy.contains('Cancel').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/faq')
    })
})