
describe('TC-028', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify the newly added payment updated in Dashboard', function() {
        
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(1) > div > div.inner').contains('Total RTR')
        cy.get('.row > :nth-child(1) > .small-box > .inner').should('contain', '$1,500.00', 'Total RTR')
        cy.get(':nth-child(2) > .small-box > .inner').should('contain', '$1,350.00', 'Expected RTR')
        cy.get(':nth-child(3) > .small-box > .inner').should('contain', '1', 'Investors')
        cy.get(':nth-child(4) > .small-box > .inner').should('contain', '1', 'Merchants')
        cy.get(':nth-child(5) > .small-box > .inner').should('contain', '$49,040.00', 'Liquidity')
        cy.get(':nth-child(7) > .small-box > .inner').should('contain', '$1,110.00', 'Total Amount Invested')
        cy.get(':nth-child(8) > .small-box > .inner').should('contain', '$999.00', 'Current Invested')

    })

})





