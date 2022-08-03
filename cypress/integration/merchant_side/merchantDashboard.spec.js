describe('Merchants Dashboard', () => {
    beforeEach(() => {
        
        cy.visit('/merchants')
       // cy.login({ email: '501email@iocod.com' })

        cy.get('#login-page > div.form_sec.pr > div.form_container > input.email').type('501email@iocod.com')
        cy.get('#login-page > .form_sec > .form_container > .password').type('lkjlkj')
        cy.get('#login-page > div.form_sec.pr > div.form_container > button').click()
    

    })

    

    it('Verify its a dashboard', () => {

        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > header > h1').contains('Dashboard')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > header > div > button:nth-child(1)').contains('Request payoff')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > header > div > button:nth-child(2)').contains('Request more money')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section:nth-child(2) > div > div > div > div.amounts > div > div > span > span').contains('Welcome')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section:nth-child(2) > div > div > div > div.amounts > ul > li > div > div > h3').contains('Funded')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section:nth-child(2) > div > div > div > div.investment-sum-card.grow > div > div > div > div:nth-child(1) > div > span.title').contains('Factor Rate')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section:nth-child(2) > div > div > div > div.investment-sum-card.grow > div > div > div > div:nth-child(2) > div > span.title').contains('Total Payments')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section:nth-child(2) > div > div > div > div.investment-sum-card.grow > div > div > div > div:nth-child(3) > div > span.title').contains('Actual Payments Left')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section.info-row.graph > div.col.left > div > div > div.title-wrapper > h3').contains('Graph')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section.info-row.graph > div.col.right > div > ul > li:nth-child(1) > div > div.text > div > h3').contains('Payment Amount')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section.info-row.graph > div.col.right > div > ul > li:nth-child(2) > div > div.text > div > h3').contains('Balance')
        cy.get('#appWrapper > section > div > section > div > section > div.col.overview > div > section.info-row.graph > div.col.right > div > ul > li:nth-child(3) > div > div.text > div > h3').contains('RTR')
        cy.get('#appWrapper > section > div > section > div > section > div.col.payments > div > section > div > div > h2').contains('Latest Payments')

            
    })
     it.only('Latest payments', ()=> {
        cy.get('.title').contains('Latest Payments')
        cy.get('#appWrapper > section > div > section > div > section > div.col.payments > div > section > div > div > a').contains('View All').click()
        cy.url().should('include', '/merchants/reports')
     })

     it('Transaction page test', () =>{
         cy.get('#appWrapper > section > div > header > div.collapse > div.nav-box > ul > li:nth-child(2) > a').contains('Transaction').click()
         cy.url().should('include', '/merchants/transactions')
         cy.visit('/merchants/transactions')
         cy.get('#transactionView > div.info.info-date > p').contains('Payment Date')
         cy.get('#transactionView > div.info.info-amount > p').contains('Payment Amount')
         cy.get('#transactionView > div.info.info-count > p').contains('Payment Balance')

     })

     it('FAQ page', () =>{
        cy.get('#appWrapper > section > div > header > div.collapse > div.nav-box > ul > li:nth-child(4) > a').click()
        cy.url().should('include', '/merchants/faq')
        
     })


    //  it('Make Payment', () =>{
    //     cy.get('#appWrapper > section > div > header > div.collapse > div.nav-box > ul > li:nth-child(3) > a')
    //     .contains('Make Payment')
    //     .should('have.attr', 'target', '_blank')
    //     .invoke('removeAttr', 'target')
    //     .click()
    // //test new payment page.
    //     .get('body > section > div > div > div.caption-left.col-md-6 > div > h1')
    //     .should('include', "Thank you for visiting Velocity Group USA's payment gateway.")            
        
    //  })

     
})
