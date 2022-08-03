<template>
    <div class="box-outer">
        <div class="service-box">
            <header>
                <h3 class="box-title"> {{ data.display_value == "mid" ? "Merchant ID (MID) : " : '' }} {{ data.name | limit(25) }}</h3>
                <router-link :to="`/marketplace/docs/${data.id || ''}`" class="view-docs" >
                    <img :src="require('@image/icons/open-document.svg').default" alt="" srcset="">
                    View Docs
                </router-link>
            </header>
            <div class="scores-row">
                <div
                    class="score-view intelli"
                    :class="getExcellence(getScore(data.experian_intelliscore))"
                    v-if="getScore(data.experian_intelliscore) > 0"
                >
                    Experian Intelliscore : &nbsp;
                    <span class="excellence">
                        {{ data.experian_intelliscore }}
                        <span class="value">
                            <icon :icon="['far','check-circle']" class="icon" />
                            {{getExcellence(getScore(data.experian_intelliscore))}}
                        </span>
                    </span>
                </div>
                <div
                    class="score-view financial"
                    :class="getExcellence(getScore(data.experian_financial_score))"
                    v-if="getScore(data.experian_financial_score) > 0"
                >
                    Experian Financial Score : &nbsp;
                    <span class="excellence">
                        {{ data.experian_financial_score }}
                        <span class="value">
                            <icon :icon="['far','check-circle']" class="icon" />
                            {{getExcellence(getScore(data.experian_financial_score))}}
                        </span>
                    </span>
                </div>
            </div>
            <!-- donut row -->
            <div class="donut-row">
                <div class="col donut">
                    <div class="box donut">
                        <canvas ref="donut-chart" style="height:100%;width:100%;"></canvas>
                        <div class="info-carrier">
                            <h3 class="percent green">
                                {{ data.fundingCompleted }}
                            </h3>
                            <h4 class="label top">Funding Completed</h4>
                            <h3 class="percent red">
                                {{ data.available }}
                            </h3>
                            <h4 class="label">Available</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="box info">
                        <div class="info-box">
                            <h3 class="label">
                                Maximum Participation Available
                            </h3>
                            <h4 class="amount">
                                {{ data.maximumParticipationAvailable }}
                            </h4>
                        </div>
                        <div class="info-box">
                            <h3 class="label">
                                Total Net Investment
                            </h3>
                            <h4 class="amount">
                                {{ data.totalFundedAmount }}
                            </h4>
                        </div>
                        <div class="info-box">
                            <h3 class="label">
                                RTR
                            </h3>
                            <h4 class="amount">
                                {{ data.rtr }}
                            </h4>
                        </div>
                        <div class="info-box last">
                            <h3 class="label">
                                Factor Rate
                            </h3>
                            <h4 class="amount">
                                {{ data.factorRate }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- payment info row -->
            <div class="payment-row">
                <div class="table-outer">
                    <div class="col">
                        <h4 class="label">Credit Score</h4>
                        <h5 class="amount">
                            {{ data.credit_score || '--' }}
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Monthly Revenue</h4>
                        <h5 class="amount">
                            {{ data.monthly_revenue || '--' }}
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Industry Name</h4>
                        <h5 class="amount">
                            {{ data.industry_name | limit(12) }}
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Daily Payment</h4>
                        <h5 class="amount">
                            {{ data.dailyPayment }}
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">No. of Payment</h4>
                        <h5 class="amount">
                            {{ data.numberOfPayments }}
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Commission</h4>
                        <h5 class="amount">
                            {{ data.commissionPayable }}%
                        </h5>
                    </div>

                    <div class="col">
                        <h4 class="label">Syndication Fee</h4>
                        <h5 class="amount">
                            {{ data.prepaid }}%
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Underwriting Fee</h4>
                        <h5 class="amount">
                            {{ data.underwritingFee }}%
                        </h5>
                    </div>
                    <div class="col">
                        <h4 class="label">Management Fee</h4>
                        <h5 class="amount">
                            {{ data.managementFee }}%
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="action-box">
            <div
                v-if="lowFundAmountError" 
                class="error"
            >
                <p>Kindly fund an amount greater than $100 in Net Funding Amount </p>
            </div>
            <div class="title-row">
            </div>
            <div class="input-row">
                <!-- Gross Amount -->
                <div class="input-col">
                    <div class="input-box">
                        <div class="label">
                            Gross Funding Amount
                        </div>
                        <div class="input-group">
                            <span class="sign">$</span>
                            <input v-model="grossAmount" @input="(e) => calculateFund('gAmount')" class="input" title="Please enter a valid decimal number." />
                        </div>
                    </div>
                </div>
                <!-- Net Amount -->
                <div class="input-col">
                    <div class="input-box">
                        <div class="label">
                            Net Funding Amount
                        </div>
                        <div class="input-group">
                            <span class="sign">$</span>
                            <input v-model="netFundingAmount" @input="(e) => calculateFund('nFAmount')" class="input" />
                        </div>
                    </div>
                </div>
                <!-- Percentage -->
                <div class="input-col">
                    <div class="input-box">
                        <div class="label">
                            Percentage
                        </div>
                        <div class="input-group">
                            <span class="sign">%</span>
                            <input v-model="percentage" @input="(e) => calculateFund('percentage')" class="input" />
                        </div>
                    </div>
                </div>
                <div class="input-col">
                    <button class="blue-bt" type="button" @click="proceedToFunding(null)" v-loader="loading.funding" ref="fundNow" :disabled="isDisabled">
                        Fund Now
                    </button>
                </div>
            </div>
        </div>
        <!-- agreement form -->
        <agreement
            v-if="showAgreement"
            :responsePdfUrl="responsePdfUrl"
            :data="agreementData"
            :loading="loading.submitSign"
            :fundingAmount="netFundingAmount"
            @hide="hideAgreement"
            @signed="proceedToFunding"
            :merchantID="data.id"
            @payWithCreditCard="payWithCreditCard"
        />
        <!-- /agreement form -->
        <liquidityAlert
            v-if="showLiquidityAlert"
            @close="showLiquidityAlert = false"
            :requestAmount="liquidityBalance"
            :res="alertData.resolve"
            :rej="alertData.reject"
            :accNo="alertData.accNo"
            :id="$store.getters['auth/investorID']"
        />
    </div>
</template>

<script>
import amount from './sub/amountInput'
import agreement from './sub/agreement'
import liquidityAlert from './sub/liquidityAlert'
import Chart from 'chart.js'
import { roundOff , uf , onRTRAmount } from './helper'
const draw = Chart.controllers.doughnut.prototype.draw
Chart.controllers.doughnut = Chart.controllers.doughnut.extend({
    draw() {
        draw.apply(this, arguments)
        let ctx = this.chart.chart.ctx
        let _fill = ctx.fill
        ctx.fill = function() {
            ctx.save()
            ctx.shadowColor = 'black'
            ctx.shadowBlur = 7
            ctx.shadowOffsetX = 0
            ctx.shadowOffsetY = 0
            _fill.apply(this, arguments)
            ctx.restore()
        }
    }
})

const strip = v => +v.toString().replace(/[^0-9\.]/g, '')
export default {
    name: 'service-box',
    data() {
        return {
            doneAction: {
                percent: false,
                amount: false
            },
            chartData: [],
            chartLabels: ['Available', 'Funding Completed'],
            loading: {
                funding: false,
                submitSign: false
            },
            showAgreement: false,
            agreementData: null,
            responsePdfUrl: null,
            fundRequest: {
                amount: 0,
                maxAmount: 0,
                grossPercent: 0
            },
            grossAmount: null,
            percentage: 100,
            netFundingAmount: null,
            showLiquidityAlert: false,
            liquidityBalance: 0,
            alertData:{
                resolve: null,
                reject: null,
                accNo: null
            },
            lowFundAmountError:false
        }
    },
    computed: {
        chartConfig() {
            return {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: this.chartData,
                        backgroundColor: [
                            '#FF6977',
                            '#14C765',
                        ],
                        borderColor: 'white',
                        hoverBorderColor: 'white',
                        borderWidth: 10,
                        weight: 10
                    }, ],
                    labels: this.chartLabels,
                },
                options: {
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 70,
                    aspectRatio: 0.5
                }
            }
        },
        isDisabled(){
            if(this.netFundingAmount < 100 || this.netFundingAmount == null){
                this.lowFundAmountError = true
                return true
            }
            else {
                this.lowFundAmountError = false
                return false
            } 
        }
    },
    props: {
        data: Object,
        uniqueIndex: Number,
        openPopUp:{
            type:Boolean,
            default:false
        },
        amountPassed:String
    },
    methods: {
        payWithCreditCard(data){
            console.log('paying with credit card',data)
        },
        getScore(s) {
            return s ?
                parseFloat(
                    s
                    .toString()
                    .replace(/[^0-9]/g, '')
                ) : 0
        },
        getExcellence(eis) {
            let excellence = ''
            if (eis <= 25) excellence = 'poor'
            else if (eis <= 50) excellence = 'average'
            else if (eis <= 75) excellence = 'good'
            else excellence = 'excellent'
            return excellence
        },
        hideAgreement(done) {
            this.showAgreement = false
            this.$set(this, 'agreementData', null)
            if (done) this.$emit('remove')
        },
        // input validators end
        initChart() {
            const ctx = this.$refs['donut-chart'].getContext('2d')
            const chart = new Chart(ctx, this.chartConfig)
        },
        proceedToFunding(sign) {
            if (this.agreementData && !sign) this.showAgreement = true
            else {
                this.loading.funding = true
                this.loading.submitSign = sign ? true : false
                const post = {
                    merchantId: this.data.id,
                    amount: strip(this.netFundingAmount),
                    grossAmount: strip(this.grossAmount)
                }
                if (sign) post.signed = sign
                this.$store.dispatch('api/call', {
                        url: '/marketplace-fund',
                        post
                    }).then(r => {
                        if (r && r.status) {
                            if (sign) {
                                // this.responsePdfUrl = r.data.pdfUrl
                                const a = document.createElement('a')
                                a.href = r.data.pdfUrl
                                a.target = '_blank'
                                document.body.appendChild(a)
                                a.click()
                                document.body.removeChild(a)
                                this.showAgreement = false
                                this.$store.dispatch('init/alert', {
                                    type: 'success',
                                    message: r.data.message
                                })
                                this.$emit('remove')
                            } else {
                                this.$set(this, 'agreementData', r.data)
                                this.showAgreement = true
                            }
                        } else {
                            if (r.errors && typeof r.errors.liquidity_status !== 'undefined') {
                                let balance_amount = this.liquidityBalance = r.errors.balance_amount
                                this.showAgreement = false
                                this.showLiquidityAlert = true
                                this.alertData.accNo = r.errors.bank_account_no
                                this.alert()
                                .then(result=>{
                                    let data = r.errors.data
                                    if (result) {
                                        if(strip(result.amount)<balance_amount){
                                            this.$store.dispatch('init/alert', {
                                                type: 'warning',
                                                message: "Please enter at least required Amount to send Request [" + balance_amount + "]"
                                            })
                                            return false
                                        }
                                        const post = {
                                            amount: strip(result.amount),
                                            transaction_type: 'debit'
                                        }
                                        this.loading.funding = true
                                        this.$store.dispatch('api/call', {
                                            url: '/investor-ach-request-send',
                                            post
                                        }).then(r => {
                                            if (!r.status) {
                                                this.$store.dispatch('init/alert', {
                                                    type: 'warning',
                                                    message: r.errors.message
                                                })
                                            } else {
                                                if (sign) {
                                                    // this.responsePdfUrl = r.data.pdfUrl
                                                    const a = document.createElement('a')
                                                    a.href = data.pdfUrl
                                                    a.target = '_blank'
                                                    document.body.appendChild(a)
                                                    a.click()
                                                    document.body.removeChild(a)
                                                    this.showAgreement = false
                                                    this.$store.dispatch('init/alert', {
                                                        type: 'success',
                                                        message: data.message
                                                    })
                                                    this.$emit('remove')
                                                } else {
                                                    this.$set(this, 'agreementData', data)
                                                    this.showAgreement = true
                                                }
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
                                    }
                                })
                                .catch(e=>e)
                                .finally(()=>this.showLiquidityAlert=false)
                            } else {
                                this.showAgreement = false
                                const e = r || {}
                                this.$store.dispatch('init/alert', {
                                    type: 'init/alert',
                                    message: e.msg || e.message || 'Something went wrong! Please try again later'
                                })
                            }
                        }
                    })
                    .catch((e = {}) => {
                        this.showAgreement = false
                        this.$store.dispatch('init/alert', {
                            type: 'init/alert',
                            message: e.msg || e.message || 'Something went wrong! Please try again later'
                        })
                    })
                    .finally(() => this.loading.funding = this.loading.submitSign = false)
            }
        },
        calculateFund(field) {
            Number.prototype.format = function() {
                return this.toString().split(/(?=(?:\d{3})+(?:\.|$))/g).join(",")
            }
            // load maximum amount
            let amount = this.fundRequest.maxAmount
            // gross percentage
            let gPercentage = this.fundRequest.grossPercent

            //validation
            //max
            let valMaxGrossAmount
            if(!this.data.syndication_fee_on_amount){
                valMaxGrossAmount =  roundOff(
                    onRTRAmount(
                        amount,
                        this.data.factorRate,
                        this.data.prepaid,
                        this.data.commissionPayable,
                        this.data.underwritingFee
                    )
                )
            }
            else{
               valMaxGrossAmount =  roundOff(amount * (1 + (gPercentage / 100)))
            }
            if (field == 'gAmount') {
                if (parseFloat(uf(this.grossAmount)) >= valMaxGrossAmount) {
                    this.calculateFund()
                } 
                else {
                    if(this.grossAmount == '.'){
                        this.grossAmount = '0.'
                    }
                    if ((/^[0-9.,]+$/).test(this.grossAmount)) {
                        if (this.grossAmount.includes('.') || this.grossAmount.includes(',')) {
                            let len = this.grossAmount.split('.').length - 1
                            if (len == 1) {
                                let arr = this.grossAmount.split('.')
                                if (arr[1].length > 2) {
                                    this.grossAmount = arr[0] + '.' + arr[1].substr(0, 2)
                                }
                            } 
                            else if (len == 0) {
                                this.grossAmount = parseFloat(uf(this.grossAmount)).format()
                            } 
                            else {
                                this.percentage = 0
                                this.grossAmount = 0
                                this.netFundingAmount = 0
                                return
                            }
                        } 
                        else {
                            this.grossAmount = parseFloat(uf(this.grossAmount)).format()
                        }
                        let deFormattedAmount = roundOff(uf(this.grossAmount) / (1 + (gPercentage) / 100))
                        if(!this.data.syndication_fee_on_amount){
                            this.netFundingAmount = roundOff(
                            onRTRAmount(
                                parseFloat(uf(this.grossAmount)),
                                this.data.factorRate,
                                this.data.prepaid,
                                this.data.commissionPayable,
                                this.data.underwritingFee,
                                true
                                )   
                            ).format()
                        }
                        else{
                            this.netFundingAmount = (deFormattedAmount).format()
                        }
                        let dfNetFundingAmount = parseFloat(uf(this.netFundingAmount))
                        this.percentage = roundOff((dfNetFundingAmount / amount) * 100)
                    } 
                    else {
                        this.percentage = null
                        this.grossAmount = null
                        this.netFundingAmount = null
                    }
                }
            } 
            else if (field == 'percentage') {
                if(this.percentage == '.'){
                    this.percentage = '0.'
                }
                if ((/^[0-9.]+$/).test(this.percentage)) {
                    if (this.percentage >= 100) {
                        this.calculateFund()
                        return
                    } 
                    else if (this.percentage <= 0) {
                        this.grossAmount = 0
                        this.netFundingAmount = 0
                        return
                    }
                    if (this.percentage.includes('.')) {
                        let dotCount = this.percentage.split('.')
                        if (dotCount.length > 2) {
                            this.percentage = 0
                            return
                        } 
                        else if (dotCount.length == 2) {
                            let arr = dotCount[1]
                            if (arr.length > 2) {
                                this.percentage = dotCount[0] + '.' + dotCount[1].substr(0, 2)
                            }
                        }
                    }
                    let newAmount = (amount * this.percentage) / 100
                    if(!this.data.syndication_fee_on_amount){
                            this.grossAmount = roundOff(
                                onRTRAmount(
                                    newAmount,
                                    this.data.factorRate,
                                    this.data.prepaid,
                                    this.data.commissionPayable,
                                    this.data.underwritingFee
                                )
                            ).format()
                        }
                    else{
                        this.grossAmount = (roundOff(newAmount * ((100 + gPercentage) / 100))).format()
                    }
                    this.netFundingAmount = (roundOff(newAmount)).format()
                } 
                else {
                    this.percentage = null
                    this.grossAmount = null
                    this.netFundingAmount = null
                }
            } 
            else if (field == 'nFAmount') {
                if (parseFloat(uf(this.netFundingAmount)) >= amount) {
                    this.calculateFund()
                } else {
                    if(this.netFundingAmount == '.'){
                        this.netFundingAmount = '0.'
                    }
                    if ((/^[0-9.,]+$/).test(this.netFundingAmount)) {
                        if (this.netFundingAmount.includes('.') || this.netFundingAmount.includes(',')) {
                            let len = this.netFundingAmount.split('.').length - 1
                            if (len == 1) {
                                let arr = this.netFundingAmount.split('.')
                                if (arr[1].length > 2) {
                                    this.netFundingAmount = arr[0] + '.' + arr[1].substr(0, 2)
                                }
                            } else if (len == 0) {
                                this.netFundingAmount = parseFloat(uf(this.netFundingAmount)).format()
                            } else {
                                this.percentage = 0
                                this.grossAmount = 0
                                this.netFundingAmount = 0
                                return
                            }
                        } else {
                            this.netFundingAmount = parseFloat(uf(this.netFundingAmount)).format()
                        }
                        this.percentage = roundOff((parseFloat(uf(this.netFundingAmount)) / amount) * 100)
                        if(!this.data.syndication_fee_on_amount){
                            this.grossAmount = roundOff(
                            onRTRAmount(
                                parseFloat(uf(this.netFundingAmount)),
                                this.data.factorRate,
                                this.data.prepaid,
                                this.data.commissionPayable,
                                this.data.underwritingFee
                                )   
                            ).format()
                        }
                        else{
                            this.grossAmount = (roundOff(parseFloat(uf(this.netFundingAmount)) * ((100 + gPercentage) / 100))).format()
                        }
                    } else {
                        this.percentage = null
                        this.grossAmount = null
                        this.netFundingAmount = null
                    }

                }
            } 
            else {
                // sets default values on page load
                this.percentage = 100
                this.netFundingAmount = amount.format()
                this.grossAmount = valMaxGrossAmount.format()
            }
        },
        alert(){
            return new Promise(function(res,rej){
                this.alertData.resolve = res
                this.alertData.reject = rej
                this.showLiquidityAlert = true
            }.bind(this))
        },
        showViewDocs(status){
            if(!status) return false
            return Number(status) > 0
        }
    },
    created() {
        if (this.data) {
            const {
                fundingCompleted,
                available,
                yourAmount,
                netValuePercent
            } = this.data
            // configure donut chart
            this.chartData.push(
                this.parcePercent(available),
                this.parcePercent(fundingCompleted)
            )
            // configure Funding request data object
            this.fundRequest.amount = this.netFundingAmount = yourAmount
            this.fundRequest.maxAmount = yourAmount
            this.fundRequest.grossPercent = this.percent = netValuePercent
            // calculate and assign the value of the gross funding amount here
            this.calculateFund()
        }
    },
    filters: {
        fixTo(val, dec) {
            val = val.toString().replace(/[]/g, '').split('.')
            if (val[1]) val[1] = val[1].slice(0, 2)
            return val.join('.')
        }
    },
    mounted() {
        this.initChart()
        if(this.amountPassed){
            this.netFundingAmount = this.amountPassed
            this.calculateFund('nFAmount')
        }
        if(this.openPopUp) this.$refs.fundNow.click()
    },
    components: {
        agreement,
        amount,
        liquidityAlert
    }
}
</script>

<style lang="scss" scoped src="~c/marketplace/serviceBox.scss"></style>