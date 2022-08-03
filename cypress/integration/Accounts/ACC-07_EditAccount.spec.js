describe('Edit Account', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Test if an account editable', () => {
        cy.get('#investor').contains('td', 'Syndicate4').siblings()
            .contains('a', 'Edit')
            .click()
        cy.get('#investorContactPerson').clear().type('edited contact')
        cy.get('#edit_investor_form').submit();
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box-body.alert-box-body > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Test `Add Bank Account` from account edit', () => {
        cy.get('#investor').contains('td', 'Syndicate3').siblings()
            .contains('a', 'Edit')
            .click()
        cy.get('a.btn-danger').invoke('removeAttr', 'target')
        cy.get('.btn-danger').click()
        cy.get('.admin-btn').click()
        cy.get('#accountHolderName').type('Investor')
        cy.get('#accountNumber').type('00012345678')
        cy.get('#routingNumber').type('121122676')
        cy.get('[name="bank_address"]').type('Address1, Address2')
        cy.get('#debit').check()
        cy.get('#submitButton').click()
        cy.get('#bank_details_form > div > div.box-body.alert-box-body > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})