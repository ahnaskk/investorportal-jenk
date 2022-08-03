import middlewear from '@a/api/middlewear';
import api from '@a/api/api';
import Vue from 'vue';
export default {
    namespaced: true,
    state: {
        token: null,
        profile: null,
        loadingProfile: false,
    },
    mutations: {
        login(state, token) {
            Vue.set(state, 'token', token);
            middlewear.defaults.headers.Authorization = `Bearer ${token}`;
            localStorage.setItem(
                'authToken',
                token
            );
        },
        logout(state) {
            state.authData = null;
            state.wishlist = null;
            state.profile = null;
            // localStorage.removeItem('authToken');
            delete middlewear.defaults.headers.Authorization;
        },
        loadProfile(state, data) {
            state.loadingProfile = data;
        },
        setProfile(state, data) {
            state.profile = data;
        }
    },
    actions: {
        requestLogin({ dispatch }, payload) {
            return new Promise((res, rej) => {
                api.post('/login', payload)
                    .then(res => res.data)
                    .then(data => {
                        if (data.status) {
                            if (payload.remember) data.remember = payload.remember;
                            const token = `Bearer ${data.token}`,
                                { name, email, marketplace_flag } = data.data;
                            dispatch('saveLogin', { token, name, email, marketplace_flag });
                            res(data);
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
                api.post('/register', data)
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
        },
        getInitData({ commit, getters }) {
            if (getters.loadingProfile || getters.profile) return;
            return new Promise((res, rej) => {
                commit('loadProfile', true);
                middlewear.post('/investor-dasboard')
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
                middlewear.post('/save-profile', data)
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
        }
    },
    getters: {
        loggedIn: ({ authData }) => authData && authData.token,
        marketplaceFlag: ({ authData }) => authData && authData.marketplace_flag,
        token: ({ token }) => token ? `Bearer ${token}` : token,
        profile: ({ profile }) => profile,
        loadingProfile: ({ loadingProfile }) => loadingProfile
    }
}
