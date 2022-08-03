<template>
    <div id="login-page" class="login-page pr">
        <!-- FORM SECTION wrapper -->
        <div class="form_sec pr">
            <!-- header -->
            <div class="head_sec"></div>
            <!-- contents -->
            <div class="form_container">
                <!-- logo at small screens -->
                <img :src="require('@image/icons/logo-investors.png').default" alt="logo image" class="logo-lg">
                <!-- title -->
                <h3 class="login_form_title">Login to your account</h3>
                <!-- email -->
                <input
                    type="text"
                    :class="[
                        'email',
                        { err:errors.email || errors.loginErr }
                    ]"
                    placeholder="Enter Email"
                    @change="validateEmail" v-model="data.email"
                />
                <!-- password -->
                <input
                    type="password"
                    :class="[
                        'password',
                        { err:errors.password || errors.loginErr }
                    ]"
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
                <p class="err_msg" v-if="errorMessage">
                    {{ errorMessage }}
                </p>
                <!-- login button -->
                <button
                    type="button"
                    class="bt_login bt_blank"
                    @click="login" v-loader="loading.login"
                >
                    Login
                </button>
                <!-- forgot password link -->
                <a
                    href="/password/reset"
                    class="forgot_pwd"
                    target="_blank"
                >
                    Forgot Password ?
                </a>
            </div><!-- /contents -->
        </div><!-- / FORM SECTION wrapper -->
        <!-- DESIGN SECTION wrapper -->
        <div class="design_sec"></div>
    </div>
</template>

<script>
    export default {
        name: 'login-view',
        data() {
            return {
                loading: {
                    login: false
                },
                data: {
                    // email:'Shabeer@iocod.com',
                    // password:'Laravelvue#78'
                    email: '',
                    password: ''
                },
                regex: {
                    email: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/
                },
                errors: {
                    email: false,
                    password: false,
                    loginErr: false
                },
                errorMessage : null
            }
        },
        methods: {
            login() {
                if (this.loading.login) return;
                this.errorMessage = null;
                const valUsername = this.validateEmail();
                const valPassword = this.validatePassword();
                if (!valUsername || !valPassword) {
                    if(valUsername && !valPassword)
                        this.errorMessage = 'Please Enter a valid password';
                    if(!valUsername && valPassword)
                        this.errorMessage = 'Please Enter valid information';
                    if(!valUsername && !valPassword)
                        this.errorMessage = 'Please enter all required information';
                    return;
                }else this.errorMessage = null;
                this.loading.login = true;
                this.$store.dispatch('auth/requestLogin',this.data)
                    .then(d =>{
                        if(d.data.login_board=='old'){
                            this.$store.dispatch('auth/logout');
                            
                            window.location.href ="/login";
                        }
                        if(d.two_factor){
                            this.$router.push({ path:'/two-factor-challenge'})
                        }
                    })
                    .catch(res=>{
                        let errorMessage = 'Something went wrong. Please try again later';
                        if(!res.status){
                            if(res.message) errorMessage = res.message;
                            else if(res.errors && res.errors.message)
                            errorMessage = res.errors.message;
                        }
                        if(!res.investor) errorMessage = 'These credentials do not match our records'
                        this.errorMessage = errorMessage;
                    })
                    .finally(()=>this.loading.login=false);
            },
            validateEmail() {
                this.errors.loginErr = false;
                if (!this.data.email || !this.regex.email.test(this.data.email)) {
                    this.errors.email = true;
                    return false;
                } else {
                    this.errors.email = false;
                    return true;
                }
            },
            validatePassword() {
                this.errors.loginErr = false;
                if (!this.data.password) {
                    this.errors.password = true;
                    return false;
                } else {
                    this.errors.password = false;
                    return true;
                }
            },
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~v/login.scss"
></style>