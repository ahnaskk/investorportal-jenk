
describe('TC-026', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check new investment is updated in Merchant Liquidity log', function() {
        
        cy.get('#cy_logs').click()
        cy.get('#cy_liquity_log').click()
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#date_filter').click()
        cy.get('.grid').should('have.length', 1).and('contain', 'Payment')
        cy.get('tbody > :nth-child(1) > :nth-child(4)').should('contain', 'Payment')
        cy.get('tbody > :nth-child(1) > :nth-child(6)').should('contain', '$150.00')
        cy.get('tbody > :nth-child(1) > :nth-child(7)').should('contain', '$49,040.00')
        

    })

})





