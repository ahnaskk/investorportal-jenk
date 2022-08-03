export default (v) => v.split("_")
    .map(v=> v.length>2 ?
        v[0].toUpperCase() + v.slice(1) :
        v
    )
.join(' ');