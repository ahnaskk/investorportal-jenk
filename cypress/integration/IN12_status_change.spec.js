describe('Tests for merchants status changing to default', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6').click({ force:true })

    })
    it('IOCOD6 merchants Status changing to default', () => {
       
        cy.get('#sub_status_id').select('Default', {force: true})
        cy.get('#change_substatus > .modal-body > p').should('have.text', 'Do you want to change status now ?')
        cy.get('#submitChangeStatus').click()
        cy.wait(5000)
        cy.get(':nth-child(3) > .merchant-details > :nth-child(3) > .value').should('have.text', 'Default')
    
    })

})

describe('IOCOD6 merchants Payment tab assertion', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()

    })
    it('Principal', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(5)').should('have.text', '$244.78')
        
    })
    it('Profit', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(6)').should('have.text', '$-244.78')
    })
    it('Reason', () => {
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(7)').should('have.text', 'Changed to Default')
        
    })

})

describe('Assert values in dashboard after merchant status changed to default', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$480,457.83')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$70,608.40')
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
            expect(elem.text()).to.equal('$883,583.69')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,931.17')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$340,907.04')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$320,745.48')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$301,963.74')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$46,715.66')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('479.6%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$409,849.43')
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
            expect(elem.text()).to.equal('1.38%')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$1,931.17')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$954,192.09')
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

describe('Assert values in default rate report', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/reports/defaultRateReport')
        cy.get('#from_date1').clear()
        cy.get('#to_date1').clear()
        cy.get('#apply').click( {force: true} )

    })
    it('default invested amount', () => {
        cy.get('tfoot > tr > :nth-child(3)').should('have.text', '$6,103.20')
        
    })
    it('default rtr amount', () => {
        cy.get('tfoot > tr > :nth-child(4)').should('have.text', '$9,163.20')
        
    })
    it('over payment', () => {
        cy.get('tfoot > tr > :nth-child(7)').should('have.text', '$0.00')
        
    })
    
})

describe('Reverting status to active advance', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6').click({ force:true })
    
    })
    it('IOCOD6 merchants Status changing to Active advance', () => {
       
        cy.get('#sub_status_id').select('Active Advance', {force: true})
        cy.get('#change_substatus > .modal-body > p').should('have.text', 'Do you want to change status now ?')
        cy.get('#submitChangeStatus').click()
        cy.wait(5000)
        cy.get(':nth-child(3) > .merchant-details > :nth-child(3) > .value').should('have.text', 'Active Advance')
    
    })

    it('IOCOD6 merchant, Assertion in payment tab after status change //Total Principal', () => {
       
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click() 
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should('have.text', '$552.02')
    })
    it('IOCOD6 merchant, Assertion in payment tab after status change //Total Profit', () => {
       
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click() 
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should('have.text', '$244.78')
    })
    it('IOCOD6 merchant, Assertion in payment tab after status change //ToParticipant', () => {
       
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click() 
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should('have.text', '$796.80')
    })

})

describe('IOCOD6 merchant, Adding a payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6').click({ force:true })
    
    })
    it('IOCOD6 merchant, Adding a payment', () => {
        // cy.get('#dataTableBuilder > tbody > tr:nth-child(9) > td:nth-child(3) > a:nth-child(1)').click({force: true})
        cy.visit('/admin/merchants/view/6')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(7) > a').click({force: true})
        cy.url().should('contains', '/admin/payment/create')
        cy.get('#select_all').click()
        cy.get('#datepicker1').type('02-01-2021', {force: true})
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
                     .should('match', / Success/i)

})

it('IOCOD6 merchant, Assertion in payment tab after one payment //ToParticipant', () => {
   
    cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should('have.text', '$1,992.00')
})
it('IOCOD6 merchant, Assertion in payment tab after one payment //Total Principal', () => {
   
    cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should('have.text', '$828.03')  
})  
it('IOCOD6 merchant, Assertion in payment tab after one payment      //Total Profit', () => {
   
    cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should('have.text', '$367.17')
})  
it('IOCOD6 merchant, Assertion in payment tab after one payment //ToParticipant', () => {
   
    cy.get(':nth-child(2) > .nav-link').contains('Payments').click()
    cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should('have.text', '$1,195.20')
}) 
})

describe('Assert values in default rate report after changing status', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/reports/defaultRateReport')
        cy.get('#from_date1').clear()
        cy.get('#to_date1').clear()
        cy.get('#apply').click( {force: true} )

    })
    it('Assert for default report //default invested amount', () => {
        cy.get('tfoot > tr > :nth-child(3)').should('have.text', '$0.00')  
    })
    it('Assert for default report  //default rtr amount', () => {
        cy.get('tfoot > tr > :nth-child(4)').should('have.text', '$0.00')
    })
    it('Assert for default report      //over payment', () => {
        cy.get('tfoot > tr > :nth-child(7)').should('have.text', '$0.00')
    })
      
})

describe('Tests for merchants status changing to Settled', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD10').click({ force:true })
        
    })
    it('IOCOD10 merchant making net zero balance 0.00', () => {
        cy.contains('Add Payment').click({ force: true })
        cy.get('#select_all').click();
        cy.get('#payment').clear({force: true}).type('8000', {force: true})
        cy.get('[name="payment_date1"]').type('07-26-2021',{force: true})
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
        
    })
    it('IOCOD10 merchant status changing to settled', () => {
        cy.get('#sub_status_id').select('Settled', {force: true})
        cy.get('#change_substatus > .modal-body > p').should('have.text', 'Do you want to change status now ?')
        cy.get('#submitChangeStatus').click()
        cy.wait(5000)
        cy.get(':nth-child(3) > .merchant-details > :nth-child(3) > .value').should('have.text', 'Settled')
    })

})

describe('IOCOD10 merchants Payment tab assertion after status changing to settled', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD10').click({ force:true })
        cy.get(':nth-child(2) > .nav-link').contains('Payments').click()

    })
    it('Netzerobalance in IOCOD10 viw page', () => {  
        cy.get(':nth-child(4) > .merchant-details > :nth-child(4) > .value').should((elem) => {
            expect(elem.text().trim()).to.equal('$0.00')
        })
    })
    it('Principal in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(5)').should('have.text', '$4,200.78')
    })
    it('Profit in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(6)').should('have.text', '$-4,200.78')
    })
    it('Reason in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tbody > :nth-child(1) > :nth-child(7)').should('have.text', 'Changed to Settled')
    })
    it('Total Payment in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(3)').should('have.text', '$34,042.67')
    })
    it('Total ToParticipant in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should('have.text', '$16,595.80')
    })
    it('Total Principal in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should('have.text', '$13,162.50')
    })
    it('Total Profit in IOCOD10 viw page', () => {  
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should('have.text', '$3,433.30')
    })

})

describe('Assert values in dashboard after merchant status changed to settled', () => {
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
            expect(elem.text()).to.equal('$887,882.09')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,931.17')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$342,972.64')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$322,978.28')
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

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('504.46%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$414,147.83')
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
            expect(elem.text()).to.equal('$1,931.17')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$955,576.09')
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

describe('Assert Total values in default rate report after changing status to settled', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/reports/defaultRateReport')
        cy.get('#from_date1').clear()
        cy.get('#to_date1').clear()
        cy.get('#apply').click( {force: true} )

    })
    it('Assert for Default invested amount in default report ', () => {
        cy.get('tfoot > tr > :nth-child(3)').should('have.text', '$0.00')
    })
    it('Assert for Default rtr amount in default report ', () => {
        cy.get('tfoot > tr > :nth-child(4)').should('have.text', '$7,779.20')
    })
    it('Assert for Over payment in default report ', () => {
        cy.get('tfoot > tr > :nth-child(7)').should('have.text', '$0.00')
    })

    
})


