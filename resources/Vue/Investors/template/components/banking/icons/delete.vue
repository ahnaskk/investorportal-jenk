<template>
    <svg xmlns="http://www.w3.org/2000/svg" width="29" height="29" viewBox="0 0 29 29" @click.stop.prevent="$emit('edit')" class="delete">
        <g transform="translate(-387 -219)">
            <circle
                v-if="idle"
                cx="14.5"
                cy="14.5"
                r="14.5"
                transform="translate(387 219)"
                fill="#aab1dd"
            />
            <circle
                v-else
                cx="14.5"
                cy="14.5"
                r="14.5"
                transform="translate(387 219)"
            />
            <g transform="translate(394 226)">
                <path d="M0,0H16V16H0Z" fill="none" />
                <path
                    d="M7.579,12.466a.654.654,0,0,0,.654-.654V7.887a.654.654,0,1,0-1.308,0v3.925A.654.654,0,0,0,7.579,12.466Zm6.541-7.85H11.5V3.962A1.962,1.962,0,0,0,9.541,2H8.233A1.962,1.962,0,0,0,6.271,3.962v.654H3.654a.654.654,0,0,0,0,1.308h.654v7.2a1.962,1.962,0,0,0,1.962,1.962H11.5a1.962,1.962,0,0,0,1.962-1.962v-7.2h.654a.654.654,0,0,0,0-1.308ZM7.579,3.962a.654.654,0,0,1,.654-.654H9.541a.654.654,0,0,1,.654.654v.654H7.579Zm4.579,9.158a.654.654,0,0,1-.654.654H6.271a.654.654,0,0,1-.654-.654v-7.2h6.541ZM10.2,12.466a.654.654,0,0,0,.654-.654V7.887a.654.654,0,1,0-1.308,0v3.925A.654.654,0,0,0,10.2,12.466Z"
                    transform="translate(-1 -1)"
                    fill="#fff"
                />
            </g>
        </g>
    </svg>
</template>

<script>
export default {
    name: 'icon-bank',
    props:{
        idle: Boolean
    }
}
</script>

<style>

</style>