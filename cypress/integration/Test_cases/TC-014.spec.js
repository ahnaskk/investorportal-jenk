describe('TC-014', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check Investment report values are displaying correctly', function() {
        
        cy.get('#cy_reports').click()
        cy.get('#cy_investmnt').click()
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#investors').select('investor1', {force: true})
        cy.get('#apply').click()

        cy.get('#dataTableBuilder').should('have.length', 1).and('contain', 'merchant1')
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(6)').should('contain', '$1,000.00')//Amount
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(7)').should('contain', '$1,500.00')//RTR
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(8)').should('contain', '$100.00')//Commision
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(9)').should('contain', '10%')//share
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(10)').should('contain', '$0.00')//syndication fee
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(12)').should('contain', '$10.00')//Underwriting fee
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(13)').should('contain', '$0.00')//Anticipated mgt fee
        // Checking total invested amount.
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11)').should('contain', '$1,110.00').then( ($total_invested_amnt) => {
            cy.log($total_invested_amnt.text())
           
        })      
    })

})


