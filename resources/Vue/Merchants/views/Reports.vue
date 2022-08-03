<template>
    <div class="view-wrapper">
        <emptyBox  :msg="errorMsg" />
    </div>
</template>

<script>
import emptyBox from '@merchantComponents/emptyBox';
export default {
    data(){
        return{
            errorMsg:'Coming soon...'
        }
    },
    components:{
        emptyBox
    }
}
</script>