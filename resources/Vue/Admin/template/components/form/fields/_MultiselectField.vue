<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <div class="wrapper" :class="{error:hasError}">
      <span 
        v-if="selectAll && !open && !status"
        class="select-all"
        @click="selectAllOrDeselectAll()"
      >
      {{ value == null || value.length == 0 ? "Select All" : "Deselect All" }}
      </span>
      <multiselect 
        v-show="!status"
        v-model="value" 
        :placeholder="placeholder" 
        label="name" 
        :track-by="trackBy" 
        :options="options" 
        :taggable="false"
        :multiple="true"  
        @input="retrieve"
        @open="open = true"
        @close="open = false"
        :limit="2"
      >
        <span slot="noResult">No results</span>
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
    status:{
      type: Boolean
    },
    trackBy:{
      type: String,
      default:"id"
    },
    selectAll:{
      type:Boolean
    },
    label: {
      type: String,
    },
    name: {
      type: String,
    },
    options:{
      default:null
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
    Multiselect,
    preloader
  },
  data(){
    return {
      form:{},
      open:false,
      selectAllStatus:true,
      value:null
    }
  },
  methods:{
    retrieve(){
      let obj = {}
      obj[this.name] = this.value
      this.$emit('retrieve',obj);
    },
    selectAllOrDeselectAll(){
      if (this.value == null || this.value.length == 0) this.value = this.options
      else this.value = null
      this.retrieve()
      this.selectAllStatus = !this.selectAllStatus
    }
  },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
  .wrapper{
    width: 100%;
    position: relative;
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
  .select-all{
    position: absolute;
    height: 10px;
    display: block;
    right: 20px;
    text-align: center;
    align-items: center;
    top: calc(50% - 10px);
    padding: 10px;
    z-index: 3;
    margin: auto 0;
    width: auto;
    border: none;
    outline: none;
    background: #00AA17;
    border-radius: 5px;
    font-size: 12px;
    line-height: 0px;
    color: white;
    font-weight: bold;
    box-shadow: 0 10px 20px -8px rgba(22, 141, 0, 0.22);
    cursor: pointer;
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