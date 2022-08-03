
describe('Logs Report Assertion', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    it('Creating Pref Return', () => {
        cy.visit('/admin/investors')
        cy.get('#investor_type').select('Debt(65/20/15)',{force:true})
        cy.get('#date_filter').click({force:true})
        cy.get('#investor').contains('a', ' INVESTORVEL-3 ').should('have.attr', 'target', '_blank').invoke('removeAttr', 'target') .click({ force:true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.inner.admin-dsh.header-tp > h3').should((name) => {
            expect(name.text()).to.equal(' INVESTORVEL-3  ')
        })
        cy.contains(' Pref Return').click({force: true})
        cy.get('#cy_create_transactions').click({force:true})
        cy.get('#date_start1').type('11-01-2020', {force:true})
        cy.get('#date_end1').type('04-01-2022', {force:true})
        cy.get('#roi_rate').select('10.00', {force:true})
        cy.get('[value="Create"]').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box > div > div.box-body.alert-box-body > div').should((name) => {
            expect(name.text().split('×').pop().trim()).to.equal('Successfully Created')
        })
    })
    it('Assert Pref Return in Profitability Report', () => {
        cy.visit('/admin/reports/profitability2')
        cy.get('#from_date1').clear( {force:true})
        cy.get('#to_date1').clear( {force:true})
        cy.get('#apply').click({force:true})
        cy.get('#dataTableBuilder > tbody > tr.even > td:nth-child(6)').should((pref_ret) =>{
            expect(pref_ret.text()).to.equal('$260.27')
        })
    })
})

