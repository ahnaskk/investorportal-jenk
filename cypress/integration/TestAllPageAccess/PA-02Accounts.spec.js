describe('Accounts Test', () => {
    beforeEach(() => {
        cy.log_in()
        cy.visit('/admin/investors')

    })
    it('Checking Accounts page is visible', () => {
        cy.get('#cy_accounts').contains('Accounts').should('be.visible')
        cy.get('#cy_all_accounts').contains('All Accounts').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 
    })

    it('Add filtering and checking the page URL', () => {
    //DropDown's
        cy.get('#investor_type').should('be.visible').select('1',{force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#velocity').should('be.visible').select(1,{force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#role_id').should('be.visible').select('Over Payment',{force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#auto_invest_label').should('be.visible').select('1',{force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#notification_recurence').should('be.visible').select('1',{force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 
    //RadioButtons/Checkbox
        cy.get('#active_status_all').should('be.visible').check({force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#label_enable').should('be.visible').check({force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 

        cy.get('#active_status_companies_disable').should('be.visible').check({force:true})
        cy.get('#date_filter').click({force:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', 'https://investorportal.test/admin/investors')
    })

    // it('clicking `Download` button', () => {
    //     cy.visit('/admin/investors')
    //     cy.get('#form_filter').should('be.visible').click()
    //     cy.verifyDownload('Account List 04-04-2022 03_18_19.csv')
    //     cy.url().should('include', '/admin/investors') 
    //     cy.url().should('eq', 'https://investorportal.test/admin/investors') 
    // })
    it('Test`Download` button', () => {
        cy.get('#form_filter').should('be.visible')
        cy.url().should('eq', 'https://investorportal.test/admin/investors') 
    })
    it('clicking `Create` button', () => {
        cy.contains('Create Account').should('be.visible').click()
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/investors/create') 
        cy.get('[value="Create"]').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/investors/create') 
        cy.contains('View Accounts').should('be.visible').click({multiple:true})
        cy.url().should('include', '/admin/investors') 
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/investors') 
    })

    it('`T` Button testing in Actions column', () => {
        cy.get('#investor').get('[title="Transaction"]').contains('T').should('be.visible').first().click( { force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/transactions/') 
    })
    it('`D` Button testing in Actions column', () => {
        cy.get('#investor').get('[title="Document"]').contains('a', 'D').first().click( { force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/merchant_investor/documents_upload/') 
    })
    it('`APIR` Button testing in Actions column', () => {
        cy.get('#investor').contains('a', 'APIR').should('be.visible').first().click( { force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/reports/AdvancePlusInvestments/') 
    })
    it('`B` Button testing in Actions column', () => {
        cy.get('#investor').contains('a', 'B').first().click( { force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/bank_details/') 
    })
    it('` Generate Statement` Button testing in Actions column', () => {
        cy.get('#investor').contains('a', ' Generate Statement').should('be.visible').first().click( { force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/pdf_for_investors?id=') 
    })
    it('`EDIT` Button testing in Actions column', () => {
        cy.get('#investor').get('[title="Edit"]').should('be.visible').first().click( {force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/edit/') 
    })
    it('`DELETE` Button testing in Actions column', () => {
        cy.get('#investor').get('[title="Delete"]').should('be.visible').first().click( {force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors') 
    })
    
    it('`DELETE` Button testing in Actions column', () => {
        cy.get('#investor').get('[title="Delete"]').should('be.visible').first().click( {force: true } )
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors') 
    })
    it('Testing `Create Account` page is visible', () => {
        cy.get('#cy_create_account').contains('Create Account').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/investors/create') 
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/create')
        cy.get('[value="Create"]').should('be.visible').click({force:true})
            cy.url().should('eq', Cypress.config().baseUrl + '/admin/investors/create') 
            cy.contains('View Accounts').should('be.visible').click({multiple:true})
            cy.url().should('include', '/admin/investors') 
            cy.url().should('eq', Cypress.config().baseUrl + '/admin/investors') 
    })
    it('Testing `Generate Statement` page is visible', () => {
        cy.contains('Generate Statement').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/pdf_for_investors') 
        cy.url().should('include', Cypress.config().baseUrl + '/admin/pdf_for_investors')
            cy.contains('Generate Statement').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/pdf_for_investors')
    })
    it('Testing `Generate PDF/CSV` page is visible', () => {
        cy.contains('Generated PDF/CSV').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/generatedPdfCsv') 
        cy.url().should('include', Cypress.config().baseUrl + '/admin/generatedPdfCsv')
            cy.get('#delete_multi_statment').contains(' Delete Selected').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/generatedPdfCsv')
            cy.get('#multi_mail_send').contains(' Send Mail ').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/generatedPdfCsv')
            cy.get('#date_filter').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/generatedPdfCsv')
    })
    it('Testing `FAQ` page is visible', () => {
        cy.contains('FAQ').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/investors/faq') 
        cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/faq')
            cy.contains('Create New').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/faq/create')
            cy.get('#faqForm > div > div.btn-wrap.btn-right > div > input').contains('Create')
            cy.get('[value="Create"]').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/faq/create')
            cy.contains('Cancel').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/investors/faq')       
    })
})
