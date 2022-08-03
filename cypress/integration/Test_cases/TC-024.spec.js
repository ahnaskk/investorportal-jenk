describe('TC-024', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment reflects in Liquidity report', function() {
           
        cy.get('#cy_reports').click()
        cy.get('#cy_liquidity_report').click()
        cy.get('#dataTableBuilder_filter > label > .form-control').type('investor1')
        cy.get('.odd > :nth-child(3)').should('contain', '$1,350.00')//rtr balance
        cy.get('.odd > :nth-child(4)').should('contain', '$150.00')//ctd
        cy.get('.sorting_1').should('contain', '$50,000.00')//credits=principal investment
        cy.get('.odd > :nth-child(6)').should('contain', '$100.00')//commission
        cy.get('.odd > :nth-child(7)').should('contain', '$1,000.00')//funded amount
        cy.get('.odd > :nth-child(9)').should('contain', '$49,040.00')//liquidity
        cy.get('.odd > :nth-child(10)').should('contain', '$10.00')//underwriting fee
    })

})





