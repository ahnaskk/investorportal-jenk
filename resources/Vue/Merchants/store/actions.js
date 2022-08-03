import auth from '../api/auth';
import {EventBus} from '../views/bus';
import cookie from 'vue-cookies'
export const actions={
    setToken(context,payload){
        context.commit('setToken',payload);
    },
    saveMerchants({commit},data){
        commit('saveMerchants',data);
    },
    saveMerchantID({commit},id){
        commit('saveMerchantID',id)
    },
    twoFactor({commit}, payload){
        commit('twoFactorCheck',payload)
        return new Promise ( (res,rej) => {
            res({status:true})
        })
    },
    loginWithRCode({commit,dispatch} , payload) {
        return new Promise ( (resolve,reject) => {
            auth.post('/login-by-recovery-code',payload)
            .then(response => response.data)
            .then(data => {
                if(data.status){
                    const token=data.token;
                    const user=data.data;
                    const merchants = data.data.merchants;
                    dispatch('saveMerchants',{merchants});
                    cookie.set('merchant-token',token);
                    dispatch('setToken',token);
                    EventBus.$emit('login');
                    resolve(data);
                }
                else{
                    reject(data)
                    commit('loginByRecoveryCode',data)
                }
            })
        })
    },
    twoFactorChallenge({commit,dispatch} , payload){
        return new Promise ( (resolve , reject) => {
            auth.post('/two-factor-challenge',payload)
            .then(res => res.data)
            .then(data => {
                if(data.status){
                    const token=data.token;
                    const user=data.data;
                    const merchants = data.data.merchants;
                    dispatch('saveMerchants',{merchants});
                    cookie.set('merchant-token',token);
                    dispatch('setToken',token);
                    EventBus.$emit('login');
                    resolve(data);
                }
                else{
                    reject(data)
                    commit('twoFactorChallenge',data)
                }
            })
        })
    }
}
// shoul define an action that clears all the user specific data on a call to clear on logout