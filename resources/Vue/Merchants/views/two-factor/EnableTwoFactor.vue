<template>
    <div class="enable-two-factor">
        <preloader v-if="loading" />
        <div class="content" v-else>
            <recovery-codes :codes="recoveryCodes" v-if="recoveryCodes"/>
            <div v-else>
                <section class="intro">
                    <h3>Install a verification app on your phone</h3>
                    <p>
                                    
                        Download and install a verification App such as Google Authenticator from App Store/Play Store in your cell phone. Scan the following QR code using the Authenticator App, then enter the 6 digit code that appears on your Authenticator App and select Connect Phone. Two factor authentication is now enabled. When two factor authentication is enabled, you will be prompted for a secure, random token from your cell phone's Authenticator app.
                    </p>
                    <h4 class="step">
                        1.Scan this QR code with your verification app
                    </h4>
                    <div class="svg-wrapper"
                        v-if="data"
                        v-html="data.qr_code"
                    >
                    </div>
                    <p class="footer">
                        Once your app reads the QR code, you'll get a 6-digit code.
                    </p>
                </section>
                <section class="connect">
                    <h4 class="step">
                        2.Enter the 6-digit code here
                    </h4>
                    <p>Enter the code from the app below. Once connected, we'll remember your phone so you can use it each time you log in.</p>
                    <div class="pin">
                        <v-otp-input
                            ref="otpInput"
                            :input-classes="inputClasses"
                            separator="-"
                            :num-inputs="6"
                            :should-auto-focus="true"
                            :is-input-num="true"
                            @on-change="handleChange"
                            @on-complete="handleOnComplete"
                        />
                        <div class="btn-wrapper">
                            <button type="submit" @click.prevent="connectPhone()" :disabled="disable" :class="{disable:disable}">Connect Phone</button> 
                        </div>   
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script>
import OtpInput from "@bachdgvn/vue-otp-input";
import RecoveryCodes from "@merchant/views/two-factor/RecoveryCodes"
import { mapState } from 'vuex';
export default {
    data(){
        return{
            btnDisabled:true,
            otp:null,
            inputClasses:'otp-input',
            recoveryCodes:null
        }
    },
    components:{
        "v-otp-input": OtpInput,
        "recovery-codes" : RecoveryCodes
    },
    computed:{
      ...mapState('api',{
        data: state => state.enabletwofactor,
        loading: ({loading}) => loading.enabletwofactor
      }),
      disable(){
          if(this.otp && this.data?.two_factor_secret && this.data?.two_factor_secret) return false
          else return true
      }
    },
    methods:{
        loadQrCode(){
            this.$store.dispatch('api/getData',{
                force: true,
                url: '/enable-two-factor-details',
                field: 'enabletwofactor'
            })
            .catch(e=>{
                this.error =true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
            })
        },
        handleOnComplete(value){
            this.otp = value
        },
        handleChange(value){
            if(value.length < 6){
                this.otp = null
            }
        },
        connectPhone(e){
            let post = {
                two_factor_recovery_codes:this.data.two_factor_recovery_codes,
                two_factor_secret:this.data.two_factor_secret,
                code:this.otp
            };
            this.$store.dispatch('api/call', {
            url: '/connect-phone',
            post
            }).then(r => {
            if (!r.status) {
                this.inputClasses += ' error'
            } else {
                this.recoveryCodes = r.data.recovery_code
            }
            }).catch(e => {
                e = e || {}
                this.$store.dispatch('init/alert', {
                    type: 'init/alert',
                    message: e.msg || e.message || 'Something went wrong! Please try again later'
                })
            })
          }

    },
    created(){
        this.loadQrCode()
    }
}
</script>

<style 
    lang="scss" 
    src="~v/enable-two-factor.scss"
    scoped
>
</style>