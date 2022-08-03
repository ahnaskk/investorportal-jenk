<template>
    <div class="wrapper">
        <preloader v-if="loading" />
        <div class="inner" v-else>
            <div class="head">
                <h3>Two-step verification</h3>
                <span :class="{disable:!data.two_factor}">{{ data.two_factor ? "ENABLED" : "DISABLED" }}</span>
            </div>       
            <p>
            Protect your account by adding an extra layer of security. A second login step can keep your account secure, even if your password is compromised. To enable it, all you need is a smart phone.
            You can enable two factor authentication by clicking the following button.
            </p> 
            <button type="submit" :class="{disable:data.two_factor}" @click="redirect">
                {{ data.two_factor ? "Disable" : "Enable" }}
            </button>    
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
    computed:{
      ...mapState('api',{
        data: state => state.twofactor,
        loading: ({loading}) => loading.twofactor
      })
    },
    methods:{
        disableTwoFactor(){
            this.$store.dispatch('api/getData',{
                force: true,
                url: '/disable-two-factor',
                field: 'twofactor'
            }).then(()=>{
                const data = JSON.parse(window.localStorage.getItem('auth_data'));
                data.two_factor_enabled = false;
                window.localStorage.setItem(
                'auth_data',
                JSON.stringify(data)
            );
                
            this.$store.dispatch('auth/setTwoFactor',false)    

            })
            .catch(e=>{
                this.error =true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
            })
        },
        twofactorStatus(){
            this.$store.dispatch('api/getData',{
                force: true,
                url: '/check-two-factor',
                field: 'twofactor'
            })
            .catch(e=>{
                this.error =true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
            })
        },
        redirect(){
            if(this.data.two_factor){
                this.disableTwoFactor()
            }else{
                this.$router.push({
                    path:'enable-two-factor-auth'
                })
            }
           
        }
    },
    created(){
        this.twofactorStatus()
    }
}

</script>
<style 
    lang="scss" 
    src="~v/two-factor.scss"
    scoped
>
</style>

