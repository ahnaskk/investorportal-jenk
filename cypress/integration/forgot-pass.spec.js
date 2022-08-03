describe('Forgot password', () => {
    beforeEach(() => {
        cy.visit('/password/reset')
    })
    it('Test if `required` validation works', () => {
        cy.get('[data-cy="submit"]').click();
        cy.get('#app-layout > div.container > div > div > div > div.panel-body > form > div.form-group.has-error > div > span > strong').should('have.text', 'The email field is required.');
    })
    it('Test if back button works', () => {
        cy.get('[data-cy="back"]').click()
        cy.url().should('contain', '/login')
    })
    it('Shows success notification on submitting valid email', () => {
        cy.get('[data-cy="email"]').type('admin@investor.portal');
        cy.get('[data-cy="submit"]').click();
        cy.get('[data-cy="success"]').should('have.text', 'We have e - mailed your password reset link!')
    })
})