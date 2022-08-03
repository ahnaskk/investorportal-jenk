<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <div class="date-picker-wrapper" :class="{error:hasError}">
      <span class="icon">
        <img :src="require('@image/icons/calander.svg').default" />
      </span>
      <datepicker
        placeholder="mm-dd-yyyy"
        :typeable="true"
        :bootstrap-styling="true"
        format="MM-dd-yyyy"
        input-class="datepicker"
        class="date-picker"
        :name="name"
        @input="retrieve"
        v-model="form[name]"
      >
    </datepicker>
  </div>
  <span v-show="hasError && errorMessage" class="error">{{errorMessage}}</span>
  </div>
</template>

<script>
import Datepicker from 'vuejs-datepicker';
export default {
  name: "date-field",
  props: {
    placeholder: {
      type: String,
    },
    label: {
      type: String,
    },
    value:{
      type: String
    },
    name: {
      type: String,
    },
    confirm: {
      type: Boolean,
    },
    formRef:{
      type:String
    },
    componentClass:{
      type:String
    },
    hasError: {
      type:Boolean,
      default:false
    },
    errorMessage:{
      type:String
    },
  },
  components:{
    Datepicker
  },
  data(){
    return{
      checked:false,
      form:{},
    }
  },
  methods:{
    retrieve(){
      let obj = {}
      obj[this.name] = this.form[this.name]
      this.$emit('retrieve',obj);
    }
  },
  mounted(){
  }
};
</script>

<style lang="scss"  src="~ac/datePicker.scss" scoped></style>
<style scoped>
  .date-picker-wrapper{
    transition: border ease-in-out 0.5s;
  }
  .form-wrapper .date-picker-wrapper.error ::v-deep input{
    border: 1px solid #ff8989;
  }
  ::v-deep .vdp-datepicker.date-picker input{
    color: #35495e;
  }
</style>