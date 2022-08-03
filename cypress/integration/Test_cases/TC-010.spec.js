describe('TC-010', function() {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')
    })

    it('Verify investment details updated in Merchant view page', function() {
        
        cy.get('#cy_merchants').click()
        cy.get('#cy_all_merchants').click()
        cy.get('#dataTableBuilder > tbody > tr > td:nth-child(11) > a:nth-child(1)').contains('View').click()
        //Funded date
        cy.get(':nth-child(1) > .merchant-details > :nth-child(2) > .value').then(($fdate) => {
            cy.log($fdate.text())
            expect($fdate.text().trim()).to.equal('07-01-2021');
        })
        //Funded amount
        cy.get(':nth-child(1) > .merchant-details > :nth-child(3) > .value').then(($famount) => {
            cy.log($famount.text())
            expect($famount.text()).to.equal('$10,000.00');
        })
        //RTR
        cy.get(':nth-child(1) > .merchant-details > :nth-child(5) > div.value').then(($rtr) => {
            cy.log($rtr.text())
            expect($rtr.text().trim()).to.equal('$15,000.00');
        })

        //Payment amount
        cy.get(':nth-child(1) > .merchant-details > :nth-child(6) > .value').then(($p_amnt) => {
            cy.log($p_amnt.text())
            expect($p_amnt.text()).to.equal('$1,500.00');
        })

        //No of payments
        cy.get(':nth-child(2) > .merchant-details > :nth-child(3) > .value').then(($no_pmnts) => {
            cy.log($no_pmnts.text())
            expect($no_pmnts.text()).to.equal('10');
        })
        
        //Total invested
        cy.get(':nth-child(3) > .merchant-details > :nth-child(6) > .value').then(($total_invested) => {
            cy.log($total_invested.text())
            expect($total_invested.text().trim()).to.equal('$1,110.00');
        })

        //Commision
        cy.get(':nth-child(3) > .merchant-details > :nth-child(1) > .value').then(($commision) => {
            cy.log($commision.text())
            expect($commision.text().trim()).to.equal('10%');
        })

        //CTD
        cy.get(':nth-child(3) > .merchant-details > :nth-child(2) > .value > span').then(($ctd) => {
            cy.log($ctd.text())
            expect($ctd.text().trim()).to.equal('$0.00');
        })

        //Syndicate percentage
        cy.get(':nth-child(3) > .merchant-details > :nth-child(4) > .value').then(($syn_perc) => {
            cy.log($syn_perc.text())
            expect($syn_perc.text()).to.equal('10%');
        })

         //Syndicate amount
         cy.get(':nth-child(3) > .merchant-details > :nth-child(5) > .value').then(($syn_amnt) => {
            cy.log($syn_amnt.text())
            expect($syn_amnt.text()).to.equal('$1,000.00');
        })

        //Merchant balance
        cy.get(':nth-child(10) > .value').then(($mer_bal) => {
            cy.log($mer_bal.text())
            expect($mer_bal.text().trim()).to.equal('$15,000.00');
        })

        //Net zero balance
        cy.get(':nth-child(4) > .merchant-details > :nth-child(4) > .value').then(($net_z_balance) => {
            cy.log($net_z_balance.text())
            expect($net_z_balance.text().trim()).to.equal('$1,110.00');
        })







        
    })

})
