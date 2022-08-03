<template>
    <section class="payment-info-sec">
        <slot name="body"></slot>
        <slot name="footer"></slot>
    </section>
</template>

<script>
    export default {
        name: 'payment-info'
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/paymentInfo.scss"
></style>