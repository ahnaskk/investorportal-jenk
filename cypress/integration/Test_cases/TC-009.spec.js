describe('TC-009', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check whether newly created Investor is able to Fund the newly created Merchant.', function() {
        //settings page
        cy.get('#cy_settings').click()
        cy.get('#cy_advanced_settings').click()
        cy.get('#minimum_investment_value_form > div > div:nth-child(2) > div.col-sm-4.form-group.text-capitalize > div > input').type('100')
        cy.get('#max_investment_per').type('10')
        cy.get('#minimum_investment_value_form > div > div.btn-wrap.btn-right > div > input').click()
        //merchant page
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11) > a:nth-child(1)').contains('View').click()
        cy.get('#assign_button').click()
        cy.get('#company').select('3', {force: true})
        cy.get('input[type=submit]').click()
        cy.get('.alert > .btn-primary').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div').should('contain.text', ' Success')

    
    })
})


