export const isValidAccountNumber = (accountNumber,canBeEmpty = false) => {
    let acc_number = Number(accountNumber)
    if(canBeEmpty && accountNumber.length == 0) return true
    if(Number.isFinite(acc_number) && accountNumber.length >=4){
        return true
    }
    else{
        return false
    }
}