import * as Validators from 'vuelidate/lib/validators'

export const validationRules = schema => {
    console.log(schema)
    let rules = {}
    schema.forEach(wrapper => {
        wrapper.fields.forEach(field =>{
            if(!field.hasOwnProperty('validations')) return rules
            const validations = {}
            for(const rule in field.validations){
                const params = field.validations[rule].params
                if (params) {
                    validations[rule] = Validators[rule](params)
                } else {
                    validations[rule] = Validators[rule]
                }
            }
            rules[field.name] = validations
            console.log(rules)
        })
    });
    return rules
}
