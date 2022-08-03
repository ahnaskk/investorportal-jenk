describe('TC-007', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Transaction is listed in Transaction report or not', function() {
        cy.viewport(1440, 789)
        cy.get('#cy_reports').click()
        cy.get('#cy_transactions').click()
        cy.url().should('contains', '/admin/investors/transaction-report')
        cy.get('#investors').select('investor1', {force: true})
        cy.get('#apply').click()
        cy.get('#dataTableBuilder').should('have.length', 1)
        cy.screenshot()
        
    })
})
