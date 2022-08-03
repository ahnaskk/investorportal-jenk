import Vue from 'vue';
import auth from '@merchant/api/auth';
export default {
    namespaced:true,
    state:{
        alert: null,
        routeBackup:{
            url: null,
            data: null,
            action: null
        },
        refresh: 0,
        prompt: null,
        blockLoad: false,
        popup:{
            switchAccount:{
                show:false
            }
        },
        payoff:{}
    },
    mutations:{
        blockLoad(s,d){
            s.blockLoad = d || false;
        },
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
        },
        refresh(s){
            s.refresh ++
            console.log('refresing',s.refresh)
        },
        prompt(s,p){
            Vue.set(s,'prompt',p)
        },
        popup(s,p){
            Vue.set(s,'popup',p)
        },
        payoff(s,p){
            if(!p){
                p = {
                    reqSuccess:false,
                    alertMsg:'Unknown message',
                    showAlert:false
                }
            }
            Vue.set(s,'payoff',p)
        }
    },
    actions:{
        prompt({commit},p){
            if(p){
                return new Promise((res,rej)=>{
                    commit('prompt',{
                        ...p,
                        res,
                        rej
                    })
                })
            }else commit('prompt',null)
        },
        popup({commit},p){
            return new Promise((res,rej) =>{
                commit('popup',{
                    ...p,
                    res,
                    rej
                })
            })
        },
        backUpRoute({commit},p){
            commit('backUpRoute',p);
        },
        refresh({commit}){
            commit('refresh')
        },
        payoff({commit},data){
            commit('payoff',data)
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
        G_load({dispatch, commit}){
            // detrmine if the user is logged in
            const
                data = localStorage.getItem('auth_data_merchant'),
                achLoginData = localStorage.getItem('ach');
            if(data && !achLoginData) {
                dispatch('auth/saveLogin', JSON.parse(data), { root: true })
            }
            else if(achLoginData) {
                commit('blockLoad', true);
                auth.post('/fundings/login',{ email: achLoginData })
                .then(r => {
                    const creds = r.data.data;
                    creds.token = `Bearer ${creds.token}`
                    if(r.status == 200){
                        dispatch('auth/saveLogin', creds, { root: true });
                    }
                })
                .catch(e=>{
                    dispatch('alert',{
                        type: 'warning',
                        message: e.msg || e.message || "Something went wrong!"
                    })
                })
                .finally(()=>{
                    localStorage.removeItem('ach')
                    commit('blockLoad', false)
                });
            }
        }
    },
    getters:{
        blockLoading: s => s.blockLoad,
        alert: ({alert}) => alert,
        routeBackup:({routeBackup}) => routeBackup,
        refresh: s => s.refresh,
        prompt: s => s.prompt,
        popup: s => s.popup,
        payoff: s => s.payoff
    }
}

