<template>
  <div class="form-outer">
    <form
      :id="formRef"
      :ref="formRef"
      @submit.prevent="validate();"
    >
      <div
        v-for="(wrapper, index) in wrappers"
        v-show="wrapper.hidden != true"
        :key="`${index}`"
        :class="[wrapper.className, 'form-wrapper']"
      >
        <div
          class="form-inner"
          v-for="(field, index) in wrapper.fields"
          :key="index"
          :style="{ width:field.width ? `calc(${field.width*100}% - 40px)` : `calc(${100/(wrapper.fields.length)}% - 40px)`}"
        >
          <component
            :is="field.type + '-field'"
            :label="field.label"
            :key="field.name"
            :value="field.value"
            :placeholder="field.placeholder"
            :options="field.options"
            :name="field.name"
            :radioList="field.radioList"
            :confirm="field.confirm"
            :formRef="formRef"
            :componentClass="field.componentClass"
            :checkboxLabel="field.checkboxLabel"
            :buttonList="field.buttonList"
            :error="field.error"
            :errorMessage="errorMessages[field.name]"
            :hasError="$v.form[field.name] ? $v.form[field.name].$error : false"
            :selectAll="field.selectAll"
            :status="field.status"
            :trackBy="field.trackBy"
            :selected="field.selected"
						@retrieve="retrieveData($event)"
            @custom="customFunction($event)"
          />
        </div>
      </div>
      <div class="btn-wrapper form-wrapper">
        <slot></slot>
      </div>
    </form>
  </div>
</template>

<script>
import { kebabCase } from "lodash";
import { validationRules } from './parser'
const FieldContext = require.context("./fields", false, /_[\w]+\.vue$/),
  components = {};
FieldContext.keys().forEach((path) => {
  const Field = FieldContext(path),
    fieldName = kebabCase(path.replace(/^\.\/_/, "").replace(/\.vue$/, ""));
  components[fieldName] = Field.default || Field;
});
export default {
  props: ['data'],
  name: "formBase",
  components,
	data(){
		return {
      form:{}
		}
  },
  computed: {
    wrappers() {
      if (this.data) {
        if (
          this.data.wrappers &&
          this.data.wrappers.length > 0 &&
          Array.isArray(this.data.wrappers)
        ) {
          return this.data.wrappers;
        }
      }
    },
    formRef() {
      if (this.data) {
        if (this.data.form && this.data.form.length > 0) {
          return this.data.form;
        } else {
          return "formId1";
        }
      }
    },
    errorMessages () {
      const validations = this.$v.form
      let errors = {}
      this.wrappers.forEach(wrapper =>{
        wrapper.fields.forEach(field =>{
          if(field.name){
            const rules = field.validations
            const validator = validations[field.name]
            if(validations.hasOwnProperty(field.name) && this.$v.form.hasOwnProperty(field.name)){
              const rulesKeys = Object.keys(field.validations)
              for (let rule of rulesKeys){
                if (validator[rule] !== false) continue
                errors[field.name] = rules[rule].message
              }
            }
          }
        })
      })
      return errors
    }
  },
	methods:{
		retrieveData(data){
      console.log("retrieveData from base",data)
      if(data.hasOwnProperty('confirm') || data.hasOwnProperty('parent')){
        if(data.hasOwnProperty('confirm')){
            //confirmation field
            let dataKey = Object.keys(data);
            let target = dataKey[0];
            let pass = target.replace("confirm-","");
            this.form["confirm-"+pass] = data[target] 
            if(this.form[pass]){
              if(this.form["confirm-"+pass] != this.form[pass]){
              console.log('mismatch')
            }
          }
        }
        else{
          //password
          let dataKey = Object.keys(data);
          let target = dataKey[0];
          this.form[target] = data [target]
          if(this.form["confirm-"+target]){
            if(this.form["confirm-"+target] != this.form[target]){
              console.log('mismatch')
            }
          }
        }
      }
      let dataKey = Object.keys(data);
      let target = dataKey[0];
      this.form[target] = data[target]
      if(this.$v.form.hasOwnProperty(target)) this.$v.form[target].$touch()
    },
    validate () {
      Object.keys(this.form).forEach(key =>{
        if(this.$v.form.hasOwnProperty(key)) this.$v.form[key].$touch()
      })
      if (this.$v.form.$invalid) console.warn('invalid')
      else this.$emit('submit', this.form)
    },
    customFunction(data){
      console.log('data from form base',data);
      this.$emit('custom',data)
    }
  },
  validations () {
    return { form: validationRules(this.wrappers) }
  },
  mounted () {
  },
  created () {
    this.wrappers.forEach(wrapper =>{
      wrapper.fields.forEach(field =>{
        this.$set(this.form,field.name,null)
      })
    })
    delete this.form[undefined]
  },
};
</script>

<style 
	scoped
	lang="scss"
	src="~ac/form/form.scss"
>
</style>