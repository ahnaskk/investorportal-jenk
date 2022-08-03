describe('Companies Test', () => {
    beforeEach(() => {
        cy.log_in()
        cy.visit('/admin/dashboard')
        cy.get('#cy_companies').contains('Companies').should('be.visible').click()


    })
    it('Checking All Companies page is visible', () => {
        cy.get('[data-cy="all_companies"]').contains('All Companies').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/sub_admins') 
        cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins')
            //create companies button
            cy.contains('Create Companies').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins/create')
            cy.get('[value="Create"]').contains('Create')
            cy.get('[value="Create"]').should('be.visible').click()
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins/create')
            cy.get('#create_subadmin > div > div.btn-wrap.btn-right > div > a').contains('View Compaines').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins')
            //edit button
            cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td:nth-child(8) > a').contains('Edit').should('be.visible').first().click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins/edit/')
            cy.get('[value="Update"]').contains('Update')
            cy.get('[value="Update"]').should('be.visible')
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins/edit/')
            cy.get('#edit_subadmin > div > div.btn-wrap.btn-right > div > a').contains('View Compaines').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins')
            //delete button
            cy.get('[value="Delete"]').contains('Delete').should('be.visible').first().click({force:true})
            cy.on('window:confirm', () => false)
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins')
    })
    it('Checking Create Companies page is visible', () => {
        cy.get('#cy_create_companies').contains('Create Companies').should('be.visible').click({force:true})
        cy.url().should('include', '/admin/sub_admins/create') 
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/sub_admins/create')
            //create button
            cy.get('[value="Create"]').contains('Create')
            cy.get('[value="Create"]').should('be.visible').click()
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins/create')
            //View company button
            cy.get('#create_subadmin > div > div.btn-wrap.btn-right > div > a').contains('View Compaines').should('be.visible').click({force:true})
            cy.url().should('include', Cypress.config().baseUrl + '/admin/sub_admins')        
    })
})