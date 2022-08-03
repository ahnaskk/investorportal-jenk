describe('Creating Roll ins merchant', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants/create')
    })
    it('Creating merchant `roll_ins`', () => {
        cy.get('[name="name"]').type('roll_ins')
        cy.get('[name="first_name"]').type('roll_ins_mer')
        cy.get('[name="state_id"]').select('11', { force: true })
        cy.get('[name="industry_id"]').select('8', { force: true })
        cy.get('[name="merchant_email"]').type('test10@gmail.com')
        cy.get('[name="date_funded1"]').type('06-01-2021')
        cy.get('[name="funded"]').clear().type('30000')
        cy.get('[name="max_participant_fund_per"]').type('75', { force: true })
        cy.get('#company_max_1').clear().type('6750')
        cy.get('#company_max_2').clear().type('7875')
        cy.get('#company_max_3').clear().type('7875')
        cy.get('[name="factor_rate"]').type('2')
        cy.get('[name="commission"]').type('8')
        cy.get('[name="pmnts"]').type('15')
        cy.get('[name="credit_score"]').type('500')
        // cy.get('[name="sub_status_flag"]').select('1', { force: true })
        cy.get('[name="sub_status_id"]').select('1', { force: true })
        cy.get('[name="advance_type"]').select('Weekly ACH', { force: true })
        cy.get('[name="marketplace_status"]').select('0', { force: true })
        cy.get('[name="source_id"]').select('1', { force: true })
        cy.get('[name="lender_id"]').select('LenderE', { force: true })
        cy.get('[name="label"]').select('Insurance', {force: true})
        // cy.get('[name="m_s_prepaid_status"]').check('2')
        // cy.get('[name="underwriting_fee"]').select('1.75', { force: true })
        // cy.get('[name="underwriting_status[]"]').check('1')
        // cy.get('[name="underwriting_status[]"]').check('2')
        cy.get('[name="ach_pull"]').check()
        cy.get('[name="account_holder_name"]').type('federal')
        cy.get('[name="routing_number"]').type('121122676')
        cy.get('[name="account_number"]').type('000154534678')
        cy.get('#bankAcoountModalSubmit').click()
        cy.get('#merchant_create_form').submit();
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
})

describe('Assign investor in Roll Ins', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')    

    })
    it('Assigning `roll_ins`', () => {
        cy.get('label > .form-control').type('ROLL_INS')
        cy.get('#dataTableBuilder').contains('a', 'ROLL_INS').click({ force:true })
        cy.get('#assign_payment_button').click({force: true})
        cy.get('#date_start1').clear().type('01-01-2021', {force: true})
        cy.get('#date_end1').clear().type('09-13-2021', {force: true})
        cy.get('[value="Assign"]').contains('Assign').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div')
             .should((elem) => {
                expect(elem.text().trim()).to.equal('×\n             Success\n           Payment of $88,047.06 has been collected from 01-01-2021 till 09-13-2021 in the Insurance Category, which has been reinvested to ROLL_INS')
            }) 
       
    })
})   

describe('Assertion in merchant ROLL_INS view page', () => {
        before(() => {
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/merchants')
            cy.get('#dataTableBuilder_next').click({force: true})
            cy.get('label > .form-control').type('ROLL_INS')
            cy.get('#dataTableBuilder').contains('a', 'ROLL_INS').click({ force:true })
    
        })
        it('VP advance  funding percentage', () => { 
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > label')
            .should((elem) => {
               expect(elem.text().trim()).to.equal('52.9%')
           }) 
        })
        it('VP advance  funding Share', () => { 
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > div > div:nth-child(2)')
            .should((elem) => {
                expect(elem.text().split('Share').pop().trim()).to.equal('$42,152.85')
            }) 
        })
    
        it('Velocity  percentage', () => { 
            cy.get('      body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(2) > div > label')
            .should((elem) => {
                expect(elem.text().trim()).to.equal('47.1%')
            }) 
        })
        it('Velocity  Share', () => { 
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(2) > div > div > div:nth-child(2)')
            .should((elem) => {
                expect(elem.text().split('Share').pop().trim()).to.equal('$37,527.74')
            }) 
        })
        it('Investor tab Total amount', () => { 
            cy.get('#investorTable > tbody > :nth-child(9) > :nth-child(3)').should('have.text', '$79,680.59')
    
        })
        it('Investor tab RTR', () => { 
            cy.get('#investorTable > tbody > :nth-child(9) > :nth-child(4)').should('have.text', '$159,361.18')
    
        })
        it('Investor tab Total Invested', () => { 
            cy.get('#investorTable > tbody > :nth-child(9) > :nth-child(5)').should('have.text', '$88,047.06')
    
        })
        it('Investor tab Total Balance', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(8)').should((elem) => {
                expect(elem.text().trim()).to.equal('$159,361.18')
            })
    
        })
        it('Investor tab Total Principal', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(9)').should('have.text', '$0.00')
    
        })
        it('Investor tab Total Profit', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(10)').should('have.text', '$0.00')
    
        })
        it('Investor tab Total Share', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(11)').should((share) => {
                expect(share.text().trim()).to.equal('99.99%')
            })
    
        })
        it('Investor tab Total Paid mgt fee', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(12)').should((mgt) => {
                expect(mgt.text().trim()).to.equal('$0.00')
            })
    
        })
        it('Investor tab Total Syndication fee', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(13)').should((syndi) => {
                expect(syndi.text().trim()).to.equal('$1,992.02')
            })
    
        })
        it('Investor tab Total Underwrting fee', () => { 
            cy.get('#investorTable > tbody > tr:nth-child(9) > td:nth-child(14)').should('have.text', '$0.00')
    
        })
})
    
describe('Assertion in dashboard', () => {
        before(() => {
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/dashboard')
            cy.get('[name="company[]"]').check()
            cy.get('#company-dashboard-form').submit()
    
        })
    
        it('Dashboard Total RTR', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(1) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$489,620.89')
            })
        })
        it('Dashboard Expected rtr', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(2) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$234,351.95')
            })
        })
        it('Dashboard Investors', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(3) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('12')
            })
        })
        it('Dashboard Merchants', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(4) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('11')
            })
        })
        it('Dashboard Liquidity', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(5) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$729,003.20')
            })
        })
        it('Dashboard Syndicates', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$221,931.03')
            })
        })
        it('Dashboard VP advance', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$259,130.51')
            })
        })
        it('Dashboard Velocity', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$247,941.66')
            })
        })
        it('Dashboard Total amount invested', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$301,963.74')
            })
        })
        it('Dashboard Current Invested', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$141,110.70')
            })
        })
        it('Dashboard CTD', () => {
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
                expect(elem.text()).to.equal('$255,268.94')
            })
        })
    
})

describe('Payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')    

    })
    it('Creating `roll_ins` complete payments', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('ROLL_INS')
        cy.get('#dataTableBuilder').contains('a', 'ROLL_INS').click({ force:true })
        cy.get('.flexMenu-viewMore > [href="#"]').click({force: true})
        cy.contains(' Add Payment').click({force: true})
        cy.url().should('contains', '/admin/payment/create')
        cy.get('#select_all').click()
        cy.get('#payment').clear({force: true}).type('159361.32', { force: true })
        cy.get('[name="payment_date1"]').type('06-10-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
            
        })
} )
    
describe('Assertion in dashboard after payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()

    })

    it('Dashboard Total RTR', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$489,621.03')
        })
    })
    it('Dashboard Expected rtr', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$79,771.60')
        })
    })
    it('Dashboard Investors', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })
    it('Dashboard Merchants', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(4) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('11')
        })
    })
    it('Dashboard Liquidity', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(5) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$883,583.69')
        })
    })
    it('Dashboard Syndicates', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,931.17')
        })
    })
    it('Dashboard VP advance', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$340,907.04')
        })
    })
    it('Dashboard Velocity', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$320,745.48')
        })
    })
    it('Dashboard Total amount invested', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$301,963.74')
        })
    })
    it('Dashboard Current Invested', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$53,063.64')
        })
    })
    it('Dashboard CTD', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$409,849.43')
        })
    })

})


describe('Assert ROLL_INS values in merchant payments tab after full payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'ROLL_INS').click({ force:true })
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
        .contains('Payment').click()
        
    })

    it('Payment date', () => {
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(3) > a').should((elem) => {
            expect(elem.text()).to.equal('06-10-2021')
        })
    })
    it('Payment', () => {
        cy.get('#dataTableBuilder > tbody > tr > td.text-right.sorting_1 > span').should((elem) => {
            expect(elem.text()).to.equal('$159,361.32')
        })
    })
    it('Payment To Participant', () => {
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(5) > span').should((elem) => {
            expect(elem.text()).to.equal('$154,580.49')
        })
    })
    it('Payment Principal', () => {
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$88,047.06')
        })
    })
    it('Payment Profit', () => {
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$66,533.43')
        })
    })


    it('Payment Total Payment', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$159,361.32')
        })
    })
    it('Payment Total To Participant', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$154,580.49')
        })
    })
    it('Payment Total Principal', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$88,047.06')
        })
    })
    it('Payment Total Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$66,533.43')
        })
    })

    

})