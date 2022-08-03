describe('Merchant create and assign', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('create new merchant', () =>{
        cy.contains('Add Merchant').click({force:true})
        cy.get('[name="name"]').type('credit_card_mer')
        cy.get('[name="first_name"]').type('iocod1')
        cy.get('[name="state_id"]').select('3', { force: true })
        cy.get('[name="industry_id"]').select('33', { force: true })
        cy.get('[name="merchant_email"]').type('credit_card_mer@gmail.com')
        cy.get('[name="date_funded1"]').type('03-01-2022')
        cy.get('[name="funded"]').clear().type('10000')
        cy.get('[name="max_participant_fund_per"]').type('100', { force: true })
        cy.get('#company_max_1').clear({force: true}).type('5000', {force: true})
        cy.get('#company_max_2').clear({force: true}).type('5000', {force: true})
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
        // cy.get('[name="underwriting_fee"]').select('1.75', { force: true })
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

    it('Assigning investors', () => {
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })   
})




describe('Credit payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true }) 
        cy.contains(' Credit Card').click({ force: true })  
    })
    it('Make CreditCardPayment 1 for `CREDIT_CARD_MER` merchant', () => {   
        cy.get('#name_on_card').type('card name')
        cy.get('#card-number').type('4242 4242 4242 4242')
        cy.get('#date-exp').type('10/25')
        cy.get('#cvv').type('123')
        cy.get('#subthis').click({force:true})
        cy.wait(3000)
       
    })

    it('Make CreditCardPayment 2 for `CREDIT_CARD_MER` merchant', () => {   
       cy.get('#name_on_card').type('card name')
       cy.get('#card-number').type('4242 4242 4242 4242')
       cy.get('#date-exp').type('10/25')
       cy.get('#cvv').type('123')
       cy.get('#subthis').click({force:true})
       cy.wait(3000)

   })
    it('Make CreditCardPayment 3 for `CREDIT_CARD_MER` merchant', () => {
        cy.get('#name_on_card').type('card name')
        cy.get('#card-number').type('4242 4242 4242 4242')
        cy.get('#date-exp').type('10/25')
        cy.get('#cvv').type('123')
        cy.get('#subthis').click({force:true})  
        cy.wait(3000)

    })  
})

describe('Updating transaction after 3 payments', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/payment/PendingTransactions')
    })
    
    it('Pending transactions updating', () => {
    cy.get('#from_date1').clear({force:true})
    cy.get('#to_date1').clear({force:true})
    cy.get('#checkAllButtont').check({force:true})
    cy.get('#updateButton').click({force:true})
    cy.contains('Yes, Send it!').click({force:true})
    cy.contains('OK').click({force:true})
    cy.contains('OK').click({force:true})

    })

     
})

describe('Assertion after 3 payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true }) 
    })
    
    it('total payments', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(3) > div.value').should((elem) => {
            expect(elem.text()).to.equal('10')
        })
    })
    it('Payments left', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(4) > div.value').should((elem) => {
            expect(elem.text()).to.equal('7')
        })
    })
    it('Complete', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(7) > div.value').should((elem) => {
            expect(elem.text()).to.equal('30%')
        })
    })
    it('CTD', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal('$4,500.00 ')
        })
    })
    it('Participant balance', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(4) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal(' $10,500.00 ')
        })
    })   
})

describe('100% payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true }) 
        cy.contains(' Credit Card').click({ force: true })  
    })
    it('Make 100% CreditCardPayment', () => {
        cy.get('#amount').clear({force:true}).type('10500', {force:true})
        cy.get('#name_on_card').type('card name')
        cy.get('#card-number').type('4242 4242 4242 4242')
        cy.get('#date-exp').type('10/25')
        cy.get('#cvv').type('123')
        cy.get('#subthis').click({force:true})
        cy.wait(3000)    
    })
})

describe('Pending transactions updating after 100%', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/payment/PendingTransactions')
    })
    
    it('Pending transactions updating', () => {
    cy.get('#from_date1').clear({force:true})
    cy.get('#to_date1').clear({force:true})
    cy.get('#checkAllButtont').check({force:true})
    cy.get('#updateButton').click({force:true})
    cy.contains('Yes, Send it!').click({force:true})
    cy.contains('OK').click({force:true})
    cy.contains('OK').click({force:true})

    })

     
})


describe('Assertion after 100% payments', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true }) 
    })
    
    it('total payments', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(3) > div.value').should((elem) => {
            expect(elem.text()).to.equal('10')
        })
    })
    it('Payments left', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(2) > div > div:nth-child(4) > div.value > font').should((elem) => {
            expect(elem.text()).to.equal('None')
        })
    })
    it('Complete', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(7) > div.value').should((elem) => {
            expect(elem.text()).to.equal('100%')
        })
    })
    it('CTD', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(3) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal('$15,000.00 ')
        })
    })
    it('Participant balance', () => {
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-md-12.mrchntVwDetails > div > div:nth-child(4) > div > div:nth-child(2) > div.value > span').should((elem) => {
            expect(elem.text()).to.equal(' $0.00 ')
        })
    })   
})

describe('Assertion after 100% payments in payment tab', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' CREDIT_CARd', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'CREDIT_CARD_MER') .click({ force:true }) 
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
            expect(elem.text()).to.equal('$11,000.00')
        })
    })
    it('Total Profit', () => {
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(7)').should((elem) => {
            expect(elem.text()).to.equal('$4,000.00')
        })
    })
    
})