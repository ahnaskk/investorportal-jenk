describe('Reconciliation_Request', () => {    
    beforeEach(() => { 
    cy.viewport(2000, 1700)
    cy.login({ email:'1email.33433@iocod.com'})
    cy.visit('/admin/dashboard')
    })
    it('Reconciliation_Request', ()=>{
        cy.get('[data-cy="Reconciliation_Request"]').contains('Reconciliation Request').should('be.visible').click()
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/reconcilation-request')
        cy.get('#merchants').should('be.visible')
        cy.get('#reconciliation_status').should('be.visible')
        cy.get('[value="Apply Filter"]').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/admin/merchants/reconcilation-request')
        cy.get('[value="Download"]').should('be.visible')
    })
  
})