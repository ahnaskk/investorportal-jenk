<template>
    <div class="form-field">
      <label v-if="label">{{ label }}</label>
      <div :class="[componentClass,'checkbox']">
        <input type="checkbox" class="" :name="name" :checked="value" @change="retrieve();custom()" v-model="form[name]"/>
        <label v-if="checkboxLabel">{{checkboxLabel}}</label>
      </div>
    </div>
</template>

<script>
export default {
  name: "checkbox-field",
  props: {
    placeholder: {
      type: String,
    },
    value:{
      type:Boolean
    },
    label: {
      type: String,
    },
    name: {
      type: String,
    },
    checkboxLabel:{
      type:String
    },
    componentClass:{
      type:String
    }
  },
  data(){
    return {
      form:{}
    }
  },
  methods:{
    retrieve(){
      let obj = {}
      obj[this.name] = this.form[this.name]
      this.$emit('retrieve',obj);
    },
    custom(){
       let obj = {}
      obj[this.name] = this.form[this.name]
      this.$emit('custom',obj)
    }
  },
};
</script>
