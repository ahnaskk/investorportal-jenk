
describe('TC-030', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors/portfolio/6')
    })

    it('Verify Investor Profit is showing correctly', function() {
         //investor profit checking

         cy.get(':nth-child(15) > .info-box').should('contain', 'Profit', '$390.00')


        
        
      


    })

})




