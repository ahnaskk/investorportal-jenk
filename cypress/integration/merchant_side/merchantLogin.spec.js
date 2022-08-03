describe('Merchant login page test', () => {
    beforeEach(() => {

        cy.visit('/merchants')
        
    })
    it('Verify if it is login page', () => {
        cy.contains('Login to your account');
    })
    it('Test if `required` validation works', () => {
        cy.get('#login-page > div.form_sec.pr > div.form_container > button').click();
        cy.get('div > #login-page > .form_sec > .form_container > .err_msg').contains( 'Please fill all the fields in the correct format');

    })
    it('Test if wrong credentials shows appropriate message', () => {
        cy.get('div > #login-page > .form_sec > .form_container > .email').type('wrong@email.com')
        cy.get('div > #login-page > .form_sec > .form_container > .password').type('lkjlkjlkjlkjlkj')
        cy.wait(5000)
        cy.get('#login-page > div.form_sec.pr > div.form_container > button').click();
        cy.get('#login-page > div.form_sec.pr > div.form_container > p').contains('Username or Password is incorrect !');
    })

    it('Login with right credentials', () => {
        cy.get('div > #login-page > .form_sec > .form_container > .email').type('501email@iocod.com')
        cy.get('div > #login-page > .form_sec > .form_container > .password').type('lkjlkj')
        cy.get('#login-page > div.form_sec.pr > div.form_container > button').click();
        cy.url().should('include', '/merchants/dashboard')  
    })
    

});




