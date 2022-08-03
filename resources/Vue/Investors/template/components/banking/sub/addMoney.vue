<template>
    <div class="box">
        <h2 class="title">Add Money</h2>
        <div class="input">
            <span class="placeholder">$</span>
            <currency-input
                type="text"
                :currency="null"
                v-model="amount"
                :value="amount"
            />
        </div>
        <div class="agreement" :class="{active}">
            <span class="check-wrapper">
                <span class="check-box active" @click.stop.prevent="$emit('agree')">
                    <icon :icon="['fas','check']" />
                </span>
            </span>
            <span class="label" @click.stop.prevent="$emit('agree')">
                I hereby authorize Velocity Group USA to debit {{ amount | formatMoney }} 
                {{accNo ? `from my account ending in ${accNo}` : '' }}
                
            </span>
        </div>
        <div class="submit">
            <button
                class="blue-bt"
                :class="{disabled:!active}"
                :disabled="!active"
                @click="$emit('submit',amount)"
                v-loader="loading"
                :key="active"
            >
                Add Money
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'add-money',
    data(){
        return {
            active: false,
            amount: 1000
        }
    },
    created(){
        this.$on('agree',function(){
            this.active = !this.active
        }.bind(this))
    },
    props:{
        loading: Boolean,
        accNo: [ Number, String]
    }
}
</script>

<style
    src="~c/banking/sub/addMoney.scss"
    scoped
    lang="scss"
></style>