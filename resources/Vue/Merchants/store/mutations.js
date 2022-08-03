import Vue from 'vue';
import { state } from './states';
export const mutations={
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
}