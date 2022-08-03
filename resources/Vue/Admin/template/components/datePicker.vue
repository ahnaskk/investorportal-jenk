
<template>
  <div class="date-picker-wrapper">
    <span class="icon">
      <img :src="require('@image/icons/calander.svg').default" />
    </span>
    <datepicker
      placeholder="dd-mm-yyyy"
      :bootstrap-styling="true"
      input-class="datepicker"
      class="date-picker"
      v-model="date"
    >
    </datepicker>
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
    props:[ 'value' ],
    watch:{
      date:{
        immediate: true,
        handler(to){
          this.$emit('input',to);
        }
      }
    }
  }
</script>

<style lang="scss"  src="~ac/datePicker.scss"></style>