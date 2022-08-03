describe('TC-021', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment reflects in Payment report', function() {
        
        cy.get('#cy_reports').click()
        cy.get('#cy_payments').click()
        cy.url().should('contains', '/admin/reports/payments')
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#investors').select('6', {force: true})
        cy.get('#date_filter').click()
        cy.get('.odd > :nth-child(6)').should('contain', '$1,500.00')//debited
        cy.get('.odd > :nth-child(7)').should('contain', '$150.00')//total payments
        cy.get('.odd > :nth-child(8)').should('contain', '$0.00')//mgt fee
        cy.get('.odd > :nth-child(9)').should('contain', '$150.00')//net amount
        cy.get('.odd > :nth-child(11)').should('contain', '$39.00')//profit
        cy.get('.odd > :nth-child(10)').should('contain', '$111.00')//principal
        cy.get('.odd > :nth-child(15)').should('contain', '$1,500.00')//participant rtr
        cy.get('.odd > :nth-child(16)').should('contain', '$960.00')//net zero balance
        cy.get('.odd > :nth-child(17)').should('contain', '$1,350.00')//participant rtr balance


    })

})

