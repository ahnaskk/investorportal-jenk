<template>
    <div class="investment-sum-card">
        <div class="data-box">
            <div class="amounts">
                <div class="wrapper">
                    <div class="card">
                        <div class="card-inner">
                            <div class="icon-wrapper teal">
                                <div class="icon">
                                    <rate-icon/>
                                </div>
                            </div>
                            <span class="rate"> {{ data.merchant.factor_rate }}</span>
                            <span class="title">Factor Rate</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-inner">
                            <div class="icon-wrapper green">
                                <div class="icon">
                                    <dollar-icon/>
                                </div>
                            </div>
                            <span class="rate">{{ data.merchant.pmnts }}</span>
                            <span class="title">Total Payments</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-inner">
                            <div class="icon-wrapper violet">
                                <div class="icon">
                                    <payment-icon/>
                                </div>
                            </div>
                            <span class="rate">{{ data.merchant.actual_payment_left}}</span>
                            <span class="title">Actual Payments Left</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const strip = v => +v.toString().replace(/[^0-9\.]/g,'');
    import rate from  '@merchantComponents/dashboard/icons/rate'
    import payment from  '@merchantComponents/dashboard/icons/payment'
    import dollar from  '@merchantComponents/dashboard/icons/dollar'
    export default {
        name: 'investment-sum',
        props:[
            'data'
        ],
        components: {
            'rate-icon':rate,
            'dollar-icon':dollar,
            'payment-icon':payment
        },
        data(){
            return{
                colors:['#FFC646','#50BFFE','#4EBE67','#F86995','#727BED'],
                series:[45],
                updateKey:1,
                labels:['a']
            }

        },
        methods:{
            percent(v1,v2){
                [v1,v2] = [ strip(v1), strip(v2) ];
                return (v1/v2*100).toFixed(2);
            }
        },
        mounted(){
      
        },
        computed:{
            anticipatedPercentage(){
                return null
            }
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~merchantComponents/dashboard/investmentSum.scss"
></style>