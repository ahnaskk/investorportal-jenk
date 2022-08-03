import { text } from "@fortawesome/fontawesome-svg-core"

describe('TC-005', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Add Transaction', function() {
        cy.get('#cy_accounts').click()
        cy.get('#cy_all_accounts').click()
        cy.url().should('contains', '/admin/investors')
        cy.get('#investor > tbody > tr:nth-child(1) > td:nth-child(7) > a:nth-child(2)').first().click();
        cy.url().should('contains', '/admin/investors/transactions/')
        cy.get('#cy_create_transactions').click()
        cy.get('#inputAmount').type('50000');
        cy.get('#transaction_category').select('Transfer To Velocity', {force: true})
        cy.get('#transaction_form').submit();
        cy.get('.alert > h4')
        .should(($msg) => {
        expect($msg.text().trim()).equal('Success');
        });    
    })
})

