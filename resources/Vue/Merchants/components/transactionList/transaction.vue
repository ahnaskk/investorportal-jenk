<template>
    <div
        class="payment-box"
        :class="[{ negative },{rcode}]"
    >
        <div class="icon">$</div>
        <div class="pay-data">
            <h3 class="paid-by" v-if="name">
                {{ name }}
            </h3>
            <h4 class="pay-date">
                {{ date }}
            </h4>
        </div>
        <div class="amount">
            {{ amount }} <br>
            <span class="type" v-if="rcode">
                ({{ rcode }})
            </span>
            <span class="type" v-else>
                ({{ negative ? 'Debit' : 'Credit'}})
            </span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'transaction',
    props:[
        'name',
        'date',
        'amount',
        'negative',
        'rcode',
    ]
}
</script>

<style
    lang="scss"
    scoped
    src="~c/transactionList/transaction.scss"
></style>