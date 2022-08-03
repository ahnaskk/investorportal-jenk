describe('Tests for delete merchant payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Deletes 3 payments from `IOCOD3` merchant', () => {
        cy.get('#dataTableBuilder_filter > label > input').type('IOCOD3')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD3').click({ force:true })
        cy.get('.card-header').contains('Payments').click()
        cy.get('.odd:nth-child(1) .delete_bulk').check();
        cy.get('.even:nth-child(2) .delete_bulk').click();
        cy.get('.odd:nth-child(3) .delete_bulk').click();
        cy.get('#delete_multi_submit').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

    })
    it('Deletes only over payment from `IOCOD8` merchant', () => {
        cy.get('#dataTableBuilder_filter > label > input').type('IOCOD8')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })
        cy.get('.card-header').contains('Payments').click()
        cy.get('#dataTableBuilder > tbody > tr.even > td.checkbox11.delayHover > label > span').check({ force:true });
        cy.get('#delete_multi_submitt').click({ force:true });
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

    })
})

describe('Assert Payment tab for merchant iocod3', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type('IOCOD3')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD3').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    })
    it('Assert for IOCOD3 Total payment', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$20,650.00')
        })
    })
    it('Assert for IOCOD3 Total ToParticipant', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$20,650.00')
        })
    })
    it('Assert for IOCOD3 Total Principal', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$13,416.62')
        })
    })
    it('Assert for IOCOD3 Total Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$7,233.38')
        })
    })

})

describe('Assert Payment tab for merchant iocod8', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type('IOCOD8')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    })
    it('Assert for IOCOD8 Total payment', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$25,908.00')
        })
    })
    it('Assert for IOCOD8 Total ToParticipant', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$18,135.60')
        })
    })
    it('Assert for IOCOD8 Total Principal', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$12,588.24')
        })
    })
    it('Assert for IOCOD8 Total Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$5,547.36')
        })
    })

})


describe('Assert values in dashboard after merchant Delete payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$481,141.83')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$76,544.00')
        })
    })

    it('Assert for `Investors` value in dashboard', () => {
        cy.get('.total_investors-widget h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })

    it('Assert for `Merchants` value in dashboard', () => {
        cy.get('.total_merchants-widget h3').should((elem) => {
            expect(elem.text()).to.equal('11')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$878,332.09')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,231.17')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$340,022.92')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$317,078.00')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$301,963.74')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$52,230.83')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('504.46%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$404,597.83')
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
            expect(elem.text()).to.equal('$1,231.17')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$954,876.09')
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
