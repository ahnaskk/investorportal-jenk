describe('TC-015', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check new investment is listed in Investor Assignment report', function() {
        
        cy.get('#cy_reports').click()
        cy.get('#cy_inv_assignment').click()
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#investors').select('6', {force: true})
       // cy.get('#merchants').select('1', {force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div > div > form > div:nth-child(2) > div:nth-child(3) > div > span > span.selection > span > span > textarea').click()
        cy.wait(5000)
        cy.get('.select2-results__option').click() 
        cy.get('#apply').click()
        cy.get('#dataTableBuilder_wrapper > div:nth-child(2) > div').should('have.length', 1).and('contain', 'investor1', 'merchant1')
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(4)').should('contain', '$1,110.00')//Participant Amount
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(5)').should('contain', '$48,890.00')//Liquidity
   
    })

})


