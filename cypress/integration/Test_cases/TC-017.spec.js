describe('TC-017', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check new investment is updated in Liquidity log', function() {
        
        cy.get('#cy_logs').click()
        cy.get('#cy_liquity_log').click()
        cy.get('#dataTableBuilder_wrapper').should('have.length', 1).and('contain', 'based_on_liquidity')
        cy.get('.odd > :nth-child(6)').should('contain', '$-1,110.00')//Liquidity change amount
        cy.get('.odd > :nth-child(7)').should('contain', '$48,890.00')//investor liquidity
        
    })

})


