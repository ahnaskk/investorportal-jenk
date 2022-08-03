describe('TC-004', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('New Merchant', function() {
        cy.get('#cy_merchants').click()
        cy.get('#cy_create_merchants').click()
        cy.url().should('contains', '/admin/merchants/create')
        cy.get('#merchantNameId').type('merchant1');
        //Business information
        cy.get('#first_name').type('mer');
        cy.get('#last_name').type('chant');
        cy.get('#state_id').select('1', { force: true })
        cy.get('#industry_id').select('Alcoholic Beverages', { force:true })
        cy.get('#email').type('merchant1@gmail.com',{ force: true });
        cy.get('#inputPassword').type('lkjlkj');
        cy.get('#inputConfirmPassword').type('lkjlkj')
        //Payment information
        cy.get('#funded').type('10000')
        cy.get('#factorRate').type('1.5');
        cy.get('#datepicker1').type('07-01-2021');//date selector
        cy.get('#max_participant_fund_per').type('100');
        cy.get('#max_participant_fund').click();
        cy.get('#company_per_1').type('100')
        cy.get('.col-md-4:nth-child(9) .input-group > .form-control').type('400');
        cy.get('.col-md-4:nth-child(10) .input-group > .form-control').type('10');
        cy.get('.form-box-styled:nth-child(2) .col-md-4:nth-child(11) .form-control').type('10');
        cy.get('#marketplace').select('Yes', {force: true})
        cy.get('#lender_id').select('lender one', {force: true})
        cy.get('#merchant_create').click()
        
    })
})

