export default function(v,l=10){
    if(v.length >l+3) return v.toString().slice(0,l) + '...'
    else return v
}