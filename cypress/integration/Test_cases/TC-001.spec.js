describe('TC-001', () => {
     before(() => {
        cy.exec('php artisan migrate:fresh --seed')
    })

    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    
    
    it('Create a new company', () => {
        cy.get('#cy_companies').click()
        cy.get('#cy_create_companies').click()
        cy.url().should('contains', '/admin/sub_admins/create')
        cy.get('#nameId').type('New Company')
        cy.get('[name="email"]').type('new@company.com')
        cy.get('#imageUpload').attachFile('/test-images/logo-1.jpeg')
        cy.get('#brokerageId').clear().type('10');
        cy.get('[name="password"]').type('lkjlkj');
        cy.get('[name="password_confirmation"]').type('lkjlkj');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    
})