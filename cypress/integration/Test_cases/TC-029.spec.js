
describe('TC-029', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })

    it('Verify that merchant status became advanced completed', function() {
        
        cy.get('#dataTableBuilder').contains('td', 'merchant1').siblings()
            .contains('a', 'View')
            .click()

        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-02-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
      });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-03-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
      .should(($suc) => {
      expect($suc.text().trim()).equal('Success');
    });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-04-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });



        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-05-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-06-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });



        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-07-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-08-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-09-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });


        cy.get('a[href*="http://investorportal.test/admin/payment/create/1"]').click( {force: true })
        cy.get('#user_id').select('6', {force : true})
        cy.get('#datepicker1').type('08-10-2021',  {force: true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });

        cy.get(':nth-child(3) > .merchant-details > :nth-child(7)').should('contain', 'Complete %', '100%')




        

       // cy.get('[name="payment_date1"]').type('08-09-2021', '08-10-2021', '08-11-2021', '08-12-2021', '08-13-2021', '08-14-2021', '08-15-2021', '08-16-2021', '08-17-2021', {force: true})

    //     cy.get('#paymentClick').click()
    //   //  cy.get('.box-body > .alert').should.trim(('have.text', 'Success'))
    //   cy.get('.alert > h4')
    //         .should(($suc) => {
    //         expect($suc.text().trim()).equal('Success');
    //       });
        
        //   cy.get('.box-body > .alert')         .should(($count) => {
        //   expect($count.text().trim()).equal('9 payments added successfully');
        // });




    })

})




