describe('Tests for Create companies', () => {
    before(() => {
        cy.exec('php artisan migrate:fresh --seed')
        cy.exec('php artisan db:seed --class=CommonSeeder')
    })
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/sub_admins/create')
    })
    it('Verify if `create companies` page accessable', () => {
        cy.get('.wrapper.demo > div.content-wrapper > section.content > div.inner.admin-dsh.header-tp > h3').contains('Create Companies')
    })
    it('Test if `required` validation works', () => {
        cy.get('#create_subadmin > div > div.btn-wrap.btn-right > div > input.bran-mng-bt').click()
        cy.get('#nameId-error').should('have.text', 'Enter Name')
        cy.get('#email-error').should('have.text', 'Enter Email Id')
        cy.get('#imageUpload-error').should('have.text', 'Upload Logo')
        cy.get('#inputPassword-error').should('have.text', 'Enter Password')
        cy.get('#password_confirmation-error').should('have.text', 'You must confirm your password.')
    })
    it('Create company `VP Advance funding`', () => {
        cy.get('#nameId').type('VP Advance Funding')
        cy.get('[name="email"]').type('pactolus@vgusa.com')
        cy.get('#imageUpload').attachFile('/test-images/logo-1.jpeg')
        cy.get('#brokerageId').clear().type('15');
        cy.get('[name="password"]').type('123123');
        cy.get('[name="password_confirmation"]').type('123123');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create company `Velocity`', () => {
        cy.get('#nameId').type('Velocity')
        cy.get('[name="email"]').type('abcd@gmail.com')
        cy.get('#imageUpload').attachFile('/test-images/logo-1.jpeg')
        cy.get('#brokerageId').clear().type('3');
        cy.get('[name="password"]').type('123123');
        cy.get('[name="password_confirmation"]').type('123123');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })
    it('Create company `Syndicate`', () => {
        cy.get('#nameId').type('Syndicate')
        cy.get('[name="email"]').type('syndicate@gmail.com')
        cy.get('#imageUpload').attachFile('/test-images/logo-1.jpeg')
        cy.get('#brokerageId').clear().type('100');
        cy.get('[name="password"]').type('123123');
        cy.get('[name="password_confirmation"]').type('123123');
        cy.get('input[type="submit"]').click();
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4').should('have.text', ' Success')
    })


// it('Delete insurance3 from seeder', () => {
//           cy.visit('/admin/label')
//           cy.get('#status > tbody > tr > td:nth-child(3) > form > input.sub-bt.btn.btn-xs.btn-danger').click({force: true})
//         //   cy.get('#delete_multi_submit').click()
//         //   cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div > h4 > i').invoke('text').should('match', Success)

// })

})

describe('Tests for View Companies', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/sub_admins')
    })
    it('Verifies table has 3 companies', () => {
        cy.get('#dataTableBuilder').find('tr')
            .then((row) => {
                expect(row.length).to.eq(2);
            })
    })
})