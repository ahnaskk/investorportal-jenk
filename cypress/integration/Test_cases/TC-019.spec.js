describe('TC-019', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Check whether Merchant is able to add payment', function() {
        
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.url().should('contains', '/admin/merchants')
        cy.get(':nth-child(11) > [href="http://investorportal.test/admin/merchants/view/1"]').click()
        cy.get('a[href="http://investorportal.test/admin/payment/create/1"]').click({force: true})
        cy.get('#company').select('3', {force: true})
        cy.get('#user_id').select('6', {force:true})
        cy.get('#payment').should('have.value', '1500')
        cy.get('[name="payment_date1"]').type('08-01-2021', {force:true})
        cy.get('#paymentClick').click()
        cy.get('.alert > h4')
        .should(($suc) => {
        expect($suc.text().trim()).equal('Success');
        });


    })

})

