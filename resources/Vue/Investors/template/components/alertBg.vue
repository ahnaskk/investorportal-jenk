<template>
    <div class="alert-bg" @click="click">
        <div class="alert-box" ref="box">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'alert',
    methods:{
        click(e){
            if(this.$clickOut(this.$refs.box,e.target,this.$el)) this.$emit('close')
        }
    },
    created(){
        document.body.style.overflow = 'hidden'
    },
    destroyed(){
        document.body.style.overflow = 'auto'
    }
}
</script>

<style
    src="~c/banking/sub/alert.scss"
    scoped
    lang="scss"
></style>