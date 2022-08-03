
<template>
  <div class="date-picker-wrapper">
    <span class="icon">
      <img :src="require('@image/icons/calander.svg').default" />
    </span>
    <datepicker
      placeholder="mm-dd-yyyy"
      format="MM-dd-yyyy"
      :bootstrap-styling="true"
      input-class="datepicker"
      :typeable="false"
      class="date-picker"
      v-model="date"
    >
    </datepicker>
    <button
      class="clear-bt"
      v-if="date"
      @click="date = null"
    >
     clear
    </button>
  </div>
</template>

<script>
  import Datepicker from 'vuejs-datepicker';
  export default {
    name: 'date-picker-component',
    data(){
      return {
        date:null
      }
    },
    components: {
      Datepicker
    },
    created(){
      if(this.value) this.date = this.value;
    },
    props:['value'],
    watch:{
      date:{
        immediate: true,
        handler(to){
          this.$emit('input',to);
        }
      },
      value:{
        immediate:true,
        handler(val){
          if(val == null) this.date = null
        }
      }
    },
  }
</script>

<style lang="scss"  src="~c/datePicker.scss"></style>