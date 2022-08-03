import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";

import plugins from "./plugins";
import VModal from 'vue-js-modal';
import Echo from 'laravel-echo';
 
window.Pusher = require('pusher-js');
 
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: 'us2',
    forceTLS: true
});
Vue.use(VModal,{ dialog: true });
Vue.use(plugins);

Vue.config.productionTip = false;
Vue.prototype.$bus = new Vue();

/* API CONFIGURATION
auth token is added in auth and init vuex-modules */
import middleware from "@/api/middleware";
middleware.interceptors.response.use(
  (res) => {
    if(res.data.login_dashboard=="old"){
      store.dispatch('auth/logout');
    }
console.log('in',res.data.two_factor_mandatory)
store.dispatch('auth/setTwoFactorMandatory',res.data.two_factor_mandatory);



    const data = JSON.parse(window.localStorage.getItem('auth_data'));

                data.two_factor_mandatory = res.data.two_factor_mandatory;
               

                window.localStorage.setItem(
                'auth_data',
                JSON.stringify(data)
            );
    
  
  
    return res;
  },
  (err) => {
    if (err.response && err.response.status == 401) {
      store.dispatch("auth/sessionExpired");
    }
    return Promise.reject(err);
  }
);

new Vue({
  router,
  store,
  render: (h) => h(App),
}).$mount("#app");
