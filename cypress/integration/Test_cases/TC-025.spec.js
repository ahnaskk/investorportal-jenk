
describe('TC-025, Run php artisan q:w', function() {
    
        it('Verify the newly added payment reflects in Dashboard', function() {
           
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
        cy.get('.row > :nth-child(1) > .small-box').should('contain','$1,500.00', 'Total RTR')
        cy.get(':nth-child(2) > .small-box').should('contain', '$1,350.00', 'Expected RTR')
        cy.get(':nth-child(3) > .small-box > .inner').should('contain', 'Investors', '1')
        cy.get(':nth-child(4) > .small-box > .inner').should('contain', 'Merchants', '1')
        cy.get(':nth-child(5) > .small-box').should('contain', '$49,040.00', 'Liquidity')
        cy.get(':nth-child(7) > .small-box > .inner').should('contain', '$1,110.00', 'Total Amount Invested')
        cy.get(':nth-child(8) > .small-box > .inner').should('contain', '$999.00', 'Current Invested')
        cy.get(':nth-child(9) > .small-box > .inner').should('contain', '895.95%', 'Blended Rate')
        cy.get(':nth-child(10) > .small-box > .inner').should('contain', '$150.00', 'CTD')



        

    })

})





