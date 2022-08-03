<template>
    <div :class="['scroll-down-container',{'active':isActive}]" style="border">
        <svg  width="92" height="159" viewBox="0 0 92 159">
            <g id="Group_1" data-name="Group 1" transform="translate(-812 -389)">
                <g id="Rectangle_1" data-name="Rectangle 1" transform="translate(812 389)" fill="rgba(2,10,33,0)" stroke="#fff" stroke-width="3">
                <rect width="92" height="159" rx="46" stroke="none"/>
                <rect x="1.5" y="1.5" width="89" height="156" rx="44.5" fill="none"/>
                </g>
                <circle id="Ellipse_1" data-name="Ellipse 1" cx="11" cy="11" r="11" transform="translate(847 454)" fill="#fff"/>
            </g>
        </svg>
        <span>Scroll down</span>
    </div>
</template>

<script>
export default {
    data(){
        return{
            isActive:true,
            timeout:1000
        }
    },
    created(){
        setTimeout( t =>{
            this.isActive = false
        },this.timeout)
    }
}
</script>

<style
    lang="scss"
    scoped
    src="~/components/scrolldown.scss"
    >
</style>

