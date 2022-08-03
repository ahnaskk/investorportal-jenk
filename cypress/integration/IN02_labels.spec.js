describe('Labels test', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/label/create')
    })
    it('Verify if `create label` page accessable', () => {
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.inner.admin-dsh.header-tp > h3').contains('Add Label ')
    })
    it('Create label `MCA (Default)`', () => {
        cy.get('#inputName').type('MCA (Default)');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create label `Luthersales`', () => {
        cy.get('#inputName').type('Luthersales');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create label `Insurance`', () => {
        cy.get('#inputName').type('Insurance');
        cy.get('#flag').click();
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create label `Insurance 1`', () => {
        cy.get('#inputName').type('Insurance 1');
        cy.get('#flag').click();
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create label `Insurance 2`', () => {
        cy.get('#inputName').type('Insurance 2');
        cy.get('#flag').click();
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
})
describe('Tests for `Label` views', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/label')
    })
    it('Verifies table has 5 labels', () => {
        cy.get('#status > tbody').find('tr')
            .then((row) => {
                expect(row.length).to.eq(5);
            })
    })
})