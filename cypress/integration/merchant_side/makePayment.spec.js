describe('Make payment in merchant dashboard', () => {
    beforeEach(() => {
        cy.viewport(1920,700 )
        cy.visit('/merchants')
       // cy.login({ email: '501email@iocod.com' })

        cy.get('#login-page > div.form_sec.pr > div.form_container > input.email').type('501email@iocod.com')
        cy.get('#login-page > .form_sec > .form_container > .password').type('lkjlkj')
        cy.get('#login-page > div.form_sec.pr > div.form_container > button').click()
    

    })

    
     it('Validation check', () =>{
        cy.get('#appWrapper > section > div > header > div.collapse > div.nav-box > ul > li:nth-child(3) > a')
        .should('have.attr', 'target', '_blank')
        .invoke('removeAttr', 'target')
        .click()

        cy.get('#amount').dblclick().type('0000')
        cy.get('#amount_to_display').dblclick({ force: true })
        cy.get('#subthis').click().screenshot()

     })


     it('Make payment', () =>{
        cy.get('#appWrapper > section > div > header > div.collapse > div.nav-box > ul > li:nth-child(3) > a')
        .should('have.attr', 'target', '_blank')
        .invoke('removeAttr', 'target')
        .click()

        cy.get('#amount')
        cy.get('#name_on_card').type('card name')
        cy.get('#card-number').type('4242 4242 4242 4242')
        cy.get('#amount_to_display')
        cy.get('#date-exp').type('10/25')
        cy.get('#cvv').type('123')
        cy.get('#subthis').click()
        cy.url().should('include', '/payment/process-stripe-payment')
        cy.screenshot()
        
     })

     
})
