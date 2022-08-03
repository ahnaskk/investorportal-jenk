describe('TC-011', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the assigned investor is listed in Investor tab', function() {
        
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11) > a:nth-child(1)').contains('View').click()
        
        cy.get(':nth-child(1) > .nav-link') .click()
        cy.get('#investorTable').should('have.length', 1).and('contain', 'investor1')
        
      
    })

})


