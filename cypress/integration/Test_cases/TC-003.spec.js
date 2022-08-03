describe('TC-003', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('New Lender', function() {
        cy.get('#cy_lender').click()
        cy.get('#cy_create_lenders').click()
        cy.url().should('contains', '/admin/lender/create')
        cy.get('.row:nth-child(1) > .form-group:nth-child(3) > .form-control').click();
        cy.get('#lenderNameId').type('lender one'); 
        cy.get('.row:nth-child(1) > .form-group:nth-child(3) > .form-control').type('lender1@iocod.com');
        cy.get('#password').type('lkjlkj');
        cy.get('.row:nth-child(2) > .form-group:nth-child(1) > .form-control').type('lkjlkj');
        cy.get('[name="management_fee"]').select('1.00', { force: true })
        cy.get('[name="global_syndication"]').select('1.00', { force: true })
        cy.get('[type="radio"]').check() 
        cy.get('#underwriting_fee').select('1.00', { force: true })
        cy.get('.input-group > .mrch > .input-group-text > label > #m_underwriting_status_velocity').check('1')
        cy.get('[value="Create"]').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div > div.box-body.alert-box-body > div').invoke('text').should('match', /New Lender Created/i)
  })
})