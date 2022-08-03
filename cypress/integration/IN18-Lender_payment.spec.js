describe('Merchant create and assign', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('create new merchant', () =>{
        cy.contains('Add Merchant').click({force:true})
        cy.get('[name="name"]').type('lender_payment_mer')
        cy.get('[name="first_name"]').type('iocod1')
        cy.get('[name="state_id"]').select('3', { force: true })
        cy.get('[name="industry_id"]').select('33', { force: true })
        cy.get('[name="merchant_email"]').type('lender_payment_mer@gmail.com')
        cy.get('[name="date_funded1"]').type('03-01-2022')
        cy.get('[name="funded"]').clear().type('20000')
        cy.get('[name="max_participant_fund_per"]').type('100', { force: true })
        cy.get('#company_max_1').clear({force: true}).type('10000', {force: true})
        cy.get('#company_max_2').clear({force: true}).type('10000', {force: true})
        cy.get('[name="factor_rate"]').type('1.5')
        cy.get('[name="commission"]').type('10')
        cy.get('[name="pmnts"]').type('10')
        cy.get('[name="credit_score"]').type('500')
        // cy.get('[name="sub_status_flag"]').select('1', { force: true })
        cy.get('[name="sub_status_id"]').select('1', { force: true })
        cy.get('[name="advance_type"]').select('Daily ACH', { force: true })
        cy.get('[name="marketplace_status"]').select('0', { force: true })
        cy.get('[name="source_id"]').select('1', { force: true })
        cy.get('[name="lender_id"]').select('LenderA', { force: true })
        cy.get('[name="m_s_prepaid_status"]').check('2')
        cy.get('[name="underwriting_fee"]').select('1.75', { force: true })
        cy.get('[name="underwriting_status[]"]').check('1')
        cy.get('[name="underwriting_status[]"]').check('2')
        cy.get('[name="ach_pull"]').check()
        cy.get('[name="account_holder_name"]').type('federal')
        cy.get('[name="routing_number"]').type('082000549')
        cy.get('[name="account_number"]').type('0001234005678')
        cy.get('#bankAcoountModalSubmit').click()
        cy.get('#merchant_create_form').submit();
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })
    it('Assigning INVESTOR VP', ()=> {
        cy.get('#dataTableBuilder_filter > label > input').type(' lender_payment_mer', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'LENDER_PAYMENT_MER') .click({ force:true })
        cy.get('[data-cy="cy_assign_new_inv"]').click({force: true})
        cy.get('#company').select('VP Advance Funding', {force:true})
        cy.get('#user_id').select('791',{force:true})
        cy.get('#input_amount_field').type('10000' ,{force:true})
        cy.get('#add_btn').click({force:true})  
        cy.wait(4000)
        cy.get('#save_btn').click()
        cy.get('#investorCreateForm > div > div.box-head > div').invoke('text')
            .should('match', / Success/i)
    })
    it('Assigning INVESTOR VELOCITY', ()=> {
        cy.get('#dataTableBuilder_filter > label > input').type(' lender_payment_mer', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'LENDER_PAYMENT_MER') .click({ force:true })
        cy.get('[data-cy="cy_assign_new_inv"]').click({force: true})
        cy.get('#company').select('Velocity', {force:true})
        cy.get('#user_id').select('793',{force: true})
        cy.get('#input_amount_field').type('10000' ,{force:true})
        cy.get('#add_btn').click({force:true})  
        cy.wait(4000)
        cy.get('#save_btn').click()
        cy.get('#investorCreateForm > div > div.box-head > div').invoke('text')
            .should('match', / Success/i)
    })
})

describe('`Lender Payment Generation`', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/payment/lender_payment_generation')
    })
    it('Make lender payment for `IOCOD4` merchant', () => {
        cy.get('#lenders').select('LenderA', { force: true })
        cy.get('#datepicker11').type('03-27-2022,03-28-2022,03-29-2022,03-30-2022,03-31-2022', { force: true })
        cy.get('#company').select('All', { force: true })
        cy.get('#filter_form > div.col-md-12.btn-wrap.btn-right > div > input').click({force:true})
        // cy.get('#select_all').check({force:true})
        cy.get('[id="interst_div[]"]> .form-row-checkbox > .form-group > .select_merchant').last().check({force:true});
        cy.get('#paymentClick').click()
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-body.alert-box-body > div > h4').invoke('text').should('match', / Success/i)
    })
})

describe('Assertion after payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type('LENDER_PAYMENT_MER', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'LENDER_PAYMENT_MER') .click({ force:true }) 
    })
    
    it('total payments', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(3) > div.value').should((elem) => {
            expect(elem.text()).to.equal('10')
        })
    })
    it('Payments left', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(4) > div.value').should((elem) => {
            expect(elem.text()).to.equal('5')
        })
    })
    it('Complete', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(7) > div.value').should((elem) => {
            expect(elem.text()).to.equal('50%')
        })
    })
    it('CTD', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal('$15,000.00 ')
        })
    })
    it('Participant balance', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(4) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal(' $15,000.00 ')
        })
    })   
})

describe('Assertion payment tab', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type('LENDER_PAYMENT_MER', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'LENDER_PAYMENT_MER') .click({ force:true }) 
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a').click({force:true})
    })
    
    it('Total payment', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(4)').should((elem) => {
            expect(elem.text()).to.equal('$15,000.00')
        })
    })
    it('Total To participant', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$15,000.00')
        })
    })
    it('Total principal', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((elem) => {
            expect(elem.text()).to.equal('$11,175.00')
        })
    })
    it('Total Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$3,825.00')
        })
    })
    
})
