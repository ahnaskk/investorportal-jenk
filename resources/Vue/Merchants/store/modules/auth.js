import middleware from '@merchant/api/middleware';
import auth from '@merchant/api/auth';
import Vue from 'vue';
import axios from 'axios';
export default {
    namespaced: true,
    state: {
        authData: null,
        profile: null,
        loadingProfile: false,
        merchantDetails:{
            token:null,
            ID: null
        },
        merchants: null,
        two_factor : false,
        loginByRCode : false
    },
    mutations: {
        login(state, data) {
            Vue.set(state, 'authData', data);
            middleware.defaults.headers.Authorization = data.token;
            // if(data.remember){
            localStorage.setItem(
                'auth_data_merchant',
                JSON.stringify(data)
            );
            // }
        },
        logout(state) {
            state.authData = null;
            state.wishlist = null;
            state.profile = null;
            localStorage.removeItem('auth_data_merchant');
            delete middleware.defaults.headers.Authorization;
        },
        loadProfile(state, data) {
            state.loadingProfile = data;
        },
        setProfile(state, data) {
            state.profile = data;
        },
        twoFactor(state,data){
            Vue.set(state,'two_factor_redirect',data)
        },
        setToken(state,payload){
            state.merchantDetails.token=payload;
        },
        saveMerchants(state,data){
            Vue.set(state,'merchants',data);
        },
        saveMerchantID(state,ID){
            state.merchantDetails.ID = ID
        },
        twoFactorCheck(state , payload){
            state.two_factor = payload
        },
        loginByRecoveryCode(state , payload){
            state.loginByRCode = payload
        },
        twoFactorChallenge(state, payload){
            state.twoFactorChallenge = payload
        }
    },
    actions: {
        requestLogin({ dispatch }, payload) {
            return new Promise((res, rej) => {
                auth.post('/login', payload)
                    .then(res => res.data)
                    .then(data => {
                        if (data.status) {
                            if(data.data.role != "merchant") {rej({...data,merchant:false}); return}
                            if(!data.two_factor){
                                if (payload.remember) data.remember = payload.remember;
                                const token = `Bearer ${data.token}`,
                                    { name, email, marketplace_flag, encrypted_id } = data.data;
                                dispatch('saveLogin', { token, name, email, marketplace_flag, encrypted_id });
                                res(data);
                            }
                            else{
                                dispatch('twoFactorCheck',data)
                                res(data,{two_factor_redirect:true})
                            }
                        } else {
                            rej(data);
                        }
                    })
                    .catch(err => rej(err));
            })
        },
        saveLogin({ commit }, data) {
            commit('login', data);
        },
        register({ dispatch }, data) {
            return new Promise((res, rej) => {
                auth.post('/register', data)
                    .then(res => res.data)
                    .then(data => {
                        if (data.status) {
                            dispatch('saveLogin', data);
                            res(data);
                        } else {
                            rej(data);
                        }
                    })
                    .catch(err => rej(err));
            })
        },
        logout({ commit, dispatch }) {
            commit('logout');
            dispatch('api/clearData', null, { root: true });
            axios.get('/fundings/logout')
                .catch(e => e);
        },
        getInitData({ commit, getters }) {
            if (getters.loadingProfile || getters.profile) return;
            return new Promise((res, rej) => {
                commit('loadProfile', true);
                middleware.post('/investor-dasboard')
                    .then(res => res.data)
                    .then(data => {
                        if (data.status == 200) {
                            commit('setProfile', data.data);
                            res(data);
                        } else {
                            rej(data);
                        }
                    })
                    .catch(err => rej(err))
                    .finally(() => commit('loadProfile', false));
            });
        },
        saveProfile({ commit, dispatch }, data) {
            return new Promise((res, rej) => {
                middleware.post('/save-profile', data)
                    .then(r => r.data)
                    .then(data => {
                        if (data.status == 200) {
                            dispatch(
                                'init/alert',
                                {
                                    type: 'success',
                                    message: 'You have successfully updated your profile'
                                },
                                { root: true }
                            );
                            res(r);
                        }
                    })
                    .catch(e => rej(e));
            });
        },
        sessionExpired({ dispatch }) {
            dispatch('logout');
            dispatch('init/alert', {
                type: 'warning',
                message: 'The session has been expired. Please login again!'
            }, {
                root: true
            });
        },
        twoFactorCheck( {commit}, data){
            commit('twoFactor',data)
        },
        twoFactorChallenge( {dispatch}, payload){
            return new Promise((res,rej) => {
                auth.post('/two-factor-challenge',payload)
                .then(res => res.data)
                .then(data => {
                    if(data.status){
                        const token = `Bearer ${data.token}`,
                        { name, email, marketplace_flag, encrypted_id } = data.data;
                        dispatch('saveLogin', { token, name, email, marketplace_flag, encrypted_id });
                        res(data);
                    }
                    else {
                        rej(data)
                    }
                })
                .catch(err => rej(err))
            })
        },
        loginWithRCode( {dispatch} , payload){
            return new Promise((res,rej) => {
                auth.post('/login-by-recovery-code',payload)
                .then( res => res.data)
                .then( data => {
                    if (data.status) {
                            if (payload.remember) data.remember = payload.remember;
                            const token = `Bearer ${data.token}`,
                                { name, email, marketplace_flag, encrypted_id } = data.data;
                            dispatch('saveLogin', { token, name, email, marketplace_flag, encrypted_id });
                            res(data);
                    } else {
                        rej(data);
                    }
                })
                .catch(err => rej(err))
            })
        },
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
        check({commit,dispatch,state},payload) {
            const { url } = payload
            return new Promise( (res, rej ) => {
                auth.get(url,{headers:{'Authorization':state.authData.token}})
                .then(res => res.data)
                .then(data => {
                    if(data.status){
                        dispatch('saveMerchants',data.data);
                        res(data)
                    }
                    else{
                        rej(data)
                    }
                })
            })
        }
    },
    getters: {
        loggedIn: (s) => s.authData && s.authData.token,
        investorID: (s) => s.authData ? s.authData.encrypted_id : null,
        marketplaceFlag: ({ authData }) => authData && authData.marketplace_flag,
        token: (s) => s.token,
        profile: (s) => s.profile,
        loadingProfile: (s) => s.loadingProfile,
        merchantToken(state){
            return state.merchantDetails.token;
        },
        merchants: s => s.merchants,
        merchantID: s => s.authData.encrypted_id,
        paymentAmount: s => s.header && s.header.merchant.payment_amount,
        twoFactor: s => s.two_factor,
        loginByRCode: s => s.loginByRCode
    }
}
