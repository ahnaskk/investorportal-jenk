<template>
    <div id="dashboard-header" class="align-center dashboard-header pr">

        <!-- welcome Wrapper -->
        <div class="welcome-wrap">
            <!-- welcome Left -->
            <div class="welcome-left">
                <h2 class="welcome"><span>Welcome</span>{{ name }}</h2>  

                <!-- funded amount wrap -->
                <div class="welcome-value-box">
                    <!-- funded amount -->
                    <h3 class="funded-title pr">Net Investment</h3>
                    <h2 class="funded-amount">
                        {{ funded.amount }}
                    </h2>
                    <!-- date -->
                    <h3 class="funded-date">
                        on {{ funded.date }}
                    </h3>
                </div>
                <!-- /.funded amount wrap -->

            </div>
            <!-- /.welcome Left -->

            <!-- Welcome Right -->
            <div class="welcome-right">
                <div class="welcome-right-wrap">
                    <div class="welcome-btns-row row">
                        <!-- factor rate -->
                        <div class="col-lg-4">
                            <div class="card b-c">
                                <h2 class="strong">
                                    {{
                                        factorRate || ''
                                    }}
                                </h2>
                                <h3>Factor Rate</h3>
                            </div>
                        </div>
                        <!-- Total Payments -->
                        <div class="col-lg-4">
                            <div class="card m-c">
                                <h2 class="strong">
                                    {{
                                        totalPaymentsCount || ''
                                    }}
                                </h2>
                                <h3>Total Payments</h3>
                            </div>
                        </div>
                        <!-- Payments Left Count -->
                        <div class="col-lg-4">
                            <div class="card v-c">
                                <h2 class="strong">
                                    {{
                                        paymentsLeftCount
                                    }}
                                </h2>
                                <h3>Actual Payments Left</h3>
                            </div>
                        </div>
                        <!-- hidden on small screens -->
                        <!-- <div class="card r-c">
                            <h2 class="strong pr">
                                {{
                                    lender
                                }}
                            </h2>
                            <h3 class="slim">Lender</h3>
                        </div> -->                            
                    </div>

                    <!-- buttons at the bottom -->
                    <div class="buttons justify-between">
                        <!-- Request Payoff -->
                        <button
                            class="dh-bt"
                            type="button"
                            @click="requestPayoff"
                            v-if="!payoffRequested"
                            v-loader="loading.payoff"
                        >
                            Request Payoff
                        </button>
                        <!-- disabled -->
                        <button
                            class="dh-bt disabled"
                            type="button"
                            v-else
                        >
                            Payoff Requested
                        </button>
                        <!-- Request Money -->
                        <button
                            class="dh-bt money"
                            type="button"
                            @click="requestMoney"
                            v-if="!moneyRequested"
                            v-loader="loading.money"
                        >
                            Request More Money
                        </button>
                        <!-- disabled -->
                        <button
                            class="dh-bt money disabled"
                            type="button"
                            v-else
                        >
                            Money Requested
                        </button>
                    </div>
                                 
                </div>  
            </div>
            <!-- /.Welcome Right -->
        </div>
        
    </div>
</template>

<script>
import { EventBus } from '../bus.js';
export default {
    name:'dashboard-header-component',
    methods:{
        formatMoney(money){
            // money = money.toString().replace(/[^0-9]/g,'')
            // var pattern = /(-?\d+)(\d{3})/;
            // while (pattern.test(money)) {
            //     money = money.replace(pattern, '$1,$2');
            // }
            return money;
        },
        requestPayoff(){
            EventBus.$emit('requestPayoff');
        },
        requestMoney(){
            EventBus.$emit('requestMoney');
        },
    },
    computed:{
        fundedDate(){
            if(this.funded && this.funded.date){
                const date=this.funded.date;
                const splitted=date.split('-');
                const nd=[splitted[1],splitted[2],splitted[0]].join('-');
                return nd;
            }return '';
        },
        fundedAmount(){
            let amount=Number(this.funded.amount);
            if(amount< 0 ){
                amount=amount-amount*2;
                amount=`- $${
                    this.formatMoney(Number(amount.toFixed(2)))
                }`;
            }else amount=`$${
                this.formatMoney(Number(amount.toFixed(2)))
            }`;
            return amount;
        }
    },
    props:[
        'name',
        'funded',
        'payoffRequested',
        'moneyRequested',
        'loading',
        'factorRate',
        'totalPaymentsCount',
        'paymentsLeftCount',
    ]
}
</script>

<style>

</style>