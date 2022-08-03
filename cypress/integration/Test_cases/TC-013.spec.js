describe('TC-013', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify newly added investment is listed in Investment report', function() {
        
        cy.get('#cy_reports').click()
        cy.get('#cy_investmnt').click()
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#investors').select('investor1', {force: true})
        cy.get('#apply').click()
        
    })

})


