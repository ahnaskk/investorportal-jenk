<template>
    <svg xmlns="http://www.w3.org/2000/svg" width="29" height="29" viewBox="0 0 29 29" @click.stop.prevent="$emit('edit')">
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
                    d="M10.373,7.013l.613.613-6.04,6.04H4.333v-.613l6.04-6.04M12.773,3a.667.667,0,0,0-.467.193l-1.22,1.22,2.5,2.5,1.22-1.22a.664.664,0,0,0,0-.94l-1.56-1.56A.655.655,0,0,0,12.773,3Zm-2.4,2.127L3,12.5V15H5.5l7.373-7.373-2.5-2.5Z"
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