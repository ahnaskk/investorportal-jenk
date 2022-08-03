describe('Tests for making merchant payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Make payment for `IOCOD1` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD1').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD1').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })


       // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-01-2021, 04-02-2021, 04-15-2021, 04-20-2021, 05-01-2021, 05-20-2021, 05-28-2021, 06-09-2021, 06-30-2021, 07-12-2021',{force: true})
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })



    it('Make payment for `IOCOD2` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD2').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD2').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-04-2021, 04-18-2021, 04-29-2021, 05-14-2021, 05-23-2021, 05-30-2021, 06-16-2021, 06-28-2021, 07-08-2021, 07-20-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD3` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD3').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD3').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-05-2021, 04-17-2021, 04-28-2021, 05-09-2021, 05-19-2021, 05-29-2021, 06-09-2021, 06-19-2021, 06-13-2021, 07-01-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD5` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD5').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-09-2021, 04-22-2021, 05-11-2021, 05-24-2021, 06-06-2021, 06-17-2021, 06-26-2021, 07-02-2021, 07-10-2021, 07-18-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD6` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD6').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('07-19-2021, 07-22-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD7` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD7').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD7').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('06-30-2021, 07-12-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD8` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD8').siblings()
        //     .contains('a', 'View')
        //     .click()
         //   cy.get('.alert > .btn-primary').click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create')
        cy.get('#select_all').click({force: true})
        cy.get('#payment').clear({force: true}).type('25908', {force: true})
        cy.get('[name="payment_date1"]').type('07-25-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Make payment for `IOCOD9` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD9').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD9').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-04-2021, 04-18-2021, 04-29-2021, 05-14-2021, 05-23-2021, 05-30-2021, 06-16-2021, 06-28-2021, 07-08-2021, 07-20-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Make payment for `IOCOD10` merchant', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD10').click({ force:true })
        // cy.get('#dataTableBuilder').contains('td', 'IOCOD10').siblings()
        //     .contains('a', 'View')
        //     .click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        // cy.get('[title="Open/Close Menu"]')
        // .then(($a) => {
        //     cy.log($a)
        //     if ($a.text().includes('More')) {
        //         cy.contains('More')
        //             .click({ force: true })
        //         cy.contains('Add Payment').click({ force: true })
        //     } else {
        //         cy.get('.btn-success:nth-child(1)').click({ force: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('24376', {force: true})
        cy.get('[name="payment_date1"]').type('07-22-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

})

describe('Test to add payment from `Lender Payment Generation`', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/payment/lender_payment_generation')
    })
    it('Make lender payment for `IOCOD4` merchant', () => {
        cy.get('#lenders').select('LenderD', { force: true })
        cy.get('#datepicker11').type('07-25-2021', { force: true })
        cy.get('#company').select('0', { force: true })
        cy.get('#filter_form').submit()
        cy.get('.select_merchant[type="checkbox"]').click()
        cy.get('#paymentClick').click()
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-body.alert-box-body > div > h4').invoke('text').should('match', / Success/i)
    })
})

describe('Assert values from dashboard after merchant payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
    })
    it('Compare `Total net amount` from payment report with dashboard `CTD`', () => {
        cy.visit('/admin/reports/payments')
        cy.get('#date_start1').clear()
        cy.get('#date_end1').clear()
        cy.get('#report_totals').check()
        cy.get('#date_filter').click()
        cy.wait(5000)
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(9)').then(($el) => {
            cy.log($el.text())
            const netAmt = $el.text();
            cy.visit('/admin/dashboard')
            cy.get('[name="company[]"]').check()
            cy.get('#company-dashboard-form').submit()
            cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal(netAmt)
            })
        })
    })
})

describe('Assert values in dashboard after merchant payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$333,109.56')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$118,419.11')
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
            expect(elem.text()).to.equal('$776,471.77')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$220,000.04')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$286,416.92')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$270,054.81')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$213,916.68')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$76,444.98')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('584.07%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$214,690.45')
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
            expect(elem.text()).to.equal('$0.04')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$894,890.88')
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


describe('Assert values in IOCOD3 merchant payments tab after merchant payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD3').click({ force:true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a').contains('Payment').click()
    
        })

    it('Assert for IOCOD3 merchant payment //Date', () => {
        cy.get('tbody > :nth-child(1) > :nth-child(3) > a').should((elem) => {
            expect(elem.text()).to.equal('07-01-2021')
        })
    })
    it('Assert for IOCOD3 merchant payment //Payment', () => {
        cy.get(':nth-child(1) > .sorting_1 > span').should((elem) => {
            expect(elem.text()).to.equal('$2,950.00')
        })
    })
    it('Assert for IOCOD3 merchant payment //ToParticipant', () => {
        cy.get('tbody > :nth-child(1) > :nth-child(5) > span').should((elem) => {
            expect(elem.text()).to.equal('$2,950.00')
        })
    })
    it('Assert for IOCOD3 merchant payment //principal', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$1,916.66')
        })
    })
    it('Assert for IOCOD3 merchant payment //profit', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$1,033.34')
        })        
    })
    
    it('Assert for IOCOD3 merchant payments //Expand Test //investor name', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('[title="InvestorVP-1"]').should((elem) => {
            expect(elem.text()).to.equal('INVESTORVP..')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //participant share', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$351.11')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //mgt fee', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //ToParticipant', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$351.11')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //Principal', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$228.12')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //Profit', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$122.99')
        })
    })
    it('Assert for IOCOD3 merchant payments //Expand Test //Overpayment', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(10) > td:nth-child(8)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total Participant Share', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(2)').should((elem) => {
            expect(elem.text()).to.equal('$2,950.00')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total Mgt fee', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total ToParticipant', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$2,950.00')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total principal', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$1,916.66')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total Profit', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$1,033.34')
        })
    })
    it('Assert for IOCOD3 merchant payments Expand Test Total Overpayment', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(1) > td.details-control').click({force: true})
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })


})
