
<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <div class="wrapper" :class="{error:hasError}">
      <svg v-show="!status" :class="{ turn: open }" width="18.378" height="10.256" viewBox="0 0 18.378 10.256">
        <g id="Group_148" data-name="Group 148" transform="translate(0 0)">
          <rect
            id="Rectangle_170"
            data-name="Rectangle 170"
            width="12.763"
            height="1.74"
            rx="0.87"
            transform="translate(1.231 0) rotate(45)"
            fill="#9ba5be"
          />
          <rect
            id="Rectangle_171"
            data-name="Rectangle 171"
            width="12.763"
            height="1.74"
            rx="0.87"
            transform="translate(8.122 9.025) rotate(-45)"
            fill="#9ba5be"
          />
        </g>
      </svg>
      <multiselect 
        v-show="!status"
        :placeholder="placeholder" 
        label="name" 
        :track-by="trackBy" 
        :options="options" 
        :taggable="false"
        :multiple="false"  
        v-model="value"
        @input="retrieve"
        @open="open = true"
        @close="open = false"
      >
      </multiselect>
      <preloader v-show="status"></preloader>
    </div>
    <span v-show="hasError && errorMessage" class="error">{{errorMessage}}</span>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import preloader from "@ac/preloader"
export default {
  name: "multiselect-field",
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
    trackBy:{
      type:String,
      default:"id"
    },
    options: {
      default:null
    },
    hasError: {
      type:Boolean,
      default:false
    },
    errorMessage:{
      type:String
    },
    status:{
      type:Boolean
    },
    selected:{
      type:Object,
      default:null
    }
  },
  components:{
    Multiselect,
    preloader
  },
  data(){
    return {
      open:false,
      form:{},
      value:[]
    }
  },
  methods:{
    retrieve(value){
      console.log(value)
      let obj = {}
      obj[this.name] = this.value
      this.$emit('retrieve',obj);
    },
  },
  created(){
    if(this.selected != null){
      this.value = this.selected
    }
  }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
  .wrapper{
    width: 100%;
    border: 1px solid #bec3dc;
    transition: border ease-in-out 0.5s;
    border-radius: 10px;
    min-height: 47px;
    display: flex;
    align-items: center;
  }
  .wrapper.error{
    border: 1px solid #ff8989;
  }
  .wrapper ::v-deep .multiselect__tags{
    border: none;
    border-radius: 10px;
    align-content: center;
  }
  ::v-deep .multiselect--active{
    z-index: 5;
  }
  ::v-deep .multiselect__tag{
    background-color: #3246d3;
  }
  ::v-deep .multiselect__option--highlight,  ::v-deep .multiselect__option--highlight::after{
    background-color: #3246d3;
  }
  ::v-deep .multiselect__tag i:hover{
    background-color: #2435bb;
  }
  ::v-deep .multiselect__tag-icon:after{
    color:#b4bcfb
  }
  ::v-deep .multiselect__input{
    border: none!important;
  }
  ::v-deep .multiselect__select::before{
    display: none;
  }
  ::v-deep .multiselect__placeholder{
    margin: 2px 0 0 10px;
    font-size: 16px;
    padding: 0;
    color: #9c9fb4;
  }
  ::v-deep .multiselect__single{
    margin: 2px 0 0 10px;
  }
  .wrapper {
    width: 100%;
    position: relative;
  }
  .wrapper svg {
    position: absolute;
    right: 20px;
    top: 40%;
    z-index: 3;
    transition: transform ease-in-out 0.5s;
  }
  .wrapper svg.turn{
    transform: rotate(180deg);
    z-index: 6;
  }
  ::v-deep .preloader-container {
    max-height: 47px;
    min-height: 0;
    margin-bottom: 0;
  }
  ::v-deep .lds-dual-ring{
    height: 40px;
    width: 40px;
  }
  ::v-deep .lds-dual-ring:after {
    content: " ";
    display: block;
    width: 20px;
    height: 20px;
    margin: 2px;
  }
</style>
