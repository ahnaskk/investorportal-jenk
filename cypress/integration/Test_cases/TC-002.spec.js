describe('TC-002', function () {

    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    it('investor create -Agent fee', function () {
        cy.get('#cy_accounts').click()
        cy.get('#cy_create_account').click()
        cy.url().should('contains', '/admin/investors/create')
        cy.visit('/admin/investors/create')
        cy.get('#inputName').type('agentfee');
        cy.get('#investorContactPerson').type('abc');
        cy.get('#investorCellPhone').type('(485) 124-5334');
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('Agent Fee Account', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputEmail').type('agentfee@gmail.com')
        cy.get('#inputPassword').type('lkjlkj')
        cy.get('#inputConfirmPassword').type('lkjlkj')
        cy.get('#company').select('New Company', { force: true })
    //      cy.get('#notification_email').type('investor1@gmail.com', {force: true})
        cy.get('#create_investor_form > div > div:nth-child(4) > div:nth-child(9) > div > div > label.btn.btn-default.active.toggle-off').click({ force: true })
        cy.get('#create_investor_form > div > div:nth-child(5) > div > div > input').click()
    
    
    })
    
      it('investor create -Overpayment', function () {
        cy.get('#cy_accounts').click()
        cy.get('#cy_create_account').click()
        cy.url().should('contains', '/admin/investors/create')
        cy.visit('/admin/investors/create')
        cy.get('#inputName').type('overpayment_ac');
        cy.get('#investorContactPerson').type('abc');
        cy.get('#investorCellPhone').type('(485) 124-6378');
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('Over Payment', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputInterestRate').select('0', {force: true})
        cy.get('#inputEmail').type('overpayment_ac@gmail.com')
        cy.get('#inputPassword').type('lkjlkj')
        cy.get('#inputConfirmPassword').type('lkjlkj')
        cy.get('#company').select('New Company', { force: true })
    //      cy.get('#notification_email').type('investor1@gmail.com', {force: true})
        cy.get('#create_investor_form > div > div:nth-child(4) > div:nth-child(9) > div > div > label.btn.btn-default.active.toggle-off').click({ force: true })
        cy.get('#create_investor_form > div > div:nth-child(5) > div > div > input').click()
    
    
    })
    
        it('investor create -Investor1', function () {
            cy.get('#cy_accounts').click()
            cy.get('#cy_create_account').click()
            cy.url().should('contains', '/admin/investors/create')
            cy.get('#inputName').type('investor1');
            cy.get('#investorContactPerson').type('abc');
            cy.get('#investorCellPhone').type('(485) 124-2378');
            
            // cy.get('#inputGlobalSyndication').find(':selected').contains("Lender's fee")
            // cy.get('#inputManagementFee').find(':selected').contains("Lender's management fee")

           cy.get('#inputGlobalSyndication').select('0.00', { force: true })
           cy.get('#inputManagementFee').select('0.00', { force: true })

            cy.get('#inputRoleId').select('investor', { force: true })
            cy.get('#inputInvestorType').select('1', { force: true })//debt65/20/15
            cy.get('#inputInterestRate').select('0', {force: true})
            cy.get('#inputEmail').type('investor1@gmail.com')
            cy.get('#inputPassword').type('lkjlkj')
            cy.get('#inputConfirmPassword').type('lkjlkj')
            cy.get('#company').select('New Company', { force: true })
        //      cy.get('#notification_email').type('investor1@gmail.com', {force: true})
            cy.get('#create_investor_form > div > div:nth-child(4) > div:nth-child(9) > div > div > label.btn.btn-default.active.toggle-off').click({ force: true })
            cy.get('#create_investor_form').submit();
            cy.screenshot()
    
    
        })

})