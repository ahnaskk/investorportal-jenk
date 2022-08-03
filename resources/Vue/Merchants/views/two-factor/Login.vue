<template>
  <!-- contents -->
  <div class="container">
    <div class="wrapper">
        <img
            :src="require('@image/icons/logo-investors.png').default"
            alt="logo image"
            class="logo-lg"
            style="position:relative;"
        />
        <h4 class="head">Continue with two step verification</h4>
        <label>Please enter the 6 digit code on your authentication app.</label>
        <div class="otp-wrapper">
            <v-otp-input
                ref="otpInput"
                :input-classes="inputClasses"
                separator=""
                :num-inputs="6"
                :should-auto-focus="true"
                :is-input-num="true"
                @on-change="handleChange"
                @on-complete="handleOnComplete"
            />
        </div>
        <p class="error" v-show="errors">{{ errors.message}}</p>
        <button type="submit" :class="['blue-bt',{disabled:isDisabled}]" @click="submit" :disabled="isDisabled">Submit</button>
        <router-link to="/merchants/login-by-recovery-key">Can't use your phone ?</router-link>
    </div>
   
  </div>
  <!-- /contents -->
</template>

<script>
import OtpInput from "@bachdgvn/vue-otp-input";
import { mapGetters, mapState } from 'vuex';
export default {
    data(){
        return{
            btnDisabled:true,
            payload:{
                code:null,
                login_id:null
            },
            errors:false,
            inputClasses:'otp-input',
        }
    },
    components:{
        "v-otp-input": OtpInput,
    },
    computed:{
        ...mapGetters({
           twoFactor: 'auth/twoFactor'
        }),
        isDisabled(){
            if(this.payload.code && this.payload.login_id && !this.errors) {
                return false
            }
            else return true
        }
    },
    methods:{
        handleOnComplete(value){
            this.payload.code = value
        },
        handleChange(value){
            this.inputClasses = this.inputClasses.replace('error','')
            this.errors = false
            if(value.length < 6){
                this.otp = null
            }
        },
        submit(){
            this.$store.dispatch('auth/twoFactorChallenge',this.payload)
            .then(res => {
                if(res.status){
                    this.$router.push({
                        path:'/merchants/dashboard'
                    })
                    .catch(err => {})
                }
            })
            .catch(res => {
                this.errors = res.errors
                this.inputClasses += ' error'
            })
        },
    },
    watch:{
        twoFactor:{
            immediate:true,
            handler(to){
               if(to){
                this.payload.login_id = to.login_id
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
