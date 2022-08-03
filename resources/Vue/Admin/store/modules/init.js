import Vue from 'vue';
export default {
    namespaced:true,
    state:{
        alert: null,
        routeBackup:{
            url: null,
            data: null,
            action: null
        }
    },
    mutations:{
        backUpRoute({routeBackup},payload){
            if(payload){
                const {url,data,action} = payload;
                if(url) routeBackup.url = url;
                if(action) routeBackup.action = action;
                if(data) Vue.set(routeBackup,'data',data);
            }else{
                routeBackup.data = routeBackup.url = routeBackup.action = null;
            }
        },
        alert(state,data){
            state.alert = data ? data : null;
        }
    },
    actions:{
        backUpRoute({commit},p){
            commit('backUpRoute',p);
        },
        alert({commit},data){
            commit('alert',data);
            /* format of data >>
                {
                    type: 'success/warning',
                    message: 'message to show on the alert box'
                }
            */
        },
        // G_ indicates general actions which will be dispatched when the app loads. doesn't need to call it anywhere. It will automatically dispatch itself (@ store/index.js )
        G_load({dispatch}){
            // detrmine if the user is logged in
            const token = localStorage.getItem('authToken');
            if(token){
                dispatch('auth/saveLogin', token, { root: true });
            }
        }
    },
    getters:{
        alert: ({alert}) => alert,
        routeBackup:({routeBackup}) => routeBackup
    }
}

