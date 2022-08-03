import Vue from 'vue'
import Vuex from 'vuex'
import modules from './modules.js';
Vue.use(Vuex)

const store = new Vuex.Store({
  modules
});

// Store Initialization when the app loads
if( modules.init && modules.auth ){
  if(modules.init.actions && modules.auth.actions){
    // general actions for auth and unauth
    for(const action in modules.init.actions){
      const pass = /^G_/.test(action);
      if(pass){
        store.dispatch('init/'+action);
      }
    }
  }
}

export default store;
