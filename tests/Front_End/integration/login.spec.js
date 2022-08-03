beforeEach(()=>{
    cy.visit('/')
})

it('Does not work with wrong creadentials', ()=>{
    cy.visit('/login')

    cy.get('input.email').type('test@test.com')
    cy.get('input.password').type('test@test.com')
    cy.get('button.bt_login').click()

    cy.contains('p.err_msg','These credentials do not match our records.')
    cy.url().should('contain','/login')
})



it('Logs in', ()=>{
    cy.visit('/login')

    cy.get('input.email').type('92email@iocod.com')
    cy.get('input.password').type('omassery')
    cy.get('button.bt_login').click()

    cy.contains('button.sign-out','Sign Out')
    cy.url().should('not.contain','/login')
})