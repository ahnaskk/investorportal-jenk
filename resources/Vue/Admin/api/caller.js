import middlewear from '@a/api/middlewear'
import api from '@a/api/api'

export default (config,{commit,state}) => new Promise((res,rej)=>{
    // Fall Back Configs
    const {
        url,
        field,
        force = false,
        method = 'post',
        values = null,
        unauthorized = false,
        save = true,
        post = {},
        interceptor = (d,rej) => d.status==200 ? d.data : rej(d),
        catchErr = d=>!d.status,
        onSuccess,
        onError =  (e,rej) => rej(e),
        transformer =  d=>d,
        onEnd = _=>_,
    } = config,
    {data} = state
    
    if(!force && data[field]) res(data[field])

    if(field) commit('load',{ field, val: true })
    if(field) commit('saveData',{ field, value: null })

    const callable = unauthorized ? api : middlewear
    callable[method](url,post)
    .then(d=>interceptor(d,rej))
    .then(transformer)
    .then(d=>{
        console.log('after transf.',d)
        if(catchErr(d)) onError(d,rej)
        else {
            let result = (onSuccess ? onSuccess : (d,c,values) =>{
                let value = d
                if(values){
                    value = {}
                    values.forEach(v=>{
                        value[v] = d[v]
                    })
                }
                if(field){
                    const data = {field,value}
                    if(save) c('saveData',data)
                }
                return value
            })(d,commit,values)
            res(result)
        }
    })
    .catch(e=>onError(e,rej))
    .finally(()=>{
        onEnd()
        commit('load',{ field, val: false })
    })
})