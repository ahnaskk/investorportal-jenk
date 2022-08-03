describe('Portfolio page test', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
    })
    it('Verifies if `LIQUIDITY` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(1) > div')
            .find('.g_title')
            .should('have.text', 'Liquidity ')
    })
    it('Verifies if `OVERPAYMENT` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(2) > div')
            .find('.g_title')
            .should('have.text', 'Overpayment')
    })
    it('Verifies if `TOTAL INVESTED` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(3) > div')
            .find('.g_title')
            .should('have.text', 'Total Invested')
    })
    it('Verifies if `NUMBER OF MERCHANTS` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(5) > div')
            .find('.g_title')
            .should('have.text', 'Number Of Merchants')
    })

    it('Verifies if `BLENDED ROI` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(6) > div')
            .find('.g_title')
            .should('have.text', 'Blended Roi')
    })

    it('Verifies if `ROI` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(7) > div')
            .find('.g_title')
            .should('have.text', 'ROI')
    })

    it('Verifies if `DEFAULT RATE` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(8) > div')
            .find('.g_title')
            .should('have.text', 'Default Rate')
    })

    it('Verifies if `CASH TO DATE (CTD)` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(9) > div')
            .find('.g_title')
            .should('have.text', 'Cash to Date (CTD)')
    })

    it('Verifies if `TOTAL RTR` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(10) > div')
            .find('.g_title')
            .should('have.text', 'Total RTR')
    })

    it('Verifies if `PROJECTED PORTFOLIO VALUE` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(11) > div')
            .find('.g_title')
            .should('have.text', 'Projected Portfolio Value')
    })

    it('Verifies if `PRINCIPAL INVESTMENT` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(12) > div')
            .find('.g_title')
            .should('have.text', 'Principal Investment ')
    })

    it('Verifies if `CURRENT INVESTED` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(13) > div')
            .find('.g_title')
            .should('have.text', 'Current Invested')
    })

    it('Verifies if `AVERAGE DAILY BALANCE` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(14) > div')
            .find('.g_title')
            .should('have.text', 'Average Daily Balance')
    })

    it('Verifies if `PROFIT` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(15) > div')
            .find('.g_title')
            .should('have.text', 'Profit')
    })

    it('Verifies if `PAID TO DATE` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(16) > div')
            .find('.g_title')
            .should('have.text', 'Paid To Date')
    })
    it('Verifies if `ANTICIPATED RTR` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(17) > div')
            .find('.g_title')
            .should('have.text', 'Anticipated RTR')
    })
    it('Verifies if `PENDING TO VELOCITY` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(18) > div')
            .find('.g_title')
            .should('have.text', 'Pending To Velocity')
    })

    it('Verifies if `PENDING TO USER BANK` presents in portfolio page', () => {
        cy.get('#investor').contains('td', 'Syndicate1').siblings()
            .contains('a', 'Portfolio')
            .click()
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(19) > div')
            .find('.g_title')
            .should('have.text', 'Pending To User Bank')
    })
})