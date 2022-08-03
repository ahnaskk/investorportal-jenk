
describe('TC-027', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment updated in Merchant Liquidity log', function() {
        
        cy.get('#cy_logs').click()
        cy.get('#cy_mer_liq_log').click()
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#date_filter').click()
        cy.get('.grid').should('have.length', 1).and('contain', 'Payment', 'based_on_liquidity')
        cy.get('tbody > :nth-child(1) > :nth-child(4)').should('contain', 'Payment')
        cy.get('.odd > :nth-child(5)').should('contain', '$150.00')
        

    })

})





