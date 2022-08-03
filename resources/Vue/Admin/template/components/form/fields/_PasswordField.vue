<template>
    <div class="form-field">
      <label v-if="label">{{ label }}</label>
      <input @input="retrieve" v-if="!confirm" type="password" class="" :name="name"  v-model="form[name]"/>
      <input @input="retrieve" v-if="confirm" type="password" class="" :name="name"  v-model="form['confirm-'+name]"/>
    </div>
</template>

<script>
export default {
  name: "password-field",
  props: {
    placeholder: {
      type: String,
    },
    label: {
      type: String,
    },
    name: {
      type: String,
    },
    confirm: {
      type: Boolean,
    },
  },
  data(){
    return{
      form:{}
    }
  },
  methods:{
    retrieve(){
      let obj = {}
      obj[this.name] = this.form[this.name]
      obj['parent'] = this.form[this.name]
      if(this.confirm){
        obj = {}
        obj['confirm-'+this.name] = this.form['confirm-'+this.name]
        obj['confirm'] = true
      }
      this.$emit('retrieve',obj);
    }
  }
};
</script>