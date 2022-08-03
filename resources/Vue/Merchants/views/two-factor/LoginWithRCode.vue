<template>
  <!-- contents -->
  <div class="container">
    <div class="wrapper">
        <img
            :src="require('@image/icons/logo-investors.png').default"
            alt="logo image"
            class="logo-lg"
        />
        <h4 class="head">Continue with two step verification</h4>
        <label>If you can't use your phone, log in with your emergency recovery key</label>
        <div class="rcode-wrapper">
            <input type="text" :class="['rcode',{'error':errors}]" v-model="payload.recovery_code" @input="change"/>
        </div>
        <p class="error" v-show="errors">{{ errors.message }}</p>
        <button type="submit" :class="['blue-bt',{disabled:isDisabled}]" @click="submit" :disabled="isDisabled">Submit</button>
    </div>
   
  </div>
  <!-- /contents -->
</template>

<script>
import { mapGetters } from 'vuex';
export default {
    data(){
        return{
            payload:{
                recovery_code:null,
                login_id:null
            },
            errors:false,
            inputClasses:'otp-input',
        }
    },
    computed:{
        ...mapGetters([
            'twoFactor',
            'loginByRCode'
        ]),
        isDisabled(){
            if(this.payload.login_id && this.payload.recovery_code && !this.errors) return false
            else return true
        },
    },
    methods:{
        submit(){
            this.$store.dispatch('loginWithRCode',this.payload)
            .then(res =>{
                if(res.status){
                    this.$router.push({
                        path:'/merchants'
                    })
                }
            })
            .catch(res => {
                this.errors = res.errors
            })
        },
        change(){
            this.errors = false
        }
    },
    watch:{
        twoFactor:{
            immediate:true,
            handler(to){
                if(to){
                    this.payload.login_id = to.login_id
                }
                else{
                    this.$router.push({
                        path:'/merchants'
                    })
                }
            }
        }
    }
};
</script>

<style 
    lang="scss" 
    scoped
    src="~v/two-factor/login.scss"
>
</style>
