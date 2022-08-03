<template>
    <div class="container">
        <div class="image-wrap">
            <slot></slot>
        </div>
        <h4 class="empty">{{ mainContent }}</h4>
        <span class="details">{{ elaborate }}</span>
        <router-link class="back blue-bt loader" to="/marketplace">Back</router-link>
    </div>
</template>

<script>
export default {
    data(){
        return{
            mainContent:'Oops! Looks like there are no documents uploaded.',
            elaborate:''
        }
    }
}
</script>
<style 
    scoped
    lang="scss"
    src="./empty.scss"
>
</style>
