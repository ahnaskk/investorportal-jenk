describe('TC-012', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the amount shown under Investor tab in Merchant view page', function() {
        
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11) > a:nth-child(1)').contains('View').click()
        
        cy.get(':nth-child(1) > .nav-link') .click()
        cy.get('#investorTable').should('have.length', 1).and('contain', 'investor1')
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(3)').should('contain', '$1,000.00')//Amount
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(4)').should('contain', '$1,500.00')//RTR
        cy.get('#investorTable').find('tbody > tr:nth-child(1)').find('td:nth-child(5)').should('contain', '$1,110.00')//Total invested
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(6)').should('contain', '$0.00 (0%)')//recieved amount
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(7)').should('contain', '$1,500.00')//balance(same as rtr)
        cy.get('[for="Share"]').should('contain', '10%')//share
        cy.get('#investorTable > tbody > tr:nth-child(1) > td:nth-child(9)').should('contain', '$0.00', ' ( 0%)')//Paid mgt fee
        cy.get('tbody > :nth-child(1) > :nth-child(12)').should('contain', '$0.00', ' ( 0%)')//syndication fee
        cy.get('tbody > :nth-child(1) > :nth-child(13)').should('contain', '$10.00', ' (1%)')//Underwriting fee
        cy.get('.tdActnPending > .select2 > .selection > .select2-selection').should('contain', 'Approved')//Status checking



    })

})


