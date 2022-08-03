<template>
  <div id="app">
    <!-- router view -->
    <Wrapper
      :layout="layout"
    >
      <router-view
        :key="$route.fullPath + loggedIn"
      ></router-view>
    </Wrapper>
    <!-- /router view -->
    <!-- alert -->
    <Alert
      v-if="alertData"
      :data="alertData"
      @close="$store.dispatch('init/alert')"
    />
  </div>
</template>
<script>
// @ is an alias to /src
import Alert from '@ac/alert';
import Wrapper from '@al/Wrapper';

import { mapGetters } from 'vuex';
export default {
  name: 'App',
  computed:{
    ...mapGetters({
      loggedIn:'auth/loggedIn',
      alertData:'init/alert'
    }),
    layout(){
      const meta = this.$route.meta;
      if(meta) return meta.layout;
      else return 'default';
    }
  },
  components:{
    Alert,
    Wrapper
  },
  methods:{
    loadNotifications(){
      const post = {};
      this.$store.dispatch('api/getData',{
        force: true,
        post,
        url: '/notification-list',
        field: 'notifications'
      });
    },
    initApp(){
      this.loadNotifications();
    }
  },
  watch:{
    $route:{
      immediate:true,
      handler(to,from){
        // scroll to top
        let stick = to.matched.some(r=> r.meta && r.meta.stickView );
        if(!stick){
          // window.scrollTo(0,0);
        }
      }
    },
    // nav back to home page on logout
    loggedIn:{
      immediate:true,
      handler(to){
        return;
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
  src="~a/app.scss"
></style>