describe('Template Management', () => {    
    beforeEach(() => { 
        cy.login({ email:'1email.33433@iocod.com'})
        cy.visit('/admin/dashboard')
        cy.get('[data-cy="Template_Management"]').contains('Template Management').should('be.visible').click({force:true})
    })
    it('View Template', ()=>{
        cy.get('[data-cy="View_Template"]').contains('View Template').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/template')
        cy.get('#template_type').should('be.visible',{force:true})
        cy.get('[value="Apply Filter"]').should('be.visible', {force:true}).click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/template')
    })
    it('Create Template in View Template', ()=>{
        cy.contains('Create Template').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/template/create')
        cy.get('[value="Create"]').should('be.visible',{force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/template/create')
        cy.contains('View Templates').should('be.visible', {force:true}).click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/template')
    })   
})
