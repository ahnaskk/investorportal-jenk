export const download = (url="",object,dateformat="ymd") =>{
    let target = url
    if( object != null || typeof(object) == object){
        Object.keys(object).forEach(k=>{
            if(object[k]){
                if(object[k] instanceof Date){
                    if(dateformat == "ymd"){
                        object[k] = new Date(object[k].getTime() - (object[k].getTimezoneOffset() * 60000 ))
                        .toISOString()
                        .split("T")[0];
                    }
                }
                else if(typeof object[k] == 'object')
                object[k] = object[k].value
                object[k]=object[k].toString().replace(/\s/g,'%');
            }
        });
        for (let key in object){
            if(object[key] !=null ){
                target+=`&${key}=${object[key]}`
            }
        }
    }
    Object.assign(document.createElement('a'), {
        target: '_blank',
        href: target,
    }).click();
}