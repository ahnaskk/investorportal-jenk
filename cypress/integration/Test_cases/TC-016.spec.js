describe('TC-016', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the new investment is reflected in Investor Portfolio', function() {
        
        cy.get('#cy_accounts').click()
        cy.get('#cy_all_accounts').click()
        cy.get('[href="http://investorportal.test/admin/investors/portfolio/6"]').click()
        cy.get(':nth-child(1) > .info-box').should('contain', '$48,890.00')//liquidity
        cy.get(':nth-child(3) > .info-box').should('contain', '$1,110.00')//total invested amount
        cy.get(':nth-child(5) > .info-box').should('contain', '1')//merchants
        cy.get(':nth-child(6) > .info-box').should('contain', '895.95%')//belende roi
        cy.get(':nth-child(10) > .info-box').should('contain', '$1,500.00')//rtr
        cy.get(':nth-child(11) > .info-box').should('contain', '$50,390.00')//projected portifolio
        cy.get(':nth-child(13) > .info-box').should('contain', '$1,110.00')//current invested
        cy.get(':nth-child(17) > .info-box').should('contain', '$1,500.00')//anticipated rtr
        cy.get(':nth-child(15) > .info-box').should('contain', '$0.00')//profit
        cy.get(':nth-child(7) > .info-box').should('contain', '0%')//ROI



    })

})


