describe('TC-018', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check new investment is updated in Merchant Liquidity log', function() {
        
        cy.get('#cy_logs').click()
        cy.get('#cy_mer_liq_log').click()
        cy.get('.grid').should('have.length', 1).and('contain', 'based_on_liquidity')
        cy.get('.odd > :nth-child(5)').should('contain', '$-1,110.00')//Amount
        
    })

})

