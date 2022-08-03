export default function(v,sign = '$'){
    if(!v) return sign+'0.00';
    let money = parseFloat(
            v.toString().replace(/[^0-9.]/g, "")
        ).toFixed(2).toString();
    const pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(money)) {
        money = money.replace(pattern, '$1,$2');
    }
    return `${sign}${money}`;
}
