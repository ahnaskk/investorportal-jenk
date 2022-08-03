<template>
    <div class="invst-info-card">
        <div class="amounts">
            <!-- principal investment -->
            <div class="invst-info principal">
                <div class="field-title column">
                    <!-- <div
                        target="_blank"
                        class="add-fund"
                        @click="requestFund"
                    > 
                        <span class="caption">Add Money</span>
                        <icon
                            :icon="['fas','plus']"
                        ></icon>
                    </div> -->
                    <span class="title-group">
                        <h3 class="title">
                            My Wallet
                            <tooltip
                                tooltipText="Amount of cash available in your account"
                                class="left"
                                width="200"
                            />
                        </h3>
                    </span>
            
                </div>
                <div class="amount-display">
                    <h2 class="amount">
                        {{
                            data.liquidity
                        }}
                    </h2>
                </div>
            </div>
            <!-- pending ach credit -->
            <!-- <div class="invst-info pending-ach credit">
                <div class="value">
                    {{ data.pending_ach_credit || '$0.00'}}
                </div>
                <div class="label">
                    Pending  to Bank
                </div>
            </div> -->
            <!-- pending ach credit -->
            <!-- pending ach debit-->
            <!-- <div class="invst-info pending-ach debit">
                <div class="value">
                    {{ data.pending_ach_debit || '$0.00'}}
                </div>
                <div class="label">
                Pending  to velocity
                </div>
            </div> -->
            <!-- pending ach debit-->
            <!-- info list -->
            <ul class="invst-info list">
                <li class="info-li">
                    <div class="bg-box">
                        <div class="field-title">
                            <h3 class="title" title="">Principal Investment</h3>
                            <tooltip
                                tooltipText="The original sum committed to your portfolio"
                                class="left"
                                width="200"
                            />
                        </div>
                        <h2 class="amount">
                            {{data.principal_investment}}
                        </h2>
                    </div>
                </li>
                <!-- <li class="info-li">
                    <div class="bg-box">
                        <div class="field-title">
                            <h3 class="title">Projected Portfolio Value</h3>
                            <tooltip
                                tooltipText="Anticipated distributions combined with any liquidity in your account"
                                class="left"
                                width="250"
                            />
                        </div>
                        <h2 class="amount">
                            {{
                                data.portfolio_value
                            }}
                        </h2>
                    </div>
                </li> -->
                <li class="info-li">
                    <div class="bg-box">
                        <div class="field-title">
                            <h3 class="title">Merchants</h3>
                            <tooltip
                                tooltipText="Total number of deals in which your portfolio is invested"
                                class="left"
                                width="200"
                            />
                        </div>
                        <h2 class="amount">
                            {{
                                data.merchant_count
                            }}
                        </h2>
                    </div>
                </li>
            </ul>
        </div>
        <investmentSum
            class="grow"
            :data="data"
        />
        <liquidityAlert
            v-if="showLiquidityAlert"
            @close="showLiquidityAlert = false"
            :requestAmount="achAmount"
            :res="alertData.resolve"
            :rej="alertData.reject"
            :accountNoMsg="alertData.accountNoMsg"
            :id="$store.getters['auth/investorID']"
            liquidytyMsg="Add more fund to your wallet"
            :accNo="data.default_credit_bank ? data.default_credit_bank.acc_number : null"
            :loading="loading.ach"
        />
    </div>
</template>

<script>
import tooltip from '@c/tooltip'
import investmentSum from './investmentSum'
import liquidityAlert from '@c/marketplace/sub/liquidityAlert'
const strip = v => +v.toString().replace(/[^0-9\.]/g, '')
    export default {
        name: 'investment-info',
        components:{
            investmentSum,
            tooltip,
            liquidityAlert
        },
        data(){
            return {
                achAmount: 1000,
                showLiquidityAlert: false,
                alertData:{
                    resolve: null,
                    reject: null,
                    accountNoMsg: ''
                },
                loading:{
                    ach: false
                }
            }
        },
        methods: {
            alert(){
                return new Promise(function(res,rej){
                    this.alertData.resolve = res
                    this.alertData.reject = rej
                    this.showLiquidityAlert = true
                }.bind(this))
            },
            requestFund(){
                this.alert()
                .then(r=>{
                    if(r&&r.amount){
                        this.loading.ach = true
                        this.$store.dispatch('api/call', {
                            url: '/investor-ach-request-send',
                            post: {
                                amount: r.amount,
                                transaction_type:'debit'
                            }
                        }).then(r => {
                            console.log('response',r)
                            if (!r.status) {
                                this.$store.dispatch('init/alert', {
                                    type: 'warning',
                                    message: r.errors.message
                                });
                            }else{
                                this.$store.dispatch('init/alert',{
                                    type: 'success',
                                    message: 'Fund Request added!'
                                })
                            }
                        }).catch(e=>e)
                        .finally(()=>this.showLiquidityAlert = this.loading.ach = false)
                    }
                }).catch(()=>this.showLiquidityAlert = false)
            },
          addFund() {
            let balance_amount = 100
            const post = {
            amount: strip(balance_amount)
            }
            this.$store.dispatch('api/call', {
            url: '/investor-ach-request-send',
            post
            }).then(r => {
            if (r.result != 'success') {
                this.$store.dispatch('init/alert', {
                    type: 'info',
                    message: r.result
                })
            } else {
                this.$store.dispatch('init/alert', {
                    type: 'success',
                    message: 'Requested Successfully!'
                })
            }
            }).catch(e => {
                this.showAgreement = false
                e = e || {}
                this.$store.dispatch('init/alert', {
                    type: 'init/alert',
                    message: e.msg || e.message || 'Something went wrong! Please try again later'
                })
            })
            .finally(()=>this.loading.funding = false)
          },
        },
        props:[
            'data'
        ]
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/investmentInfo.scss"
></style>
