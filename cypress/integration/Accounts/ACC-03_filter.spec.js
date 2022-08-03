describe('Verify filters are working in account page', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Tests if `Investor type` filter works', () => {
        cy.get('#investor_type').select('1', { force: true })
        cy.get('#date_filter').click()
        cy.get('#investor').contains('td', 'InvestorVP-1').should('be.visible');
        cy.get('#investor').contains('td', 'InvestorVel-3').should('be.visible');
    })

    it('Tests if `Companies` filter works', () => {
        cy.get('#velocity').select('3', { force: true })
        cy.get('#date_filter').click()
        cy.get('#investor').contains('td', 'InvestorVP-1').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-2').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-3').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-4').should('be.visible')
    })

    it('Tests if `Account type` filter works', () => {
        cy.get('#role_id').select('13', { force: true })
        cy.get('#date_filter').click()
        cy.get('#investor').contains('td', 'Overpayment').should('be.visible')
    })

    it('Tests if `Enable/Disable Investors` filter works', () => {
        cy.get('#active_status_disable').check()
        cy.get('#date_filter').click()
        cy.get('#investor > td').should('not.exist')
    })

    it('Tests if `Auto Invest Label` filter works', () => {
        cy.get('#auto_invest_label').select('3', { force: true })
        cy.get('#date_filter').click()
        cy.get('#investor').contains('td', 'Syndicate3').should('be.visible')
        cy.get('#investor').contains('td', 'Syndicate1').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-3').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-1').should('be.visible')
    })

    it('Tests if `Enable/Disable Automatic Report Generation` filter works', () => {
        cy.get('#label_enable').check()
        cy.get('#date_filter').click()
        cy.get('#investor > td').should('not.exist')
    })

    it('Tests if `Payout Frequency` filter works', () => {
        cy.get('#notification_recurence').select('1', { force: true })
        cy.get('#date_filter').click()
        cy.get('#investor').contains('td', 'Syndicate3').should('be.visible')
        cy.get('#investor').contains('td', 'Syndicate1').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVel-3').should('be.visible')
        cy.get('#investor').contains('td', 'InvestorVP-1').should('be.visible')
    })


})