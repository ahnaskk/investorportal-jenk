describe('Tests for assigning investors to companies', () => {
    beforeEach(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
    })
    it('Assign investor for merchant `IOCOD1`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD1') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
    })
    it('Assign investor for merchant `IOCOD2`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD2') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })
    it('Assign investor for merchant `IOCOD3`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD3') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
    })
    it('Assign investor for merchant `IOCOD4`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD4') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })
    it('Assign investor for merchant `IOCOD5`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD5') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
    })
    it('Assign investor for merchant `IOCOD6`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD6') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })
    it('Assign investor for merchant `IOCOD7`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD7') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        // cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        // cy.get('body').then(($body) => {
        //     if ($body.find('.box-body').length) {
        //         cy.get('.box-body').contains('Yes').click({ multiple: true });
        //     }
        // })
    })
    it('Assign investor for merchant `IOCOD8`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD8') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })
    it('Assign investor for merchant `IOCOD9`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD9') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })
    it('Assign investor for merchant `IOCOD10`', () => {
        cy.get('#dataTableBuilder').contains('a', 'IOCOD10') .click({ force:true })
        cy.get('[data-cy=cy_assi_based_liq]').click( { force:true } )
        cy.get('#select_all').click({force: true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div.col-md-12 > div:nth-child(1) > div > form > div:nth-child(4) > div.row > div.col-md-2 > input').click({force:true})
        cy.get('body > div.wrapper.demo > div.content-wrapper > section.content > div:nth-child(3) > div > div').click()
        cy.get('body').then(($body) => {
            if ($body.find('.box-body').length) {
                cy.get('.box-body').contains('Yes').click({ multiple: true });
            }
        })
    })

})

describe('Assert values in dashboard after assign investors', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/dashboard')

        cy.get('[name="company[]"]').check()
        cy.get('#company-dashboard-form').submit()
    })
    it('Assert for `Total RTR` value in dashboard', () => {
        cy.get('.total_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$333,109.52')
        })
    })

    it('Assert for `Expected RTR` value in dashboard', () => {
        cy.get('.expected_rtr-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$333,109.52')
        })
    })

    it('Assert for `Investors` value in dashboard', () => {
        cy.get('.total_investors-widget h3').should((elem) => {
            expect(elem.text()).to.equal('12')
        })
    })

    it('Assert for `Merchants` value in dashboard', () => {
        cy.get('.total_merchants-widget h3').should((elem) => {
            expect(elem.text()).to.equal('10')
        })
    })

    it('Assert for `Liquidity` value in dashboard', () => {
        cy.get('.liquidity-widget h3').should((elem) => {
            expect(elem.text()).to.equal('$561,781.32')
        })
    })

    it('Assert for `Syndicate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(1) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$220,000.00')
        })
    })

    it('Assert for `VP Advance Funding` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(2) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$185,734.19')
        })
    })

    it('Assert for `Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div.dashboard-companies > div:nth-child(3) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$156,047.13')
        })
    })

    it('Assert for `Total Amount Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(7) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$213,916.68')
        })
    })

    it('Assert for `Current Invested` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(8) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$213,916.68')
        })
    })

    it('Assert for `Blended Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(9) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('814.64%')
        })
    })

    it('Assert for `CTD` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(10) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Velocity Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(11) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Investor Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(12) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Pactolus Distribution` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(13) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Average Daily Balance` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(14) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Investor Portfolio` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(15) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$775,698.00')
        })
    })

    it('Assert for `Default Rate` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(16) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('0%')
        })
    })

    it('Assert for `Over Payment` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(17) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Portfolio Value` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(18) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$894,890.84')
        })
    })

    it('Assert for `Pending To Velocity` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(19) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

    it('Assert for `Pending To User Bank` value in dashboard', () => {
        cy.get('div.wrapper.demo > div.content-wrapper > section.content > div.box.box-primary.sub_adm.box-sm-wrap > div > div.container-fluid > div > div:nth-child(20) > div > div.inner > h3').should((elem) => {
            expect(elem.text()).to.equal('$0.00')
        })
    })

})

describe('Assert Total values in merchant7 view investors tab after assign investors', () => {
    before(() => {
        cy.login({ email: 'admin@investor.portal' })
        cy.visit('/admin/merchants')
        cy.get('#dataTableBuilder').contains('a', 'IOCOD7') .click({ force:true })

    })
    it('Total  //Amount', () => {   
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(3)').should((elem) => {
            expect(elem.text()).to.equal('$20,000.00')
        })   
    })
    it('Total    //RTR', () => {  
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(4)').should((elem) => {
            expect(elem.text().trim()).to.equal('$31,200.00')
        })  
    })
    it('Total   //Total invested', () => {   
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(5)').should((elem) => {
            expect(elem.text()).to.equal('$22,800.00')
        })  
    })
    it('Total  //Profit', () => { 
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(10)').should((elem) => {
            expect(elem.text().trim()).to.equal('$0.00')
        })  
    })
    it('Total     //share', () => {
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(11)').should((elem) => {
            expect(elem.text().trim()).to.equal('100.01%')
        })  
    })
    it('Total    //principal', () => {
        cy.get('#investorTable > tbody > tr:nth-child(8) > td:nth-child(9)').should((elem) => {
            expect(elem.text().trim()).to.equal('$0.00')
        })  
    })

})
    