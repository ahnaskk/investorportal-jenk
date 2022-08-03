describe('Tests for revert merchant payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Revert 2 payments of `IOCOD5` merchant and adds it again', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        cy.get('.card-header').contains('Payments').click()
        cy.get('.odd:nth-child(1) span > .btn').click();
        cy.get('#PaymentRevertSubmit').click();
        cy.get('.swal2-confirm').click();
        cy.get('.card-header').contains('Payments').click()
        cy.get('.odd:nth-child(3) span > .btn').click();
        cy.get('#PaymentRevertSubmit').click();
        cy.get('.swal2-confirm').click();
    })

})

describe('Assert Payment tab values after revert payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    })
    //75670 column
        it('Payment', () => {
            cy.get(':nth-child(1) > .sorting_1 > span').should('have.text', '$-75,670.00')
        })
        it('To participant', () => {
            cy.get('tbody > :nth-child(1) > :nth-child(5) > span').should('have.text', '$-37,835.00')
        })
        it('Principal', () => {
            cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(6)').should('have.text', '$-22,942.60')
        })
        it('Profit', () => {
            cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(7)').should('have.text', '$-14,892.40')
        })

        //1000 overpayment revert column
        it('Payment', () => {
            cy.get(':nth-child(2) > .sorting_1 > span').should('have.text', '$-1,000.00')
        })
        it('To participant', () => {
            cy.get('tbody > :nth-child(2) > :nth-child(5) > span').should('have.text', '$-500.00')
        })
        it('Principal', () => {
            cy.get('#dataTableBuilder > tbody > :nth-child(2) > :nth-child(6)').should('have.text', '$0.00')
        })
        it('Profit', () => {
            cy.get('#dataTableBuilder > tbody > :nth-child(2) > :nth-child(7)').should('have.text', '$-500.00')
        })

        //Total 
        it('Total Payment', () => {
            cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should('have.text', '$75,670.00')
        })
        it('Total To participant', () => {
            cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should('have.text', '$37,835.00')
        })
        it('Total Principal', () => {
            cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should('have.text', '$22,942.40')
        })
        it('Total Profit', () => {
            cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should('have.text', '$14,892.60')
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
            expect(elem.text()).to.equal('$481,341.83')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$105,529.00')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$849,547.09')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$301,963.74')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$69,423.45')
        })
    })
    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$375,812.83')
        })
    })

    it('Assert for `Default Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(16) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('0%')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$1,431.17')
        })
    })

})

describe('Renter Revert payments', () => {

    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder > tbody > tr:nth-child(3) > td:nth-child(3) > a:nth-child(1)').contains('a', 'IOCOD5').click({ force:true })
        // cy.get('[title="View More"]').then(($a) => {
        // cy.log($a)
        // if ($a.text().includes('More')) {
        //     cy.contains('More')
        //         .click({ force: true })
            cy.contains('Add Payment').click({ force: true })
        // } else {
            // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(6) > a').click({ force: true });
    //     // }
    // })
    })

    it('Make 100% payment for `IOCOD5` merchant', () => {
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('75,670.00', {force: true})
        cy.get('[name="payment_date1"]').type('07-27-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Make Over payment for `IOCOD5` merchant', () => {
        cy.url().should('contains', 'https://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('1000.00', {force: true})
        cy.get('[name="payment_date1"]').type('07-28-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})

describe('Assert Payment tab values after Renter revert payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
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

describe('Assert values in dashboard after merchant Renter revert payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$481,841.83')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$67,694.00')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$887,882.09')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$301,963.74')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$46,480.85')
        })
    })
    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$414,147.83')
        })
    })

    it('Assert for `Default Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(16) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('0%')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$1,931.17')
        })
    })

})

