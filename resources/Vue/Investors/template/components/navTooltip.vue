<template>
    <div :class="['tooltip-wrap',positionClass]">
        <span>{{tooltip}}</span>
    </div>
</template>

<script>
export default {
    props:{
        tooltip:{
            default:'Tooltip',
            type:String
        },
        position:{
            default:'bottom',
            type:String
        }
    },
    computed:{
        positionClass(){
            let positions = Object.freeze({
                top:'top',
                bottom:'bottom',
                left:'left',
                right:'right'
            })
            return positions[this.position] ?? position.bottom
        }
    },
    methods:{
        animate(){
            //
        }        
    }
}
</script>
<style scoped>
    .tooltip-wrap{
        position: absolute;
        background: #4f5355;
        color: #fff;
        padding: 5px 10px;
        min-width: 100px;
        display: block;
        border-radius: 4px;
        white-space: nowrap;
        z-index: 999;
    }
    .tooltip-wrap.bottom{
        bottom: -36px;
        right: 0;
    }
</style>