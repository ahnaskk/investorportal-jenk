describe('Reserve Liquidity Test', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder_filter > label > input').type(' lender_payment_mer', {force:true})
        cy.get('#dataTableBuilder').contains('a', 'LENDER_PAYMENT_MER') .click({ force:true })
    })
    it('Making Reserve Liquidity for a Investor', () => {
        cy.get('#investorTable').contains('a', 'INVESTORVP-2').should('have.attr', 'target', '_blank').invoke('removeAttr', 'target') .click({ force:true })
        cy.get('#edit_investor_form > div > div:nth-child(4) > div > div > a').click({force: true})
        cy.contains(' Reserve Liquidity').click({force: true})
    cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(3) > div > div > span.info-box-number.g_value').should('have.value', '$0.00')
        cy.get('#cy_create_transactions').click({force:true})
        //Note: Typing date must be after the last payment date
        cy.get('#date_start1').type('04-01-2022')
        //Type the end date is too long
        cy.get('#date_end1').type('07-07-2023')
        cy.get('#reserve_percentage').select('10.00',{force:true})
        cy.get('[value="Create"]').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.box > div > div.box-body.alert-box-body > div')
        .should((elem) => {
            expect(elem.text().split('×').pop().trim()).to.equal('Successfully Updated')
        }) 
    })

    it('Making Payment after creating reserve liquidity', () => {
        cy.contains('Add Payment').click({ force: true })
        // // cy.url().should('contains', 'http://investorportal.test/admin/payment/create');
        cy.get('#select_all').click();
        cy.get('[name="payment_date1"]').type('04-01-2022,04-02-2022,04-03-2022', { force: true })
        cy.get('#paymentClick').click()
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div.box > div.box-head > div > div > h4').invoke('text')
            .should('match', / Success/i)
    })

    it('Checking specified investor Reserved Liquidity', () => {
        cy.get('#investorTable').contains('a', 'INVESTORVP-2').should('have.attr', 'target', '_blank').invoke('removeAttr', 'target') .click({ force:true })
        cy.get('#edit_investor_form > div > div:nth-child(4) > div > div > a').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12.col-sm-12.value-box-wrap > div > div:nth-child(3) > div > div > span.info-box-number.g_value')
            .should((elem) => {
            expect(elem.text().trim()).to.equal('$54.06')
        }) 
       
    })
})

