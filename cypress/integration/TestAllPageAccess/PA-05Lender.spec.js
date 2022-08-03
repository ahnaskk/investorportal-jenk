describe('Lender Test', () => {
    beforeEach(() => {
        cy.log_in()
        cy.visit('/admin/dashboard')
        cy.get('#cy_lender').contains('Lender').should('be.visible').click()

    })


    it('Checking All Lenders page is visible', () => {
        cy.get('#cy_all_lenders').contains('All Lenders').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/lender') 
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/lender')
            //lender create button
            cy.contains('Create Lender').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender/create')
            cy.get('[value="Create"]').contains('Create')
            cy.get('[value="Create"]').should('be.visible').click()
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender/create')
            //View Lenders button in create page
            cy.get('#crete_admin_form > div > div.btn-wrap.btn-right > div > a').contains('View Lenders').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender') 
            //View button
            cy.get('#lender > tbody > tr:nth-child(1) > td:nth-child(9) > a.btn.btn-xs.btn-success').contains('View').first().click({force:true})       
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender/view/') 
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div > div.btn-wrap.btn-right > div > a')
                .contains('View Lenders').should('be.visible').click()
                cy.url().should('include', Cypress.config().baseUrl + '/admin/lender') 
            //edit button
            cy.get('#lender > tbody > tr:nth-child(1) > td:nth-child(9) > a.btn.btn-xs.btn-primary').contains('Edit').should('be.visible').first().click({force:true})       
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender/edit/') 
            cy.get('#edit_admin_form > div > div.btn-wrap.btn-right > div > a').contains('View Lenders').should('be.visible')
            cy.get('[value="Update"]').contains('Update').should('be.visible').click()
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender') 
            cy.contains('View Lenders').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender') 
            //delete button
            cy.get('[value="Delete"]').contains('Delete').should('be.visible').first().click({force:true})
            cy.on('window:confirm', () => false)
            cy.url().should('eq', Cypress.config().baseUrl + '/admin/lender') 
    })
    it('Checking Crete Lenders page is visible', () => {
        cy.get('#cy_create_lenders').contains('Create Lender').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/lender/create') 
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/lender/create')
            //create button
            cy.get('[value="Create"]').contains('Create')
            cy.get('[value="Create"]').should('be.visible').click()
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender/create')
            //View Lenders button in create page
            cy.get('#crete_admin_form > div > div.btn-wrap.btn-right > div > a').contains('View Lenders').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/lender') 
           
    })
    it('Checking Lender Settings page is visible', () => {
        cy.contains('Lender Settings').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/lenderActivation') 
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/lenderActivation')
        cy.get('.toggle-group').should('be.visible').first().click()
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/lenderActivation')
        cy.get('.toggle-group').should('be.visible').first().click()
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/lenderActivation')           
    })
})
