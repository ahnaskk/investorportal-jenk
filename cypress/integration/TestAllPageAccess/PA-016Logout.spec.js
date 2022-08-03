describe('Logout', () => {    
    beforeEach(() => { 
        cy.log_in()
        cy.visit('/admin/dashboard')
    })
    it('Logout Testing', ()=>{
        cy.contains('Logout').should('be.visible').click({force:true})
        cy.url().should('eq', Cypress.config().baseUrl + '/login')
    })
})
