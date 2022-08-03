

describe('Verify if download button works in accounts page', () => {
    // const downloadsFolder = Cypress.config('downloadsFolder')
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Test if download works', () => {
        cy.get('#form_filter').click();
    })
})