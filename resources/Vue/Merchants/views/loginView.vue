<template>
    <div id="login-page" class="login-page pr">
        <!-- FORM SECTION wrapper -->
        <div class="form_sec pr">
            <!-- header -->
            <div class="head_sec"></div>
            <!-- contents -->
            <div class="form_container">
                <!-- logo at small screens -->
                <img :src="require('./components/assets/merchant_logo.png').default" alt="logo image" class="logo-lg">
                <!-- title -->
                <h3 class="login_form_title">Login to your account</h3>
                <!-- email -->
                <input
                    type="text"
                    :class="['email',{ err:errors.email || errors.loginErr }]"
                    placeholder="Enter Email"
                    @change="validateEmail"
                    v-model="data.email"
                />
                <!-- password -->
                <input
                    type="password"
                    :class="['password',{ err:errors.password || errors.loginErr }]"
                    placeholder="Enter Password"
                    @change="validatePassword"
                    @keyup.enter="login"
                    v-model="data.password"
                />
                <!-- checkbox group -->
                <!-- <span class="checkbox-group">
                    <input type="checkbox" id="remember-me">
                    <label for="remember-me">
                        Remember me 
                    </label>
                </span> -->
                <!-- show if the inputs are wrong -->
                <p
                    class="err_msg"
                    v-if="errors.loginErr"
                >
                    Username or Password is incorrect !
                </p>
                <!-- error message to show if the fields are empty -->
                <p
                    class="err_msg"
                    v-if="!errors.loginErr && errors.password || errors.email"
                >
                    Please fill all the fields in the correct format
                </p>
                <!-- login button -->
                <button
                    type="button"
                    class="bt_login bt_blank"
                    @click="login"
                    v-loader="loading.login"
                >
                    Login
                </button>
                <a href="/password/reset" class="forgot_pwd" target="_blank">
                    Forgot Password ?
                </a>
            </div><!-- /contents -->
        </div><!-- / FORM SECTION wrapper -->
        <!-- DESIGN SECTION wrapper -->
        <div class="design_sec">
            <!--  -->
        </div><!-- /DESIGN SECTION wrapper -->
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { EventBus } from './bus';
export default {
    name:'login-page',
    data(){
        return {
            loading:{
                login:false
            },
            data:{
                // email:'Shabeer@iocod.com',
                // password:'Laravelvue#78'
                email:'',
                password:'',
                type: 'merchant'
            },
            regex:{
                email:/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/
            },
            errors:{
                email:false,
                password:false,
                loginErr:false
            }
        }
    },
    computed:{
        ...mapGetters([
            'auth/twoFactor'
        ])
    },
    methods:{
        login(){
            if(this.loading.login) return;
            const valUsername=this.validateEmail();
            const valPassword=this.validatePassword();
            if( !valUsername || !valPassword ){
                return;
            }
            this.loading.login=true;
            this.$login.post('/login',this.data)
                .then(res=>res.data)
                .then(data=>{
                    if(data.status){
                        if(data.two_factor){
                            this.$store.dispatch('auth/twoFactor',data)
                            .then(res => {
                                if(res.status){
                                    this.$router.push({
                                        path:'/merchants/two-factor-challenge'
                                    })
                                }
                            })
                        }
                        else{
                            // const token=data.token;
                            // const user=data.data;
                            // const merchants = data.data.merchants;
                            this.$store.dispatch('auth/requestLogin',this.data)
                            // this.$store.dispatch('auth/saveMerchants',{merchants});
                            // this.$cookies.set('auth/merchant-token',token);
                            // this.$store.dispatch('auth/setToken',token);
                            // EventBus.$emit('login');
                        }
                    }
                    else{
                        this.errors.loginErr=true;
                    }
                })
                .catch(err=>{
                    if(err.response && err.response.status===401){
                        this.$store.dispatch('auth/sessionExpired');
                    }
                })
                .finally(()=>{
                    this.loading.login=false;
                    // EventBus.$emit('login');
                })
        },
        validateEmail(){
            this.errors.loginErr=false;
            if(!this.data.email || !this.regex.email.test(this.data.email)){
                this.errors.email=true;
                return false;
            }else{
                this.errors.email=false;
                return true;
            }
        },
        validatePassword(){
            this.errors.loginErr=false;
            if(!this.data.password){
                this.errors.password=true;
                return false;
            }else{
                this.errors.password=false;
                return true;
            }
        }
    },
    created(){
        document.title="Login | Merchant App"
    },beforeDestroy(){
        const route=this.$route;
        const meta=route.meta;
        if(meta && meta.title){
            document.title=meta.title;
        }else document.title='Merchant App';
    },
    watch:{
        twoFactor:{
            immediate:true,
            handler(to){
                if(to){
                    return
                }
                else {
                    if(this.$route.path != '/merchants'){
                        this.$router.push({
                            path:'/merchants'
                        })
                    }
                }
            }
        }
    }
}
</script>
