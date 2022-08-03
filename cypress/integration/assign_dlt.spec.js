describe('all assignment deletes', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('delete payment `IOCOD1`', () => {
        cy.visit('/admin/merchants/view/1')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })

    it('delete payment `IOCOD2`', () => {
        cy.visit('/admin/merchants/view/2')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD3`', () => {
        cy.visit('/admin/merchants/view/3')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD4`', () => {
        cy.visit('/admin/merchants/view/4')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD5`', () => {
        cy.visit('/admin/merchants/view/5')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD6`', () => {
        cy.visit('/admin/merchants/view/6')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
  
    })
    it('delete payment `IOCOD7`', () => {
        cy.visit('/admin/merchants/view/7')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD8`', () => {
        cy.visit('/admin/merchants/view/8')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD9`', () => {
        cy.visit('/admin/merchants/view/9')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
        
    })
    it('delete payment `IOCOD10`', () => {
        cy.visit('/admin/merchants/view/10')
       cy.get('#delete_investment').check()
        cy.get('#delete_multi_investment').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
        .should('match', / Success/i)
    })


    


        

})