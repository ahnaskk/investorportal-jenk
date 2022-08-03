/// <reference types="Cypress" />



describe('Tests for making merchant payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Creates new merchant `company payment mer`', () => {
        cy.visit('/admin/merchants/create')
        cy.get('[name="name"]').type('company payment mer')
        cy.get('[name="first_name"]').type('company payment mer')
        cy.get('[name="state_id"]').select('11', { force: true })
        cy.get('[name="industry_id"]').select('8', { force: true })
        cy.get('[name="merchant_email"]').type('comp_paymnt@gmail.com')
        cy.get('[name="date_funded1"]').type('03-14-2021')
        cy.get('[name="funded"]').clear().type('20000')
        cy.get('[name="factor_rate"]').type('1.5')
        cy.get('#max_participant_fund_per').type('100', { force: true })
        cy.get('[name="company_per[3]"]').clear().type('30')
        cy.get('#company_per_1').clear().type('40')
        cy.get('#company_per_2').clear().type('40')
        cy.get('#company_per_3').clear().type('20') 
        cy.get('[name="commission"]').type('10')
        cy.get('[name="pmnts"]').type('10')
        cy.get('[name="credit_score"]').type('500')
        // cy.get('[name="sub_status_flag"]').select('1', { force: true })
        cy.get('[name="sub_status_id"]').select('1', { force: true })
        cy.get('[name="advance_type"]').select('Weekly ACH', { force: true })
        cy.get('[name="marketplace_status"]').select('0', { force: true })
        cy.get('[name="source_id"]').select('1', { force: true })
        cy.get('[name="lender_id"]').select('LenderE', { force: true })
        // cy.get('[name="m_s_prepaid_status"]').check('2')
        // cy.get('[name="underwriting_fee"]').select('1.75', { force: true })
        // cy.get('[name="underwriting_status[]"]').check('1')
        // cy.get('[name="underwriting_status[]"]').check('2')
        cy.get('[name="ach_pull"]').check()
        cy.get('[name="account_holder_name"]').type('federal')
        cy.get('[name="routing_number"]').type('121122676')
        cy.get('[name="account_number"]').type('00012345678')
        cy.get('#bankAcoountModalSubmit').click()
        cy.get('#merchant_create_form').submit();
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Assign investor', () => {
    
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('COMPANY PAYMENT MER')
        cy.get('#dataTableBuilder').contains('td', 'COMPANY PAYMENT MER').siblings()
            .contains('a', 'View')
            .click({force: true})

//New Investor
           // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > section > div > div > div.col-sm-12 > div > div > div.merchant-btn-wrap > ul > li:nth-child(2) > a').click({force:true})
           cy.contains('New Investor').click({force: true})
           cy.get('#company').select('Syndicate', {force: true})
            cy.get('#gross_value').uncheck()
            cy.get('#amount_field').type('4000', {force: true})
            cy.get('#user_id').select('SYNDICATE1 - 40000', {force:true})
            cy.wait(5000)
            cy.get('#update_btn').click()


//Assign investors
            cy.get('#assign_button').click( { force:true } )
            cy.get('.box-body > .box-footer > .btn').click()
            cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4')
                .invoke('text').should('match', / Success/i)


    })

    it('Add 6 -all company- payments', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('COMPANY PAYMENT MER')
        cy.get('#dataTableBuilder').contains('td', 'COMPANY PAYMENT MER').siblings()
            .contains('a', 'View')
            .click({force: true})
    
    
        cy.contains('Add Payment').click({ force: true })
        cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        //cy.get('#datepicker1').type('03-14-2021','03-15-2021','03-16-2021','03-17-2021','03-18-2021','03-19-2021','03-20-2021',{force: true})
        cy.get('#datepicker1').click()
        cy.get('tr:nth-child(1) > .day:nth-child(4)').click()
        cy.get('tr:nth-child(1) > .day:nth-child(5)').click()
        cy.get('tr:nth-child(1) > .day:nth-child(6)').click()
        cy.get('tr:nth-child(1) > .day:nth-child(7)').click()
        cy.get('tr:nth-child(2) > .day:nth-child(1)').click()
        cy.get('tr:nth-child(2) > .day:nth-child(2)').click()
      //  cy.get('tr:nth-child(2) > .day:nth-child(3)').click() 
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
        
        //no of payments
        cy.get(':nth-child(2) > .merchant-details > :nth-child(3) > .value').should('have.text','10')
        //payments left
        cy.get(':nth-child(2) > .merchant-details > :nth-child(4) > .value').should('have.text', '4')
        //complete
        cy.get(':nth-child(3) > .merchant-details > :nth-child(7) > .value').should('have.text', '60%')
        //ctd our portion
        cy.get('#ctd').should('have.text', '$12,828.60')
        //vp completed %
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > div > div:nth-child(3)')
            .should('have.text','\n                                    Completed\n                                                                        59.96%\n                                ')
        //velocity completed%
        cy.get(':nth-child(2) > .form-group > .content-right > :nth-child(3)')
            .should('have.text','\n                                    Completed\n                                                                        60.03%\n                                ')
        //syndicate completed%
        cy.get(':nth-child(3) > .form-group > .content-right > :nth-child(3)')
            .should('have.text','\n                                    Completed\n                                                                        60.01%\n                                ')



        

    })

    it('syndicate company payments', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('COMPANY PAYMENT MER')
        cy.get('#dataTableBuilder').contains('td', 'COMPANY PAYMENT MER').siblings()
            .contains('a', 'View')
            .click({force: true})
    
        cy.contains('Add Payment').click({ force: true })
        cy.get('#company').select('Syndicate', {force: true})
        cy.get('#datepicker1').dblclick();
        cy.get('tr:nth-child(2) > .day:nth-child(5)').click();
     //   cy.get('tr:nth-child(2) > .day:nth-child(6)').click();
        cy.get('#paymentClick').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

      //no of payments
      cy.get(':nth-child(2) > .merchant-details > :nth-child(3) > .value').should('have.text','10')
      //payments left
      cy.get(':nth-child(2) > .merchant-details > :nth-child(4) > .value').should('have.text', '3')
      //complete
      cy.get(':nth-child(3) > .merchant-details > :nth-child(7) > .value').should('have.text', '70%')
      //ctd our portion
      cy.get('#ctd').should('have.text', '$14,966.70')
      //vp completed %
      cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > div > div:nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        59.96%\n                                ')
      //velocity completed%
      cy.get(':nth-child(2) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        60.03%\n                                ')
      //syndicate completed%
      cy.get(':nth-child(3) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        95.65%\n                                ')

     })

     it('VP company payments', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('COMPANY PAYMENT MER')
        cy.get('#dataTableBuilder').contains('td', 'COMPANY PAYMENT MER').siblings()
            .contains('a', 'View')
            .click({force: true})
    
        cy.contains('Add Payment').click({ force: true })
        cy.get('#company').select('VP Advance Funding', {force: true})
        cy.get('#datepicker1').dblclick();
        cy.get('tr:nth-child(2) > .day:nth-child(3)').click();
        cy.get('#paymentClick').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

      //no of payments
      cy.get(':nth-child(2) > .merchant-details > :nth-child(3) > .value').should('have.text','10')
      //payments left
      cy.get(':nth-child(2) > .merchant-details > :nth-child(4) > .value').should('have.text', '2')
      //complete
      cy.get(':nth-child(3) > .merchant-details > :nth-child(7) > .value').should('have.text', '80%')
      //ctd our portion
      cy.get('#ctd').should('have.text', '$17,104.80')
      //vp completed %
      cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > div > div:nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        90.58%\n                                ')
      //velocity completed%
      cy.get(':nth-child(2) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        60.03%\n                                ')
      //syndicate completed%
      cy.get(':nth-child(3) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        95.65%\n                                ')

     })


     it('Balance payments No:2', () => {
        cy.get('#dataTableBuilder_next').click({force: true})
        cy.get('label > .form-control').type('COMPANY PAYMENT MER')
        cy.get('#dataTableBuilder').contains('td', 'COMPANY PAYMENT MER').siblings()
            .contains('a', 'View')
            .click({force: true})
    
        cy.contains('Add Payment').click({ force: true })
        cy.get('#select_all').click();
        cy.get('#datepicker1').dblclick();
        cy.get('tr:nth-child(3) > .day:nth-child(2)').click();
        cy.get('tr:nth-child(3) > .day:nth-child(3)').click();
        cy.get('#paymentClick').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)

      //no of payments
      cy.get(':nth-child(2) > .merchant-details > :nth-child(3) > .value').should('have.text','10')
      //payments left
      cy.get(':nth-child(2) > .merchant-details > :nth-child(4) > .value').should('have.text', 'None')
      //complete
      cy.get(':nth-child(3) > .merchant-details > :nth-child(7) > .value').should('have.text', '100%')
      //ctd our portion
      cy.get('#ctd').should('have.text', '$21,381.00')
      //vp completed %
      cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.view-merchant-option > div > div:nth-child(1) > div > div > div:nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        100%\n                                ')
      //velocity completed%
      cy.get(':nth-child(2) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        100%\n                                ')
      //syndicate completed%
      cy.get(':nth-child(3) > .form-group > .content-right > :nth-child(3)')
          .should('have.text','\n                                    Completed\n                                                                        100%\n                                ')

     })









})


