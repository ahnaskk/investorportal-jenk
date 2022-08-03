/**
 * @author vishnu
 * @param {Number} amount amount input by the user.
 * @param {Number} factorRate factor rate  in percentage.
 * @param {Number} syndication syndication fee in percentage.
 * @param {Number} commision  commision in percentage.
 * @param {Number} underwriting underwriting fee in percentage.
 * @param {Boolean} reverse perform the action in reverse.
 * @description if on rtr, max amount cannot be find out by grossPercentage alone.
 * amount ( 
 *  (factor rate % * syndication %) +
 *  (commision %) +
 *  (underwriring %) +
 *  1
 *  )
 */
export const onRTRAmount = ( amount,factorRate,syndication,commision,underwriting,reverse = false) =>{
    if(typeof factorRate == 'string'){
        factorRate = Number(factorRate.replace("%",''))
    }
    return computedAmount(amount,reverse,factorRate*syndication,commision,underwriting)
}

/**
 * 
 * @param {Number} num number to round off.
 * @returns {Number} number round off to last 2 digits
 * function to round off to last 2 digits
 */
export const roundOff = (num) => {
    return Math.round((num + Number.EPSILON) * 100) / 100
}
/**
 * 
 * @param {String} string comma formatted number.
 * @returns {String} comma removed number
 */
export const  uf = (string) => {
    return (string.split(",").join(""))
}
/**
 * 
 * @param {Number} initial amount input from the user.
 * @param {Number} reverse if set to true, divide initial by gross percentage (scaleFactor)
 * to see the actual amount.
 * @param  {...Number} percentages fees
 * @returns {Number} value added amount
 */
function computedAmount(initial,reverse,...percentages){
    const scaleFactor = (percentages.reduce( (g,p) => g+p , 0) / 100 ) + 1
    if(reverse){
        return initial / scaleFactor
    }
    else{
        return initial * scaleFactor
    }
} 