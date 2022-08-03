<template>
    <div class="rates">
        <ul class="invst-info list rate">
            <li
                class="info-li"
                v-for="(item,i) in [
                    {
                        icon: '$',
                        title: 'Payment Amount',
                        field: 'merchant.payment_amount',
                        width: 200,
                        bg:'#F2FFEC'
                    },
                    {
                        icon: '$',
                        title: 'Balance',
                        field: 'balance_merchant',
                        width: 200,
                        bg:'#FFF4E0'
                    },
                    {
                        icon: '$',
                        title: 'RTR',
                        field: 'merchant.rtr',
                        tooltip: 'Total amount of money paid back to you from the portfolio',
                        width: 200,
                        bg:'#FFF1F2'
                    }
                ]"
                :key="i"
            >
                <div class="rate-box">
                    <div class="icon" :style="{'background-color':item.bg}">
                        {{ item.icon }}
                    </div>
                    <div class="text">
                        <h2 class="amount">
                            {{
                                displayAmount(item.field)
                            }}
                        </h2>
                        <div class="field-title">
                            <h3 class="title">
                                {{ item.title }}
                            </h3>
                            <tooltip
                                v-if="item.tooltip"
                                :tooltipText="item.tooltip"
                                class="left"
                                :width="item.width"
                            />
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
    import tooltip from '@c/tooltip'
    export default {
        name: 'rates-box',
        props:['data'],
        components:{
            tooltip
        },
        methods:{
            /**
             * @param field where in data the value is located.
             * @returns value of the field in data.
             */
            displayAmount(field){
                let fieldArray = field.split('.')
                let value = this.data
                for(let i=0; i<fieldArray.length; i++){
                    value = value[fieldArray[i]]
                }
                return value
            }
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~merchantComponents/dashboard/ratesBox.scss"
></style>