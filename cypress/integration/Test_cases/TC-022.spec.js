describe('TC-022', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment displayed under Payment tab of Merchant view page', function() {
           
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11) > a:nth-child(1)').contains('View').click()
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
        cy.get('.sorting_1 > span').should('contain', '$1,500.00')//payment
        cy.get('.odd > :nth-child(5) > span').should('contain', '$150.00')//to participant
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(7)').should('contain', '$39.00')//profit
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(6)').should('contain', '$111.00')//principal
        cy.get('.odd > :nth-child(9)').should('contain', 'Manual')//payment method
        cy.get('.odd > .details-control').click()//expand + button
        cy.get('[title="$150.0000"]').should('contain', '$150.00')//participant share
        cy.get('tbody > :nth-child(3) > :nth-child(4)').should('contain', '$0.00')//mgt fee
        cy.get('tbody > :nth-child(3) > :nth-child(8)').should('contain', '$0.00')//over payment


    })

})

