import middleware from '@/api/middleware';
import auth from '@/api/auth';
import Vue from 'vue';
import axios from 'axios';
export default {
    namespaced: true,
    state: {
        authData: null,
        profile: null,
        loadingProfile: false,
       
    },
    mutations: {
        login(state, data) {
            Vue.set(state, 'authData', data);
            middleware.defaults.headers.Authorization = data.token;
            console.log('hello machan', data.two_factor_mandatory)
            Vue.set(state, 'two_factor_mandatory', data.two_factor_mandatory);
            Vue.set(state, 'two_factor_enabled', data.two_factor_enabled);

            // if(data.remember){
            localStorage.setItem(
                'auth_data',
                JSON.stringify(data)
            );
            // }
        },
        logout(state) {
            state.authData = null;
            state.wishlist = null;
            state.profile = null;
            localStorage.removeItem('auth_data');
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
        twoFactor_status(state, data){
            Vue.set(state, 'two_factor_enabled', data);
        },
        twoFactorMandatory(state, data){
            Vue.set(state, 'two_factor_mandatory', data);
        },

    
    },
    actions: {

       
      
     
        setTwoFactorMandatory({commit},data){
            commit('twoFactorMandatory', data) 
        },

        requestLogin({ dispatch }, payload) {
            return new Promise((res, rej) => {
                auth.post('/login', payload)
                    .then(res => res.data)
                    .then(data => {
                        if (data.status) {
                            if(data.data.role != "investor") {rej({...data,investor:false}); return}
                            if(!data.two_factor){
                                if (payload.remember) data.remember = payload.remember;
                                const token = `Bearer ${data.token}`,
                                    { name, email, marketplace_flag, encrypted_id,two_factor_mandatory,two_factor_enabled } = data.data;
                                dispatch('saveLogin', { token, name, email, marketplace_flag, encrypted_id ,two_factor_mandatory,two_factor_enabled});
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
        }
    },
    getters: {
        loggedIn: (s) => s.authData && s.authData.token,
        investorID: (s) => s.authData ? s.authData.encrypted_id : null,
        marketplaceFlag: ({ authData }) => authData && authData.marketplace_flag,
        token: (s) => s.token,
        profile: (s) => s.profile,
        loadingProfile: (s) => s.loadingProfile,
        getTwoFactorMandatory: (s) => s.two_factor_mandatory
    }
}
