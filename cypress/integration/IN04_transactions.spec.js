describe('Adds transactions for accounts', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
        cy.get('[name="investor_length"]').select('100')
    })

    it('Add a transaction for `INVESTORVP-1` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVP-1').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('100000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVP-2` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVP-2').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('100000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVEL-1` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVEL-1').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('100000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVEL-2` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVEL-2').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('100000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVEL-3` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVEL-3').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('50000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVEL-4` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVEL-4').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('25698')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVP-3` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVP-3').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('75000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `INVESTORVP-4` account', () => {
        cy.get('#investor').contains('td', 'INVESTORVP-4').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('5000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `SYNDICATE1` account', () => {
        cy.get('#investor').contains('td', 'SYNDICATE1').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('40000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `SYNDICATE2` account', () => {
        cy.get('#investor').contains('td', 'SYNDICATE2').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('40000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `SYNDICATE3` account', () => {
        cy.get('#investor').contains('td', 'SYNDICATE3').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('100000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

    it('Add a transaction for `SYNDICATE4` account', () => {
        cy.get('#investor').contains('td', 'SYNDICATE4').siblings()
            .contains('a', 'T')
            .click( { force: true } )
        cy.contains('a', 'Create Transactions').click()
        cy.get('#inputAmount').type('40000')
        cy.get('#transaction_category').select('1', { force: true })
        // cy.get('#transaction_method').select('1', { force: true })
        cy.get('#tran_create').click()
    })

})

describe('Assert values in dashboard after transactions', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Investors` value in dashboard', () => {
        cy.get('.total_investors-widget h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })

    it('Assert for `Merchants` value in dashboard', () => {
        cy.get('.total_merchants-widget h3').should((elem) => {
            expect(elem.text()).to.equal('0')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$775,698.00')
        })
    })

    it('Assert for `SYNDICATE` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$220,000.00')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$280,000.00')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$275,698.00')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('0%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Velocity Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(11) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Investor Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(12) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Pactolus Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(13) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Average Daily Balance` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(14) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Investor Portfolio` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(15) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$775,698.00')
        })
    })

    it('Assert for `Default Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(16) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('0%')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$775,698.00')
        })
    })

    it('Assert for `Pending To Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(19) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Pending To User Bank` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(20) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

})