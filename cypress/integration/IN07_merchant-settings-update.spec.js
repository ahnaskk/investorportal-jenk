describe('Test for to set merchant mnm and mxm investment value and percentage', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/settings')
    })
    it('Set value in `Minimum Investment Value` and `Maximum Investment Percentage`', () => {
        cy.get('[name="minimum_investment_value"]').clear().type('100')
        cy.get('[name="max_investment_per"]').clear().type('50')
        cy.get('#minimum_investment_value_form').submit()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-body.alert-box-body > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})