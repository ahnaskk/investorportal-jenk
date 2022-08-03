<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <span v-show="hasError && errorMessage" class="error">{{errorMessage}}</span>
    <!-- <input type="checkbox"  v-model="form[name]" :value="value"  class="hidden" :name="name"  :checked="checked"/> -->
    <div class="checkbox-wrapper" :class="{error:hasError}" @click="check">
      <div class="toggle-group">
       <div class="toggle-wrapper" :class="{toggle:!checked}">
          <span class="button button-success" :class="{move:!checked}">On</span>
          <span class="toggle-handle"></span>
          <span class="button button-danger" :class="{'toggle-on':!checked}">Off</span>
       </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "booelan-field",
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
  data(){
    return{
      checked:false,
      form:{}
    }
  },
  methods:{
    check(){
      this.checked = !this.checked
      if(this.checked){
        this.retrieve(this.value)
      }
      else{
        this.retrieve(null)
      }
    },
    retrieve(val){
      let obj = {}
      obj[this.name] = val
      this.$emit('retrieve',obj);
    }
  },
  mounted(){
  }
};
</script>

<style scoped>
  .toggle-group{
    height: 30px;
    width: 70px;
    border-radius: 8px;
    display: flex;
    overflow: hidden;
    flex-shrink: 0;
    cursor: pointer;
  }
  .toggle-wrapper{
    height: 30px;
    position: relative;
    display: flex;
    max-width: 70px;
    transition: all ease-in-out 0.5s;
  }
  .toggle-wrapper.toggle .toggle-handle{
    right: calc( 100% - 22px);
    transition: all ease-in-out 0.5s;
  }
  .move{
    margin-left: -70px;
  }
  .toggle-group span.button.toggle-on{
    padding-left: 31px;
  }
  .toggle-handle{
    position: absolute;
    height: 26px;
    width: 20px;
    top: 2px;
    right: 2px;
    border-radius: 8px;
    background-color: #fff;
    transition: all ease-in-out 0.5s;
  }
  .toggle-group span.button{
    min-width: 70px;
    height: 100%;
    display: flex;
    align-items: center;
    transition: all ease-in-out 0.5s;
    padding: 0 10px;
    color: #fff;
    font-weight: bold;
  }
  .button-success{
    background-color: #00AA17;
  }
  .button-danger{
    background-color: #aa0000;
  }
</style>