export function combineLoading(...values:boolean[]):boolean{
    let res:boolean = false
    values.forEach(value => {
        if(value == true){
            res =  true
        } 
    })
    return res
}