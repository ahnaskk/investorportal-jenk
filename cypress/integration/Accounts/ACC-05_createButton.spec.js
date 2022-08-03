describe('Verify `Create Account` button works', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Test if `Create Account` button works', () => {
        cy.get('.create-btn').click();
        cy.url().should('contains', 'http://investorportal.test/admin/investors/create');
    })
})