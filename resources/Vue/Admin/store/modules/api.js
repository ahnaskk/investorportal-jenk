import Call from '@a/api/caller'
import Vue from 'vue'

export default {
    namespaced:true,
    state:{
        loading:{},
        data:{}
    },
    mutations:{
        saveData({data},{field,value}){
            Vue.set(data, field, value);
        },
        load({loading},{field,val}){
            Vue.set(loading,field, val ? true : false)
        },
        clearData(s){
            Object
            .keys(s)
            .forEach(
                k => s[k] = k == 'loading' ? s[k] : null
            );
        }
    },
    actions:{
        call(c, config ){
            return (
                config.field &&
                c.getters.loading[config.field]
            ) ? null : Call(config,c)
        },
        clearData({commit}){
            commit('clearData');
        }
    },
    getters:{
        loading: s => s.loading,
        data: s => s.data
    }
}
// function $api({
//     method,
//     url,
//     post,
//     interceptor,
//     transformer,
//     field,
//     catchErr,
//     onSuccess,
//     onError,
//     onEnd
// }){
//     return new Promise((res,rej)=>{
//         middlewear[method || 'post'](url,post)
//         .then(d=> interceptor(d) || config.defaults.interceptor(d) )
//         .then(d=> transformer || config.defaults.transform)
//         .then(d=> saveData())
//     });
// }

// $api({
//     method: 'post',
//     url: '/url',
//     post: {},
//     interceptor: (data) => data.status === 200,
//     then: (data) => data.status === true,
//     transformer: (data) => data.transform(),
//     field: 'data',
//     catchErr: (e) => e.msg,
//     onSuccess: (c) => c.commit('someMutation'),
//     onError: (c,e) => c.commit('somMutation',e),
//     onEnd: (c) => c.commit('someMutation')
// })