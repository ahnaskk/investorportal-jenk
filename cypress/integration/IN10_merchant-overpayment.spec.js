describe('Tests for merchant over payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Make one  payment for `IOCOD10`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD10').click({ force:true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').click({ force: true });
            }
        })
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('07-25-2021', { force: true })
        cy.get('#paymentClick').click( { force: true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make over payment for `IOCOD5` merchant by paying in net', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').click({ force: true });
            }
        })
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('75670', {force: true})
        cy.get('[name="payment_date1"]').type('07-25-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Make over payment for `IOCOD5` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD5').siblings()
        //     .contains('a', 'View')
        //     .click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').click({ force: true });
            }
        })
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('1000', {force: true})
        cy.get('[name="payment_date1"]').type('07-26-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make an over payment for `IOCOD8` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD8').siblings()
        //     .contains('a', 'View')
        //     .click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').click({ force: true });
            }
        })
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create')
        cy.get('#select_all').click()
        cy.get('#payment').clear({force: true}).type('1000', {force: true})
        cy.get('[name="payment_date1"]').type('07-26-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Rcode payment for IOCOD8' , () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })

        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').click({ force: true });
            }
    })

    cy.url().should('contains', 'https://investorportal.test/admin/payment/create')
        cy.get('[name="payment_date1"]').type('01-01-2022', { force: true })
        cy.get('#rcode').select('3', {force: true})
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

})

    it('Make payment for `IOCOD9` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD9').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD9').siblings()
        //     .contains('a', 'View')
        //     .click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').then(($a) => {
            cy.log($a)
            if ($a.text().includes('More')) {
                cy.contains('More')
                    .click({ force: true })
                cy.contains('Add Payment').click({ force: true })
            } else {
                cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').click({ force: true });
            }
        })
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear( { force: true }).type('1000',  { force: true })
        cy.get('[name="payment_date1"]').type('07-26-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})

describe('Assert values in dashboard after merchant overpayments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$335,040.55')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$79,771.61')
        })
    })

    it('Assert for `Investors` value in dashboard', () => {
        cy.get('.total_investors-widget h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })

    it('Assert for `Merchants` value in dashboard', () => {
        cy.get('.total_merchants-widget h3').should((elem) => {
            expect(elem.text()).to.equal('10')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$817,050.26')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,931.03')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$305,709.42')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$289,409.81')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$213,916.68')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$53,063.64')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('446.76%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$255,268.94')
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
            expect(elem.text()).to.equal('$1,931.03')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$896,821.87')
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

describe('Assert values in `over payment` account', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
        cy.get('#investor_filter > label > .form-control').type('Overpayment')
        cy.contains('a', 'Overpayment').invoke('removeAttr', 'target').click()
    })

    it('Assert for `LIQUIDITY` value in over payment account', () => {
        cy.get(':nth-child(1) > .info-box > .info-box-content > .info-box-number').should((elem) => {
            expect(elem.text().trim()).to.equal('$1,931.03')
        })
    })
it('Assert values in AVAILABLE LIQUIIDTY value in overpayment account', () =>{
    cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(2) > div > div > span.info-box-number.g_value').should((elem) => {
        expect(elem.text().trim()).to.equal('$1,931.03')
    })
})

    it('Assert values in RESERVED LIQUIDITY value in overpayment account', () =>{
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(3) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text().trim()).to.equal('$0.00')
        })
} )
    it('Assert for `OVERPAYMENT` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(4) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('$1,931.03')
        })
    })
    
    it('Assert for `MERCHNATS` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(6) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('4')
        })
    })

    it('Assert for `CASH TO DATE(CTD)` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(7) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('$1,931.03')
        })
    })

    it('Assert for `TOTAL RTR` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(8) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('$1,931.03')
        })
    })

    it('Assert for `PROJECTED PORTFOLIO VALUE` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(9) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('$1,931.03')
        })
    })

    it('Assert for `PROFIT` value in over payment account', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(10) > div > div > span.info-box-number.g_value').should((elem) => {
            expect(elem.text()).to.equal('$1,931.03')
        })
    })

})

describe('Assert values in merchant payments tab after merchants over payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD5').siblings()
        // .contains('a', 'View')
        // .click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
        .contains('Payment').click()
        
    })

    it('Assert for IOCOD5 merchant overpayment //Date', () => {
        cy.get('tbody > :nth-child(1) > :nth-child(3) > a').should((elem) => {
            expect(elem.text()).to.equal('07-26-2021')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Payment', () => {
        cy.get(':nth-child(1) > .sorting_1 > span').should((elem) => {
            expect(elem.text()).to.equal('$1,000.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //ToParticipant', () => {
        cy.get('tbody > :nth-child(1) > :nth-child(5) > span').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //principal', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //profit', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })        
    })

    it('Assert for IOCOD5 merchant overpayment //Expand Test //investor name', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click()
        cy.get('[title="Overpayment"]').should((elem) => {
            expect(elem.text()).to.equal('OVERPAYMEN..')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test ///participant share', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test//mgt fee', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test //ToParticipant', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test //Principal', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test //Profit', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //Expand Test //Overpayment', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(8)').should((elem) => {
            expect(elem.text()).to.equal('$500.00')
        })
    })


    it('Assert for IOCOD5 merchant overpayment //TOTAL//payment', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$152,340.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //TOTAL//ToParticipant', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$76,170.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //TOTAL//Principal', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$45,885.00')
        })
    })
    it('Assert for IOCOD5 merchant overpayment //TOTAL//Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$30,285.00')
        })
    })

})

describe ('Assert values in IOCOD8 after rcode payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })
              cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
        .contains('Payment').click() 
        
    })
  
 it('Check rcode payment for IOCOD8', () => {     
            cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
})
it('Expanding rcode payment', () => {

                cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
                cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(3) > td:nth-child(2)').should((elem) => {
                 expect(elem.text()).to.equal('$0.00')
                })              
          
            })
        })
   
    describe('Check rcode payment in merchant view page' , () => {
        before(() => {
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/merchants')
            cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true }) 
            cy.visit('/admin/merchants/view/8')                

    })

    it('verifying rcode name', () => {
      cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(4) > div > div:nth-child(7) > div.value').should((elem) => {
        expect(elem.text()).to.equal('R03 ( No Account/Unable to Locate Account ) ( 01-01-2022 )')

                  })
           })
  })
