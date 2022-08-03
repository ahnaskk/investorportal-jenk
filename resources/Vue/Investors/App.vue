<template>
  <div id="app">
    <v-dialog />
    <!-- router view -->
    <Wrapper
      :layout="layout"
      @refresh="refreshNotification()"
    >
      <router-view
        :key="$route.fullPath + loggedIn + refresh"
      ></router-view>
    </Wrapper>
    <!-- /router view -->
    <!-- alert -->
    <Alert
      v-if="alertData"
      :data="alertData"
      @close="redirect();$store.dispatch('init/alert');"
    />
    <Prompt
      v-if="promptData"
      :data="promptData"
      @close="$store.dispatch('init/prompt')"
    />
    <div class="block-loader" v-if="blockLoading">
      Loading...
    </div>
  </div>
</template>
<script>
// @ is an alias to /src
import Alert from '@c/alert';
import Prompt from '@c/prompt';
import Wrapper from '@l/Wrapper';
// import test from '@v/test';
import { bus } from "@c/banking/bus"
import { mapGetters } from 'vuex';
export default {
  name: 'App',
  computed:{
    ...mapGetters({
      loggedIn:'auth/loggedIn',
      alertData:'init/alert',
      refresh: 'init/refresh',
      promptData: 'init/prompt',
      blockLoading: 'init/blockLoading'
    }),
    layout(){
      const meta = this.$route.meta;
      if(meta) return meta.layout;
      else return 'default';
    }
  },
  components:{
    Alert,
    Wrapper,
    Prompt
  },
  methods:{
      callModal(two_factor_mandatory,two_factor_enabled) {
            console.log(two_factor_mandatory,two_factor_enabled);

      if (two_factor_mandatory && !two_factor_enabled) {
        this.show();
      } 
    },
    show() {
      this.$modal.show("dialog", {
        title: "",
        text: " Protect your account by enabling two factor authentication ",
        buttons: [
          {
            title: "Enable",
            handler: () => {
               let url = "/two-factor";
               this.$router.push(url);
               this.$modal.hide("dialog");
            },
          },

          {
            title: "Cancel",
            handler: () => {
              this.$modal.hide("dialog");
            },
          },
        ],
      });
    },
    hide() {
      this.$modal.hide("dialog");
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
    loadCollectionNotes(){
      this.$store.dispatch('api/getData',{
        force:true,
        post:{
          limit:10,
        },
        url:'collection-notes',
        field:'collectionNotes'
      }).catch(e=>{})
    },
    loadminiFAQ(){
      this.$store.dispatch('api/getData',{
        force: true,
        post:{
          limit:10
        },
        url: '/faq',
        field: 'faq'
      })
      .catch(e=>{
        this.error =true;
        this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
      })
    },
    initApp(){
      this.loadNotifications();
      this.loadCollectionNotes();
      this.loadminiFAQ();
    },
    redirect(){
      if(this.alertData.redirect){
        this.$router.go({
          path:this.alertData.redirect
        })
      }
      else if(this.alertData.dropdown){
        bus.$emit('closeAlert',this.alertData.dropdown)
      }
    },
    refreshNotification(){
      this.loadNotifications()
    }
  },


  mounted() {





      if(this.$route.path!="/two-factor" && this.$route.path!="/enable-two-factor-auth" && this.$route.path !='/login' && this.$route.path !='two-factor-challenge'){
        if(this.$store.state.auth.two_factor_mandatory!=undefined){
          this.two_factor_mandatory = this.$store.state.auth.two_factor_mandatory;
          this.two_factor_enabled = this.$store.state.auth.two_factor_enabled;
          this.callModal(this.$store.getters['auth/getTwoFactorMandatory'] ,this.two_factor_enabled);
        console.log('gttr',this.$store.getters["auth/getTwoFactorMandatory"])
        }else{
          this.callModal(true,true);
        }
      }
      
  },
  watch:{
    $route:{
      immediate:true,
      handler(to,from){
        if(to.path!="/two-factor" && to.path!="/enable-two-factor-auth" && to.path!="/login" && to.path!="two-factor-challenge"){
          if(this.$store.state.auth.two_factor_mandatory!=undefined){
            
          this.two_factor_mandatory = this.$store.state.auth.two_factor_mandatory;
          this.two_factor_enabled = this.$store.state.auth.two_factor_enabled;
          this.callModal(this.two_factor_mandatory,this.two_factor_enabled);
          }else{
          this.callModal(true,true);
          }
        }
        if(to && to.meta && to.meta.title){
          document.title = to.meta.title
        }else document.title = 'Investor Portal'
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
        const meta = this.$route.matched[0].meta;
        if(!to){
          if( meta && meta.user){
            this.$router.replace('/login');
          }
        }else{
          this.initApp();
          if(meta && meta.visitor){
              let url = this.$store.getters['init/routeBackup'].url || '/';
              this.$router.push(url)
              .then(
                  ()=>this.$store.dispatch('init/backUpRoute',null)
              ).catch(e=>console.log('router error @ App.vue 177'));
          }
          // this.$store.dispatch('auth/getInitData').catch(e=>console.log('error in loggedIn watcher @ App.vue -> 72'))
        }
      }
    }
  }
}
</script>
<style
  lang="scss"
  src="~/app.scss"
></style>