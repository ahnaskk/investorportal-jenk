describe('TC-006', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Investors Liquidity updated or not', function() {
        cy.get('#cy_accounts').click()
        cy.get('#cy_all_accounts').click()
        cy.url().should('contains', '/admin/investors')
        cy.get('#investor').contains('a', 'Portfolio').first().click()
        cy.url().should('contains', '/admin/investors/portfolio/')
        //Investor transaction should be 50000.
        //Liquidity 
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(1) > div > div > span.info-box-number.g_value').should('have.text','$50,000.00') 
        //Principal investment
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(12) > div > div > span.info-box-number.g_value').should('have.text','$50,000.00') 
        //Projected portfolio value
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(11) > div > div > span.info-box-number.g_value').should('have.text','$50,000.00')
        cy.screenshot()
        
    })
})
