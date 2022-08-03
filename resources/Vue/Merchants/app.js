import Vue from "vue";
import App from "./App.vue";
import store from "./store";
import router from './router/router';
import preloader from './views/components/preloader';
import axios from 'axios';
import vSelect from 'vue-select'
import VueYoutube from 'vue-youtube'
// api base
const apiBase = axios.create({
  baseURL: '/api/merchant/'
});
Vue.prototype.$api=apiBase;
// login base
const loginBase = axios.create({
  baseURL: '/api/auth'
});
Vue.prototype.$login=loginBase;

// Mixins
import tokens from './mixins/tokens';
Vue.mixin(tokens);
import money from './mixins/money';
Vue.mixin(money);

// cookies
const cookies=require('vue-cookies');
Vue.use(cookies);
// click outside plugin
import vClickOutside from 'v-click-outside';
Vue.use(vClickOutside);

// breadcrumb
import VueBreadcrumbs from 'vue-breadcrumbs';
Vue.use(VueBreadcrumbs);

// <font-awesome>
  import { library } from '@fortawesome/fontawesome-svg-core';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import {
    faUserSecret,
    faBell,
    faDesktop,
    faAngleLeft,
    faUserTie,
    faSignOutAlt,
    faClock,
    faUser,
    faCalendar,
    faCheck,
    faTimes,
    faBars,
    faExchangeAlt,
    faMoneyCheckAlt
  } from '@fortawesome/free-solid-svg-icons';
  library.add(faUserSecret, faBell,faBars, faDesktop, faAngleLeft, faUserTie, faSignOutAlt, faClock, faUser,faCalendar,faCheck,faTimes, faExchangeAlt, faMoneyCheckAlt );
  // define component
  Vue.component('icon', FontAwesomeIcon);
// </font-awesome>

// custom directives
import { loader } from './directives/loader';
Vue.directive('loader',loader);
Vue.component('preloader',preloader);
Vue.component('v-select', vSelect)
// Currency input maskimport VueCurrencyInput from 'vue-currency-input'
import VueCurrencyInput from 'vue-currency-input'
const pluginOptions = {
  /* see config reference */
  globalOptions: { 
    currency: 'USD',
    locale: undefined,
    autoDecimalMode: false,
    precision: {
      min:2,
      max:2
    },
    distractionFree: {
        hideNegligibleDecimalDigits:false,
        hideCurrencySymbol:false,
        hideGroupingSymbol:false
    },
    valueAsInteger: false,
    min: 10
  }
}
Vue.use(VueCurrencyInput, pluginOptions);
Vue.use(VueYoutube)


new Vue({
    store,
    router,
    render: h => h(App)
}).$mount("#app");
