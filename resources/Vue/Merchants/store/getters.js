export const getters={
    merchantToken(state){
        return state.merchantDetails.token;
    },
    merchants: s => s.merchants,
    merchantID: s => s.merchantDetails.ID,
    twoFactor: s => s.two_factor,
    loginByRCode: s => s.loginByRCode
}