describe('Test for accounts faq', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
    })
    it('Creates `FAQ`', () => {
        cy.visit('/admin/investors/faq/create')
        cy.get('#title').type('test faq')
        cy.get('#description').type('test description')
        cy.get('.btn-primary').click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})