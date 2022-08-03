describe('TC-020', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment reflects in Merchant view page', function() {
        
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.url().should('contains', '/admin/merchants')
        cy.get(':nth-child(11) > [href="http://investorportal.test/admin/merchants/view/1"]').click()
        cy.get(':nth-child(2) > .merchant-details > :nth-child(4)').should('contain', 'Payments Left', '9')//payment left
        cy.get(':nth-child(2) > .merchant-details > :nth-child(5)').should('contain', 'Actual Payments Left', '9')//actual payments left
        cy.get(':nth-child(2) > .merchant-details > :nth-child(6)').should('contain', 'First payment date', '08-01-2021')//first payment date
        cy.get(':nth-child(2) > .merchant-details > :nth-child(7)').should('contain', 'Last payment date','08-01-2021')//last payment date
        cy.get(':nth-child(3) > .merchant-details > :nth-child(7)').should('contain', 'Complete %', '10%')//complete%
        cy.get('.merchant-details > :nth-child(10)').should('contain', 'Merchant Balance', ' $13,500.00')//merchant balance
        cy.get(':nth-child(4) > .merchant-details > :nth-child(4)').should('contain', 'Net Zero Balance', '$960.00')//net zero balance
        cy.get(':nth-child(4) > .merchant-details > :nth-child(3)').should('contain', 'CTD (Our Portion)', '$150.00')//ctd



    })

})

