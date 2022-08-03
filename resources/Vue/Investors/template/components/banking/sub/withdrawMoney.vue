<template>
    <div class="wrapper">
        <div class="box input-box">
            <h2 class="title">Withdraw Money</h2>
            <div class="input-group">
                <span class="placeholder">$</span>
                <currency-input
                    type="text"
                    :currency="null"
                    v-model="amount"
                    :value="amount"
                />
            </div>
            <div class="balance-info">
                <div class="balance">
                    <span class="sign">
                        $
                    </span>
                    <span class="amount">
                        {{ balance | formatMoney('') }}
                    </span>
                    <span class="label">
                        Balance
                    </span>
                </div>
                <div class="agreement m-0" :class="{active:withdrawAll}">
                <span class="check-wrapper">
                    <span class="check-box active" @click.stop.prevent="withdrawAll = !withdrawAll">
                        <icon :icon="['fas','check']" />
                    </span>
                </span>
                <span class="label" @click.stop.prevent="withdrawAll = !withdrawAll">
                    Withdraw All Money
                </span>
            </div>
            </div>
        </div>
        <div class="box agreement">
            <div class="agreement" :class="{active}">
                <span class="check-wrapper">
                    <span class="check-box active" @click.stop.prevent="$emit('agree')">
                        <icon :icon="['fas','check']" />
                    </span>
                </span>
                <span class="label" @click.stop.prevent="$emit('agree')">
                    I hereby authorize Velocity Group USA to credit {{ amount | formatMoney }} 
                    {{accNo ? `to my account ending in ${ acHash(accNo) }` : '' }}
                </span>
            </div>
            <div class="submit">
                <button
                    class="blue-bt"
                    @click="$emit('submit',amount)"
                    :class="{disabled:!active}"
                    :disabled="!active"
                    :key="active"
                    v-loader="loading"
                >
                    Withdraw Money
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'withdraw-money',
    data(){
        return {
            active: false,
            tmp: 0,
            amount: 1000,
            withdrawAll: false
        }
    },
    created(){
        this.$on('agree',function(){
            this.active = !this.active
        }.bind(this))
    },
    computed:{
        isLoading(){
            return this.loading
        }
    },
    methods:{
        acHash(v){
            return 'xxx'+v.toString().slice(-4)
        }
    },
    watch:{
        amount(to){
            if(this.withdrawAll && +to < +this.balance){
                this.tmp = to
                this.withdrawAll = false
            }
        },
        withdrawAll(to){
            if(to){
                this.tmp = this.amount
                this.amount = this.balance
            }else{
                this.amount = this.tmp
            }
        }
    },
    props:{
        loading: Boolean,
        balance: [ String, Number ],
        accNo: [ Number, String]
    }
}
</script>

<style
    src="~c/banking/sub/withdrawMoney.scss"
    scoped
    lang="scss"
></style>