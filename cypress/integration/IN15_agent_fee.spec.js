describe('Agent fee settings', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    it('System settings edit', ()=> {
        
        cy.visit('/admin/settings/system_settings')
        cy.get('#sub_status').select('Advance Completed', {force: true})
        cy.get('#sub_status').select('Collections', {force: true})

        cy.get('#sub').click({force: true})
    })
    it('Advance settings edit', ()=> {
        
        cy.visit('/admin/settings')
        cy.get('#inputAgentFeePer').clear().type('10', {force: true})
        cy.get(':nth-child(1) > .form-box-styled > .btn-wrap > .btn-box > .btn').click({force:true})
    })
})

describe('creating a agent fee merchant', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants/create')
    })
    it('creating merchant', ()=> {
        
        cy.get('[name="name"]').type('agent fee mer')
        cy.get('[name="first_name"]').type('agent fee mer')
        cy.get('[name="state_id"]').select('11', { force: true })
        cy.get('[name="industry_id"]').select('8', { force: true })
        cy.get('[name="merchant_email"]').type('agent_fee@gmail.com')
        cy.get('[name="date_funded1"]').type('06-01-2021')
        cy.get('[name="funded"]').clear().type('20000')
        cy.get('[name="max_participant_fund_per"]').type('100', { force: true })
        cy.get('#company_max_1').clear().type('6000')
        cy.get('#company_max_2').clear().type('7000')
        cy.get('#company_max_3').clear().type('7000')
        cy.get('[name="factor_rate"]').type('1.2')
        cy.get('[name="commission"]').type('5')
        cy.get('[name="pmnts"]').type('10')
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

describe('Assigning agent fee merchant with Roll Ins', () => {
    beforeEach(() => {
        cy.viewport(2000, 700)
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')    
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force:true})

    })
    it('Assigning `roll_ins`', () => {
        cy.get('#assign_payment_button').click({force:true})
        cy.get('#date_start1').clear({force: true}).type('01-01-2021', {force: true})
        cy.get('#date_end1').clear({force: true}).type('09-01-2021', {force: true})
        cy.get('[value="Assign"]').contains('Assign').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div')
             .should((elem) => {
                expect(elem.text().trim()).to.equal('×\n             Success\n            Payment of $246,527.41	 has been collected from 01-01-2021 till 09-13-2021 in the Insurance Category, which has been reinvested to AGENT FEE MER')
            })    
    })
    it('Status changing to collections', () => {
        cy.get('#sub_status_id').select('Collections', {force: true})
        cy.get('#change_substatus > .modal-body > p').should('have.text', 'Do you want to change status now ?')
        cy.get('#submitChangeStatus').click({force: true})
        cy.wait(5000)
        cy.get(':nth-child(3) > .merchant-details > :nth-child(3) > .value').should('have.text', 'Collections')        
       
     })

    it('Enable agent fee button', () => {
        cy.wait(5000)
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap').then(($agent) => {
            if ($agent.find('[data-cy="cy_agent_fee_btn"]').length) {
                cy.contains('Agent Fee Off').click({ force: true });
            }
        })
        cy.get('.box-head > .alert').should('have.text', 'Success! Agent Fee Status Updated Successfully')
        cy.get('#agent_fee_per_div > .value').should('have.text', '10%')
    
    })  
})

describe('Adding payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')    

    })
   
    it('Adding one payment', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force:true})
        cy.contains('Add Payment').click({ force: true })
        cy.get('#select_all').click({force: true});
       // cy.get('#payment').clear().type('')
        cy.get('[name="payment_date1"]').type('06-01-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
        
    })

})

describe('Assertions in view page after one payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')        
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true})     

    })
   
    it('Agent fee in view page', () => {
        cy.get(':nth-child(4) > .merchant-details > :nth-child(8) > .value').should('have.text', '$2,751.93')       
    })
    it('Syndicate payment in view page', () => {
        cy.get(':nth-child(4) > .merchant-details > :nth-child(1) > div.value').should('have.text', '$27,519.34 ')       
    })
    
})

describe('Payment tab assertion after one payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})


    })
    it('Assert for `Payments` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > .sorting_1').should((elem) => {
            expect(elem.text()).to.equal('$27,519.34')
        })
    })
    it('Assert for `To participant` in payment tab', () => {
        cy.get('.odd > :nth-child(5) > span').should((elem) => {
            expect(elem.text()).to.equal('$24,024.38')
        })
    })
    it('Assert for `Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$24,024.38')
        })
    })
    it('Assertfor `Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `Agent fee icon` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr.odd > td:nth-child(5) > div').click({force: true}) 
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(5) > p').should((elem) => {
            expect(elem.text()).to.equal('Agent Fee : $2,751.93')
        })

    })
})

describe('Payment tab Total assertion after one payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})


    })
    it('Assert for `Total Payments` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$27,519.34')
        })
    })
    it('Assert for `Total To participant` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$24,024.38')
        })
    })
    it('Assert for `Total Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$24,024.38')
        })
    })
    it('Assert for `Total Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    
})

describe('Payment Expanding test in Payment tab after one payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})
        cy.get('.odd > .details-control').click({force: true})
    })

    it('Assert for `Participant name` in payment tab', () => {
    
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(2)').should((elem) => {
            expect(elem.text()).to.equal('FEE..')
        })
    })
    it('Assert for `Participant Share` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Management Fee` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `To Participant` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Overpayment` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(8)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `Total Participant Share` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(2)').should((elem) => {
            expect(elem.text()).to.equal('$247,674.26')
        })
    })
    it('Assert for `Total Management Fee` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$6,687.20')
        })
    })
    it('Assert for `Total ToParticipant` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$240,987.06')
        })
    })
    it('Assert for `Total Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$216,219.45')
        })
    })
    it('Assert for `Total Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.61')
        })
    })
    it('Assert for `Total Overpayment` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.18')
        })
    })
    
})


describe('Adding Full Payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')    

    })
   
    it('Adding Complete Payment', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force:true})
        cy.contains('Add Payment').click({ force: true })
        cy.get('#select_all').click({force: true});
        cy.get('#payment').clear({force:true}).type('247,674.26', {force:true})
        cy.get('[name="payment_date1"]').type('06-02-2021', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
        
    })

})

describe('Assertions in view page after Full payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true})     

    })
   
    it('Agent fee in view page', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(4) > div > div:nth-child(8) > div.value').should('have.text', '$27,519.36')       
    })
    it('Complete % in view page', () => {
        cy.get(':nth-child(3) > .merchant-details > :nth-child(7) > .value').should('have.text', '100%')       
    })
    
})

describe('Payment tab assertion after Full payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})


    })
    it('Assert for `Payments` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > .sorting_1').should((elem) => {
            expect(elem.text()).to.equal('$247,674.26')
        })
    })
    it('Assert for `To participant` in payment tab', () => {
        cy.get('.odd > :nth-child(5) > span').should((elem) => {
            expect(elem.text()).to.equal('$216,219.63')
        })
    })
    it('Assert for `Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$216,219.45')
        })
    })
    it('Assert for `Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > .odd > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.18')
        })
    })
    it('Assert for `Agent fee icon` in payment tab', () => {
        cy.get('.help-tip').click({multiple: true}) 
        cy.get('#dataTableBuilder > tbody > tr.odd > td:nth-child(5) > p').should((elem) => {
            expect(elem.text()).to.equal('Agent Fee : $24,767.43')
        })
        
    })
})

describe('Payment tab Total assertion after Full payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})


    })
    it('Assert for `Total Payments` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$275,193.60')
        })
    })
    it('Assert for `Total To participant` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$240,244.01')
        })
    })
    it('Assert for `Total Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(6)').should((elem) => {
            expect(elem.text().trim()).to.equal('$240,243.83')
        })
    })
    it('Assert for `Total Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tfoot > tr > :nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.18')
        })
    })

    
})

describe('Payment Expanding test in Payment tab after one payment', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')         
        cy.get('label > .form-control').type('agent fee',{force: true})
        cy.get('#dataTableBuilder').contains('a', 'AGENT FEE MER').click({force: true}) 
        cy.get(':nth-child(2) > .nav-link').click({force: true})
        cy.get('.odd > .details-control').click({force: true})
    })

    it('Assert for `Participant name` in payment tab', () => { 
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(2)').should((elem) => {
            expect(elem.text()).to.equal('FEE..')
        })
    })
    it('Assert for `Participant Share` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Management Fee` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `To Participant` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.43')
        })
    })
    it('Assert for `Overpayment` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(11) > td:nth-child(8)').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })
    it('Assert for `Total Participant Share` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(2)').should((elem) => {
            expect(elem.text()).to.equal('$247,674.26')
        })
    })
    it('Assert for `Total Management Fee` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$6,687.20')
        })
    })
    it('Assert for `Total ToParticipant` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$240,987.06')
        })
    })
    it('Assert for `Total Principal` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$216,219.45')
        })
    })
    it('Assert for `Total Profit` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$24,767.61')
        })
    })
    it('Assert for `Total Overpayment` in payment tab', () => {
        cy.get('#dataTableBuilder > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(13) > td:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$0.18')
        })
    })
    
})


