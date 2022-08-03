<template>
    <div id="session-expired-warning">
        <div class="msg-box">
            <h1>Your session has been expired!</h1>
            <h3>Please login again!</h3>
            <button
                class="bt_blank bt_login"
                @click="close"
            >
                login
            </button>
            <!-- <button
                class="bt_blank bt_login"
                @click="close('close')"
            >
                close window
            </button> -->
        </div>  
    </div>
</template>
<script>
    export default {
        methods:{
            close(){
                this.$emit('login');
            }
        }
    }
</script>