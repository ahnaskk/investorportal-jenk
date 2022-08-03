describe('TC-008', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check the newly added Transaction is updated in Liquidity Log', function() {
        cy.viewport(1440, 789)
        cy.get('#cy_logs').click()
        cy.get('#cy_liquity_log').click()
        cy.url().should('contains', '/admin/reports/liquidityLog')
        cy.get('#dataTableBuilder').should('have.length', 1).and('contain', 'Transfer To Velocity')//Description
        cy.get('td').eq(5).should('contain', '$50,000.00')//Liquidity change
        cy.get('td').eq(6).should('contain', '$50,000.00')//Investor liquidity
    
    })
})
