describe('Logs Report Assertion', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })
    it.only('Checking Liquidity Change in Liquidity Log Page and Amount in Merchant Liquidity Log', () => {
        cy.visit('/admin/reports/liquidityLog')
        cy.get('#date_start1').type('03-31-2022', {force:true})
        cy.get('#date_end1').type('04-01-2022', {force:true})
        cy.get('#date_filter').click({force:true})
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').then(($lq) => {
            cy.log($lq.text())
            const total_liquidity_change = $lq.text();
            cy.visit('/admin/reports/MerchantliquidityLog')
            cy.get('#date_start1').type('03-31-2022', {force:true})
            cy.get('#date_end1').type('04-01-2022', {force:true})
            cy.get('#date_filter').click({force:true})
            cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((amount) => {
                expect(amount.text()).to.equal(total_liquidity_change)
            })
        })
    })

    it('Checking Amount in `Liquidity Log` Page after clearing date filter', () => {
        cy.visit('/admin/reports/liquidityLog')
        cy.get('#date_start1').clear({force:true})
        cy.get('#date_end1').clear({force:true})
        cy.get('#date_filter').click({force:true})
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(6)').should((amount) => {
            expect(amount.text()).to.equal('$886,667.35')
        })
    })
    it('Checking Amount in `Merchant Liquidity Log` Page after clearing date filter', () => {
        cy.visit('/admin/reports/MerchantliquidityLog')
        cy.get('#date_start1').clear({force:true})
        cy.get('#date_end1').clear({force:true})
        cy.get('#date_filter').click({force:true})
        cy.get('#dataTableBuilder > tfoot > tr > th:nth-child(5)').should((amount) => {
            expect(amount.text()).to.equal('$110,969.35')
        })
    })

})

