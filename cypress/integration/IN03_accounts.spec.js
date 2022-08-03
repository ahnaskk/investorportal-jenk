describe('Tests for Accounts module', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors/create')
    })
    it('Verify if `create account` page accessable', () => {
        cy.get('.wrapper.demo > .content-wrapper > section.content > .inner.admin-dsh.header-tp > h3').contains('Create New Accounts ')
    })
    it('Create `Overpayment` account', () => {
        cy.get('#inputName').type('Overpayment')
        cy.get('#investorContactPerson').type('contact op')
        cy.get('#investorCellPhone').type('(195) 150-2329')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('13', { force: true })
        cy.get('#inputInvestorType').select('6', { force: true })
        cy.get('#inputEmail').type('overpayment@yahoo.com')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('4', { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();

    })
    it('Create `Fee` account', () => {
        cy.get('#inputName').type('Fee')
        cy.get('#investorContactPerson').type('fee contact')
        cy.get('#investorCellPhone').type('(123) 456-1234')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('15', { force: true })
        cy.get('#inputInvestorType').select('6', { force: true })
        cy.get('#inputEmail').type('fee@yahoo.com')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('4', { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVP-1` account', () => {
        cy.get('#inputName').type('InvestorVP-1')
        cy.get('#investorContactPerson').type('investor1 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('1', { force: true })
        // cy.get('#inputInterestRate').select('10.00', { force: true })
        cy.get('#inputEmail').type('investor1@yahoo.com')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('785', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('1', { force: true })
        cy.get('#whole_portfolio').check({ force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVP-2` account', () => {
        cy.get('#inputName').type('InvestorVP-2')
        cy.get('#investorContactPerson').type('investor2 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('3', { force: true })
        // cy.get('#inputInterestRate').select('15.00', { force: true })
        cy.get('#inputEmail').type('investor24@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('785', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('3', { force: true })
        cy.get('#whole_portfolio').check({ force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })


    it('Create `InvestorVel-1` account', () => {
        cy.get('#inputName').type('InvestorVel-1')
        cy.get('#investorContactPerson').type('investorvel1 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('2', { force: true })
        cy.get('#inputEmail').type('investorvel1@gmail.com')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('786', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('2', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVel-2` account', () => {
        cy.get('#inputName').type('InvestorVel-2')
        cy.get('#investorContactPerson').type('investorvel2 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('4', { force: true })
        // cy.get('#inputInterestRate').select('13.00', { force: true })
        cy.get('#inputEmail').type('investor49@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('786', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('4', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVel-3` account', () => {
        cy.get('#inputName').type('InvestorVel-3')
        cy.get('#investorContactPerson').type('investorvel3 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('1', { force: true })
        // cy.get('#inputInterestRate').select('11.00', { force: true })
        cy.get('#inputEmail').type('investor5@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('786', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('1', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVel-4` account', () => {
        cy.get('#inputName').type('InvestorVel-4')
        cy.get('#investorContactPerson').type('investorvel4 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('3', { force: true })
        // cy.get('#inputInterestRate').select('15.00', { force: true })
        cy.get('#inputEmail').type('investor6@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('786', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('2', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVP-3` account', () => {
        cy.get('#inputName').type('InvestorVP-3')
        cy.get('#investorContactPerson').type('investorvp3 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('2', { force: true })
        cy.get('#inputEmail').type('investor7@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('785', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('3', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `InvestorVP-4` account', () => {
        cy.get('#inputName').type('InvestorVP-4')
        cy.get('#investorContactPerson').type('investorvp4 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('0.00', { force: true })
        cy.get('#inputManagementFee').select('0.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('3', { force: true })
        // cy.get('#inputInterestRate').select('15.00', { force: true })
        cy.get('#inputEmail').type('investor8@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('785', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('4', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `Syndicate1` account', () => {
        cy.get('#inputName').type('Syndicate1')
        cy.get('#investorContactPerson').type('syndicate1 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('2.00', { force: true })
        cy.get('#inputManagementFee').select('3.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputEmail').type('investor9@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('1', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Luthersales', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `Syndicate2` account', () => {
        cy.get('#inputName').type('Syndicate2')
        cy.get('#investorContactPerson').type('syndicate2 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('2.00', { force: true })
        cy.get('#inputManagementFee').select('2.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputEmail').type('investor10@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('3', { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `Syndicate3` account', () => {
        cy.get('#inputName').type('Syndicate3')
        cy.get('#investorContactPerson').type('syndicate3 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('2.00', { force: true })
        cy.get('#inputManagementFee').select('2.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputEmail').type('investor11@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('1', { force: true })
        cy.get('#inputlabel').select(['MCA (Default)', 'Insurance'], { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })

    it('Create `Syndicate4` account', () => {
        cy.get('#inputName').type('Syndicate4')
        cy.get('#investorContactPerson').type('syndicate4 contact')
        cy.get('#investorCellPhone').type('(195) 375-3895')
        cy.get('#inputGlobalSyndication').select('2.50', { force: true })
        cy.get('#inputManagementFee').select('3.00', { force: true })
        cy.get('#inputRoleId').select('2', { force: true })
        cy.get('#inputInvestorType').select('5', { force: true })
        cy.get('#inputEmail').type('investor12@yahoo.in')
        cy.get('#inputPassword').type('123123')
        cy.get('#inputConfirmPassword').type('123123')
        cy.get('#company').select('787', { force: true })
        // cy.get('#notification_email').type('investor@notify.com', { force: true })
        cy.get('#notification_recurence').select('4', { force: true })
        cy.get('.btn-default .toggle-handle').click({ force: true });
        cy.get('#create_investor_form').submit();
    })



})