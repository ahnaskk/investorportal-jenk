describe('Account menu test', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    it('Verifies 5 links are inside account menu', () => {
        cy.get('#cy_accounts').click()
        cy.get('div.wrapper.demo > aside > section > ul > li.treeview.menu-open > ul > li').should(($lis) => {
            // cy.log($lis)
            expect($lis).to.have.length(5)
            expect($lis.eq(0)).to.contain('All Accounts')
            expect($lis.eq(1)).to.contain('Create Account')
            expect($lis.eq(2)).to.contain('Generate PDF For Investors')
            expect($lis.eq(3)).to.contain('Generated PDF/CSV')
            expect($lis.eq(4)).to.contain('FAQ')
        })
    })
})