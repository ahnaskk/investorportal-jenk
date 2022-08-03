describe('Bank Account tests', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Verifies bank account added displayed in bank account details page', () => {
        cy.get('#investor').contains('td', 'Syndicate3').siblings()
            .contains('a', 'Bank')
            .click()
        cy.get('#dataTableBuilder').contains('td', 'Syndicate3').should('be.visible');
    })
    it('Tests if bank account details can edit', () => {
        cy.get('#investor').contains('td', 'Syndicate3').siblings()
            .contains('a', 'Bank')
            .click()
        cy.get('#dataTableBuilder').contains('td', 'Syndicate3').siblings()
            .contains('a', 'Edit')
            .click()
        cy.get('#accountHolderName').clear().type('investor edited')
        cy.get('#submitButton').click()
        cy.get('#bank_details_form > div > div.box-body.alert-box-body > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Tests delete bank account', () => {
        cy.get('#investor').contains('td', 'Syndicate3').siblings()
            .contains('a', 'Bank')
            .click()
        cy.get('#dataTableBuilder').contains('td', 'Syndicate3').siblings()
            .contains('input', 'Delete')
            .click()
        // cy.get('td > form').submit();

        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

    })
})