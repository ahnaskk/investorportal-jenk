describe('Create and Delete Bank ac in Investor side', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/investors')
        cy.get('#investor').contains('a', 'B').first().click({ force:true })

    })
    it('Create Bank ac in Investor side', () => {
        cy.contains('Create Bank Account ').click({force:true})
        cy.get('#accountHolderName').type('priya',{force:true})
        cy.get('#accountNumber').type('1234567890')
        cy.get('#routingNumber').type('091000022')
        cy.get('[name="bank_address"]').type('Bank Address')     
        cy.get('#debit').check({force:true})   
        cy.get('#credit').check({force:true})  
        cy.wait(5000) 
        cy.get('#submitButton').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div').should((success) => {
            expect(success.text().split('×').pop().trim()).to.equal('Success\n            Bank Details Created Successfully!')
        })
    })
    it('Delete Bank ac in Investor side', () => {
        cy.get('[value="Delete"]').click({force:true})
        cy.on('window:confirm', () => true)
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div').should((success) => {
            expect(success.text().split('×').pop().trim()).to.equal('Success\n            Bank Account Deleted Successfully!')
        })
    })

})

describe('Create and Delete Bank ac in Merchant side', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD1',{force:true}).click({ force:true })
        cy.contains('Bank').click({force:true})
    })
    it('Create Bank ac in Merchant side', () => {
        cy.contains('Create Bank Account ').click({force:true})
        cy.get('#accountHolderName').type('priya',{force:true})
        cy.get('#routingNumber').type('091000022')
        cy.get('#accountNumber').type('1234567890')
        cy.get('#debit').check({force:true})   
        cy.get('#credit').check({force:true})  
        cy.wait(5000) 
        cy.get('[value="Create"]').click({force:true})
        cy.get('#bank_details_form > div > div.box-body.alert-box-body > div').should((success) => {
            expect(success.text().split('×').pop().trim()).to.equal('Success\n            Bank Details Created Successfully')
        })
    })
    it('Delete Bank ac in Merchant side', () => {
        cy.get('[value="Delete"]').click({force:true})
        cy.on('window:confirm', () => true)
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div > div.box-head > div > div').should((success) => {
            expect(success.text().split('×').pop().trim()).to.equal('Success\n            Bank Account Deleted Successfully')
        })
    })

})


