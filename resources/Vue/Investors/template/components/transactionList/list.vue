<template>
    <div class="payment-list-box">
        <div class="title-box" v-if="bank">
            <h2 class="title">{{ title }}</h2>
            <slot name="action"></slot>
        </div>
        <!-- <virtual-list 
            style="height: 360px; overflow-y: auto;border-radius: 10px;" 
            :data-key="'id'"
            :data-sources="data"
            :data-component="itemComponent"
        /> -->
        <ul class="payment-list">
            <li
                class="payment-li"
                v-for="(p,i) in data"
                :key="i"
            >
                <transaction
                    :name="p.merchant_name"
                    :date="p.payment_date"
                    :amount="p.amount"
                    :negative="p.type==0"
                    :rcode="isRcode(p.type)"
                />
            </li>
        </ul>
    </div>
</template>

<script>
import VirtualList from 'vue-virtual-scroll-list'
import transaction from './transaction'
export default {
    name: 'list',
    components:{
        transaction
    },
    props:{
        bank:{
            required: false,
            default: true,
            type: Boolean
        },
        data: {
            required: false
        },
        title: {
            required: false
        }
    },
    methods:{
        isRcode(rcode){
            if(typeof(rcode) == "string"){
                return rcode
            }
            return null
        }
    }
}
</script>

<style
    src="~c/transactionList/list.scss"
    scoped
    lang="scss"
></style>