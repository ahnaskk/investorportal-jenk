describe('Upsell commision create merchant', () => {
    beforeEach(() => {
                cy.login({ email: 'admin@investor.portal' })
                cy.visit('/admin/merchants/create')
            })
            it('creating merchant', ()=> {
                
                cy.get('[name="name"]').type('upsell_commision')
                cy.get('[name="first_name"]').type('upsell_commision')
                cy.get('[name="state_id"]').select('11', { force: true })
                cy.get('[name="industry_id"]').select('8', { force: true })
                cy.get('[name="merchant_email"]').type('upsell_commision@gmail.com')
                cy.get('[name="date_funded1"]').type('07-01-2021')
                cy.get('[name="funded"]').clear().type('30000')
                cy.get('[name="max_participant_fund_per"]').type('100', { force: true })
                cy.get('#company_max_1').clear().type('15000')
                cy.get('#company_max_2').clear().type('15000')
                cy.get('[name="factor_rate"]').type('1.5')
                cy.get('[name="commission"]').type('5')
                cy.get('[name="pmnts"]').type('10')
                cy.get('[name="credit_score"]').type('500')
                // cy.get('[name="sub_status_flag"]').select('1', { force: true })
                 cy.get('[name="sub_status_id"]').select('1', { force: true })
                 cy.get('[name="advance_type"]').select('Weekly ACH', { force: true })
                 cy.get('[name="marketplace_status"]').select('0', { force: true })
                 cy.get('[name="source_id"]').select('1', { force: true })
                 cy.get('[name="lender_id"]').select('LenderD', { force: true })
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

describe('Assign new investor', () => {
    beforeEach(() => {
             
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/merchants')    
            cy.get('label > .form-control').type('UPSELL_COMMISION')
            cy.get('#dataTableBuilder').contains('a', 'UPSELL_COMMISION').click({force:true})
    })
    it('Assigning INVESTOR VP', ()=> {
        cy.get('[data-cy="cy_assign_new_inv"]').click({force: true})
      //  cy.get('#company').select('VP Advance Funding', {force:true})
        cy.get('#user_id').select('791',{force:true})

        cy.get('#input_amount_field').type('15000' ,{force:true})
        cy.get('#input_mgmnt_fee_per').select('1.00', {force:true})
        cy.get('#input_underwriting_fee_per').select('1.00', {force:true})
        cy.get('#input_upsell_commission_per').select('10.00', {force:true})
        cy.get('#input_synd_fee_per').select('1.00', {force:true})  
        cy.get('#add_btn').click({force:true})  
        cy.wait(4000)
      
        cy.get('#amount').should('have.value','15000')
        cy.get('#mgmnt_fee').should('have.value','1.00')
        cy.get('#underwriting_fee').should('have.value','1.00')
        cy.get('#upsell_commission').should('have.value','10.00')
        cy.get('#syndication_fee').should('have.value','1.00')
        cy.get('#total_participant_amount').should('have.value', '15000')
        cy.get('#total_mgmnt_fee').should('have.value', '225')
        cy.get('#total_underwriting_fee').should('have.value', '150')
        cy.get('#total_upsell_commission').should('have.value', '1500')
        cy.get('#total_syndication_fee').should('have.value', '150')

        cy.get('#save_btn').click()

    })


    
    it('Assigning VELOCITY ', ()=> {
        cy.get('[data-cy="cy_assign_new_inv"]').click({force: true})
//cy.get('#company').select('Velocity', {force:true})
        cy.get('#user_id').select('793',{force:true})

        cy.get('#input_amount_field').type('15000' ,{force:true})
        cy.get('#input_mgmnt_fee_per').select('1.00', {force:true})
        cy.get('#input_underwriting_fee_per').select('1.00', {force:true})
        cy.get('#input_upsell_commission_per').select('5.00', {force:true})
        cy.get('#input_synd_fee_per').select('1.00', {force:true})  
        cy.get('#add_btn').click({force:true})
        cy.wait(4000)

        cy.get('#amount').should('have.value','15000')
        cy.get('#mgmnt_fee').should('have.value','1.00')
        cy.get('#underwriting_fee').should('have.value','1.00')
        cy.get('#upsell_commission').should('have.value','5.00')
        cy.get('#syndication_fee').should('have.value','1.00')
        cy.get('#total_participant_amount').should('have.value', '15000')
        cy.get('#total_mgmnt_fee').should('have.value', '225')
        cy.get('#total_underwriting_fee').should('have.value', '150')
        cy.get('#total_upsell_commission').should('have.value', '750')
        cy.get('#total_syndication_fee').should('have.value', '150')

        cy.get('#save_btn').click()


    })
    
})

describe('Assertions in investors tab Total after Assign new investor', () => {
        beforeEach(() => {    
                cy.login({ email: 'admin@investor.portal' })
                cy.visit('/admin/merchants')    
                cy.get('label > .form-control').type('UPSELL_COMMISION')
                cy.get('#dataTableBuilder').contains('a', 'UPSELL_COMMISION').click({force:true})
        })
        it('Amount total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(3)').should((amount) => {
                expect(amount.text().trim()).to.equal('$30,000.00')
            })
        })
        it('RTR total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(4)').should((amount) => {
                expect(amount.text().trim()).to.equal('$45,000.00')
            })
        })
        it('Total invested total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(5)').should((amount) => {
                expect(amount.text().trim()).to.equal('$34,350.00')
            })
        })
        it('Recieved amount total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(6)').should((amount) => {
                expect(amount.text().trim()).to.equal('$0.00')
            })
        })
        it('CTD total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(7)').should((amount) => {
                expect(amount.text().trim()).to.equal('$0.00')
            })
        })
        it('Balance total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(8)').should((amount) => {
                expect(amount.text().trim()).to.equal('$45,000.00')
            })
        })
        it('Principal total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(9)').should((amount) => {
                expect(amount.text().trim()).to.equal('$0.00')
            })
        })
        it('Profit total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(10)').should((amount) => {
                expect(amount.text().trim()).to.equal('$0.00')
            })
        })
        it('Share total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(11)').should((amount) => {
                expect(amount.text().trim()).to.equal('100%')
            })
        })
        it('mgt fee total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(12)').should((amount) => {
                expect(amount.text().trim()).to.equal('$0.00')
            })
        })
        it('Syndication fee total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(13)').should((amount) => {
                expect(amount.text().trim()).to.equal('$300.00')
            })
        })
        it('Underwriting total', ()=> {
            cy.get('#investorTable > tbody > :nth-child(3) > :nth-child(14)').should((amount) => {
                expect(amount.text().trim()).to.equal('$300.00')
            })
        })
})

describe('Assertions in Upsell Commission Report after Assign new investor', () => {
    beforeEach(() => {
             
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/reports/upsell-commission')
            cy.get('#date_start1').clear({force:true})
            cy.get('#date_end1').clear({force:true})
            cy.get('#apply').click({force:true})    
            
    })
    it('Assertion, Total amount in Upsell Commission Report', () => {
     
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((amount) => {
            expect(amount.text().trim()).to.equal('$34,350.00')
        })
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((amount) => {
            expect(amount.text().trim()).to.equal('$2,250.00')
        })
    })
 
})


describe('Expand test in Upsell Commission Report', () => {
    beforeEach(() => {
             
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/reports/upsell-commission')
            cy.get('#date_start1').clear({force:true})
            cy.get('#date_end1').clear({force:true})
            cy.get('#apply').click({force:true})  
            cy.get('#dataTableBuilder > tbody > tr > td.details-control.sorting_1').click({force:true})  
            
    })
    it('Assert INVESTORVP-2 in Expand Upsell Commission Report', () => {
     
    
        cy.get('tbody > :nth-child(2) > :nth-child(1) > a').should((amount) => {
            expect(amount.text().trim()).to.equal('INVESTORVP-2')
        })
        cy.get('.table > tbody > :nth-child(2) > :nth-child(2)').should((amount) => {
            expect(amount.text().trim()).to.equal('$17,550.00')
        })
        cy.get('.table > tbody > :nth-child(2) > :nth-child(3)').should((amount) => {
            expect(amount.text().trim()).to.equal('$1,500.00')
        })
        
    })
    it('Assert INVESTORVP-2 in Expand Upsell Commission Report', () => {
     
    
        cy.get('tbody > :nth-child(3) > :nth-child(1) > a').should((amount) => {
            expect(amount.text().trim()).to.equal('INVESTORVEL-2')
        })
        cy.get('.table > tbody > :nth-child(3) > :nth-child(2)').should((amount) => {
            expect(amount.text().trim()).to.equal('$16,800.00')
        })
        cy.get('tbody > :nth-child(3) > :nth-child(3)').should((amount) => {
            expect(amount.text().trim()).to.equal('$750.00')
        })
        
    })
 
})

describe('Tests for making merchant payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Make full payments for `UPSELL_COMMISION` merchant', () => {
        cy.get('label > .form-control').type('UPSELL_COMMISION')
        cy.get('#dataTableBuilder').contains('a', 'UPSELL_COMMISION').click({force:true})
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
        cy.contains('Add Payment').click({ force: true })
        cy.get('#select_all').click();
        cy.get('#payment').clear().type('45,000', {force:true})
        cy.get('[name="payment_date1"]').type('07-02-2021',{force: true})
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

})

describe('Expand test in Upsell Commission Report', () => {
    beforeEach(() => {
             
            cy.login({ email: 'admin@investor.portal' })
            cy.visit('/admin/reports/upsell-commission')
            cy.get('#date_start1').clear({force:true})
            cy.get('#date_end1').clear({force:true})
            cy.get('#apply').click({force:true})  
            cy.get('#dataTableBuilder > tbody > tr > td.details-control.sorting_1').click({force:true})  
            
    })
    it('Assert INVESTORVP-2 in Expand Upsell Commission Report', () => {
     
    
        cy.get('tbody > :nth-child(2) > :nth-child(1) > a').should((amount) => {
            expect(amount.text().trim()).to.equal('INVESTORVP-2')
        })
        cy.get('.table > tbody > :nth-child(2) > :nth-child(2)').should((amount) => {
            expect(amount.text().trim()).to.equal('$17,550.00')
        })
        cy.get('.table > tbody > :nth-child(2) > :nth-child(3)').should((amount) => {
            expect(amount.text().trim()).to.equal('$1,500.00')
        })
        
    })
    it('Assert INVESTORVP-2 in Expand Upsell Commission Report', () => {
     
    
        cy.get('tbody > :nth-child(3) > :nth-child(1) > a').should((amount) => {
            expect(amount.text().trim()).to.equal('INVESTORVEL-2')
        })
        cy.get('.table > tbody > :nth-child(3) > :nth-child(2)').should((amount) => {
            expect(amount.text().trim()).to.equal('$16,800.00')
        })
        cy.get('tbody > :nth-child(3) > :nth-child(3)').should((amount) => {
            expect(amount.text().trim()).to.equal('$750.00')
        })
        
    })
 
})

describe('Assert values in dashboard ', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$792,629.61')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$103,237.77')
        })
    })

    it('Assert for `Investors` value in dashboard', () => {
        cy.get('.total_investors-widget h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })

    it('Assert for `Merchants` value in dashboard', () => {
        cy.get('.total_merchants-widget h3').should((elem) => {
            expect(elem.text()).to.equal('13')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$882,248.70')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$221,231.35')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$341,430.47')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$319,586.88')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$582,841.14')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$58,514.40')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('504.46%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$689,391.84')
        })
    })

    it('Assert for `Investor Portfolio` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(15) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$775,698.00')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$1,231.35')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$985,486.47')
        })
    })

})



    
