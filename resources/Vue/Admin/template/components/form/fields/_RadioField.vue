<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <span v-show="hasError && errorMessage" class="error">{{errorMessage}}</span>
    <div :class="[componentClass, 'radio-wrapper',{error:hasError}]">
      <div
        v-for="(radio, index) in radioList"
        :key="index"
        class="radio-wrapper-inner"
      >
        <span class="radio-input">
          <input type="radio" hidden :ref="'radio'+index" :value="radio.value" :name="radio.name" @change="retrieve(name)" v-model="form[name]"/>
          <span class="radio" @click="toggle('radio'+index)"></span>
        </span>
        <label v-if="radio.label" class="radio-label">{{ radio.label }}</label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "radio-field",
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
    radioList: {
      type: Array,
    },
    componentClass: {
      type: String,
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
    return {
      form:{}
    }
  },
  computed: {},
  methods: {
    retrieve(name) {
      console.log('emit',name)
      let obj = {};
      obj[name] = this.form[name];
      this.$emit("retrieve", obj);
    },
    toggle(index){
      this.$refs[index][0].click()
    }
  },
  mounted() {},
};
</script>

<style scoped>
  .radio{
    height: 16px;
    cursor: pointer;
    display: block;
    width: 16px;
    border-radius: 50%;
    border: 1px solid #BEC3DC;
    position: relative;
  }
  .radio::before{
    position: absolute;
    background-color: transparent;
    content: '';
    display: block;
    height: 8px;
    width: 8px;
    border-radius: 50%;
    left: 50%;
    top:50%;
    transform:translate(-50%,-50%);
    transition: background-color ease-in-out 0.2s;
  }
  .radio-wrapper-inner{
    display: flex;
    align-items: center;
  }
  label.radio-label{
    margin: 0 0 0 10px!important;
  }
  .radio-input input:checked~span::before{
    background-color: #bec3dc;
  }
</style>