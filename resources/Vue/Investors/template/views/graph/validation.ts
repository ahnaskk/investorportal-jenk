
interface ValidField{
    field:string,
    value: null | Field
}
interface Field{
    label?:string,
    value:number
}

/**
 * Checks any of the input field value is not valid
 * @param fields fields to validate
 * @returns {boolean} any of the field is not valid
 */
export const fieldsAreValid = ( ...fields: Field[] | null ): boolean => {
    let result:boolean  = false
    fields.forEach(field=> {
        if(!(field && field.value != null)){
            result = true
        }
    })
    return result
}
/**
 * returns error messages for the invalid fields
 * @param fields  fields to validate
 * @returns {object}  error messages
 */
export const fieldErrors = ( ...fields: ValidField[] ):object => {
    let errors:{[k: string]: string} = {}
    fields.forEach(field => {
        if(!(field.value && field.value.value!=null)){
            errors[field.field] = 'This field is required'
        }
    })
    return errors
}