describe('Tests for merchant over payment', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('delete payment `IOCOD1`', () => {
        cy.visit('/admin/merchants/view/1')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })

    it('delete payment `IOCOD2`', () => {
        cy.visit('/admin/merchants/view/2')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD3`', () => {
        cy.visit('/admin/merchants/view/3')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD4`', () => {
        cy.visit('/admin/merchants/view/4')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD5`', () => {
        cy.visit('/admin/merchants/view/5')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD6`', () => {
        cy.visit('/admin/merchants/view/6')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
  
    })
    it('delete payment `IOCOD7`', () => {
        cy.visit('/admin/merchants/view/7')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD8`', () => {
        cy.visit('/admin/merchants/view/8')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD9`', () => {
        cy.visit('/admin/merchants/view/9')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })
    it('delete payment `IOCOD10`', () => {
        cy.visit('/admin/merchants/view/10')
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.card.merchant-table-nav.with-nav-tabs.card-default > div.card-header > ul > li:nth-child(2) > a')
            .contains('Payments').click() 
            cy.get('#delete_payment').check()
            cy.get('#delete_multi_submit').click()
            cy.on('window:confirm(true)', (str) =>{
                expect(str).to.equal('Do you really want to delete selected items?')
            })
        
    })


    


        

})