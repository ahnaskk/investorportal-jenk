<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="196"
        height="188.342"
        viewBox="0 0 196 188.342"
        class="bg-dec"
    >
        <path
            v-if="idle"
            d="M492.655,88.333C429.318,45.75,595,.333,595,.333L659.463,0c8.153,0,14.762,4.477,14.762,10V188C674.225,193.523,555.991,130.917,492.655,88.333Z"
            transform="translate(-478.225)"
            class="path"
            fill="#EFF1FE"
        />
        <path
            v-else
            d="M492.655,88.333C429.318,45.75,595,.333,595,.333L659.463,0c8.153,0,14.762,4.477,14.762,10V188C674.225,193.523,555.991,130.917,492.655,88.333Z"
            transform="translate(-478.225)"
            class="path"
        />

    </svg>
</template>


<script>
export default {
    name: 'icon-bank',
    props:{
        idle: Boolean
    },
    created(){
            // :fill="idle ? '#EFF1FE' : '#3147DE'"
    }
}
</script>

<style>

</style>