<template>
  <div id="appWrapper">
    <Wrapper
      :layout="layout"
      @refresh="refreshNotification()"
      v-if="loggedIn"
      :merchants="merchants && merchants.merchants"
      :key="'wrapper'+refresh"
    >
      <router-view
        :key="$route.fullPath + refresh"
      ></router-view>
    </Wrapper>
    <div v-else>
      <Login v-if="!twoFactor"/>
      <router-view v-else />
    </div>
    <SwitchAccount v-if="popup && popup.switchAccount.show" :merchants="merchants.merchants"/>
    <!-- <Home /> -->
    <!-- <sessionExpired
      v-if="expired"
      @login="goToLogin"
    /> -->

    <div
      class="popup_wrapper"
      v-if="status_alert.showAlert"
      @click="closeWarning"
      ref="alert_wrapper"
    >
      <statusAlert
        :action="true"
        :success="status_alert.reqSuccess"
        :msg="status_alert.alertMsg"
        @ok="closeAlert"
        :activate="status_alert.showAlert"
      />
    </div>
    <!-- alert -->
    <Alert
      v-if="alertData"
      :data="alertData"
      @close="$store.dispatch('init/alert');"
    />
  </div>
</template>

<script>
// testing
 import statusAlert from './views/components/statusAlert';
// testing
import Home from './views/home';
import Login from './views/loginView';
import sessionExpired from './views/components/sessionExpired';
import { EventBus } from './views/bus';
import TwoFactorLogin from './views/two-factor/Login'
import { mapGetters } from 'vuex';
import Wrapper from '@merchant/layout/Wrapper';
import SwitchAccount from '@merchantComponents/switchAccount'
import Alert from '@merchantComponents/alert/alert'

export default {
  data(){
    return {
      checkToken:false,
      // true if the session has expired
      expired:false,
    }
  },
  computed:{
    ...mapGetters({
      loggedIn:'auth/loggedIn',
      twoFactor:'auth/twoFactor',
      popup:'init/popup',
      merchants:'auth/merchants',
      payoff:'init/payoff',
      refresh:'init/refresh',
      alertData:'init/alert',
    }),
    layout(){
      const meta = this.$route.meta;
      if(meta) return meta.layout;
      else return 'default';
	},
	status_alert(){
		let reqSuccess =  this.payoff.reqSuccess ?? false
		let alertMsg = this.payoff.alertMsg ?? ''
		let showAlert = this.payoff.showAlert ?? false
		return {
			reqSuccess,
			alertMsg,
			showAlert
		}
	},
  },
  created(){
	this.$store.dispatch('auth/check',{
		force: false,
		url: '/check',
	})
	.catch(e => {
	})
  },
  components:{
    Home,
    Login,
    sessionExpired,
    Alert,
    TwoFactorLogin,
    // just fot testing
    statusAlert,
    Wrapper,
    SwitchAccount
  },
  mounted(){
    // listen for login
    // EventBus.$on('login',()=>{
    //   // this.loggedIn=true;
    // });
    // listen for logout
    // EventBus.$on('loggedOut',()=>{
    //   // this.loggedIn=false;
    // });
    // listen for session expiration
    // EventBus.$on('sessionExpired',()=>{
    //   console.log('session expired -> EventBus')
    //   this.clearSession();
    //   this.expired=true;
    // });
    // paymentRequestSuccess
    EventBus.$on('paymentRequestSuccess',(msg)=>{
      this.reqSuccess=true;
      this.alertMsg=msg;
      this.showAlert=true;
    });
    // paymentRequestFailed
    EventBus.$on('paymentRequestFailed',(msg)=>{
      this.reqSuccess=false;
      this.alertMsg=msg;
      this.showAlert=true;
    });
    // payoffRequestSuccess
    EventBus.$on('payoffRequestSuccess',(msg)=>{
      this.reqSuccess=true;
      this.alertMsg=msg;
      this.showAlert=true;
    });
    // payoffRequestFailed
    EventBus.$on('payoffRequestFailed',(msg)=>{
      this.reqSuccess=false;
      this.alertMsg=msg;
      this.showAlert=true;
    });
  },
  methods:{
    goToLogin(){
      this.expired=false;
    },
    // clearSession(){
    //   this.$cookies.remove('merchant-token');
    //   EventBus.$emit('loggedOut');
    // },
    closeAlert(){
      this.alertMsg='';
      this.showAlert=false;
    },
    closeWarning(e){
      if(e.target==this.$refs.alert_wrapper){
        this.closeAlert();
      }
    },
    loadNotifications(){
      const post = {};
      this.$store.dispatch('api/getData',{
        force: true,
        post,
        url: '/notification-list',
        field: 'notifications'
      }).catch(e=>{});
    },
    initApp(){
      this.loadNotifications();
    },
    refreshNotification(){
      return null
    }
  },
  /**
   * watch handles the redirections on login , logout
   * and session expiry.
   */
  watch:{
    $route:{
      immediate:true,
      handler(to,from){
        if(to && to.meta && to.meta.title){
          document.title = to.meta.title
        }else document.title = 'Merchant App'
        // scroll to top
        let stick = to.matched.some(r=> r.meta && r.meta.stickView );
        if(!stick){
          window.scrollTo(0,0);
        }
      }
    },
    // nav back to home page on logout
    loggedIn:{
      immediate:true,
      handler(to){
        const meta = this.$route.matched[0]?.meta ?? undefined;
        if(!to){
          if( meta && meta.user){
            this.$router.replace('/login');
          }
        }else{
          this.initApp();
          if(meta && meta.user){
              let url = this.$store.getters['init/routeBackup'].url || this.$router.currentRoute.path;
              this.$router.push(url)
              .then(
                  ()=>this.$store.dispatch('init/backUpRoute',null)
              ).catch(e =>{});
          }
          else{
            /**
             * Redirect the logged in user to dashboard
             * when signing in.
             */
            if(this.$router.currentRoute.path != '/merchants/dashboard'){
              this.$router.replace('/merchants/dashboard');
            }
          }
          // this.$store.dispatch('auth/getInitData').catch(e=>console.log('error in loggedIn watcher @ App.vue -> 72'))
        }
      }
    }
  }
}
</script>