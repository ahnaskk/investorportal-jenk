<template>
    <div class="invst-info-card">
        <div class="amounts">
            <!-- principal investment -->
            <div class="invst-info principal">
                <div class="field-title column">
                    <span class="title-group">
                        <span class="greet">Welcome</span>
                        <h3 class="title">
                            {{ setName }}
                        </h3>
                    </span>
            
                </div>
            </div>
            <!-- info list -->
            <ul class="invst-info list">
                <li class="info-li">
                    <div class="bg-box">
                        <div class="field-title column">
                            <h3 class="title sm">Net Investment</h3>
                            <span class="amount">{{ data.merchant.funded }}</span>
                            <span class="date sm">on {{ data.merchant.date_funded }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <investmentSum
            class="grow"
            :data="data"
        />
    </div>
</template>

<script>
import tooltip from '@c/tooltip'
import investmentSum from './investmentSum'
const strip = v => +v.toString().replace(/[^0-9\.]/g, '')
    export default {
        name: 'investment-info',
        components:{
            investmentSum,
            tooltip,
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
        computed: {
            setName(){
                return(
                    this.data.merchant.name
                    ?? this.data.merchant.first_name 
                    ?? 'User'
                )
            }
        },
        props:[
            'data'
        ]
    }
</script>

<style
    lang="scss"
    scoped
    src="~merchantComponents/dashboard/investmentInfo.scss"
></style>
