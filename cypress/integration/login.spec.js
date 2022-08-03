describe('Login page test', () => {
    beforeEach(() => {
        cy.visit('/login')
    })
    it('Verify if it is login page', () => {
        cy.contains('Login to your account');
    })
    it('Test if `required` validation works', () => {
        cy.get('[data-cy="submit"]').click();
        cy.get('.login-container > div.col-md-6.login-left > div.login-wrapper > div.panel-body > form > div:nth-child(2) > span > strong').should('have.text', 'The email field is required.');
        cy.get('.login-container > div.col-md-6.login-left > div.login-wrapper > div.panel-body > form > div:nth-child(3) > span > strong').should('have.text', 'The password field is required.');
    })
    it('Test if wrong credentials shows appropriate message', () => {
        cy.get('[data-cy="email"]').type('wrong@mail.com');
        cy.get('[data-cy="password"]').type('wrongpassword');
        cy.get('[data-cy="submit"]').click();
        cy.get('#app-layout > div.login-container > div.col-md-6.login-left > div.login-wrapper > div.panel-body > form > div.form-group.has-error > span > strong').should('have.text', 'These credentials do not match our records.');
    })
    it('Test if `forgot pass` link works', () => {
        cy.get('[data-cy="link-forgot-pass"]').click();
        cy.contains('Reset Password');
    })

    it('Logs in with right credentials', () => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard').contains('Dashboard');
    })

});