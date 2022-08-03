import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'

import plugins from './plugins'
Vue.use(plugins);
Vue.config.productionTip = false;
Vue.prototype.$bus = new Vue();

/* API CONFIGURATION
auth token is added in auth and init vuex-modules */
import middlewear from '@a/api/middlewear';
middlewear.interceptors.response.use(res=>res,err=>{
  if(err.response && err.response.status == 401){
    store.dispatch('auth/sessionExpired');
  }
  return Promise.reject(err);
});

new Vue({
  router,
  store,
  validations: {},
  render: h => h(App)
}).$mount('#app')