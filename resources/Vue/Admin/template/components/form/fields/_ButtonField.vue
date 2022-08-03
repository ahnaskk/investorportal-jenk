<template>
   <div :class="[componentClass,'form-field']">
		<div class="button-wrapper">
		     <div 
        v-for="(button,index) in buttonList"
        :key="index"
        class="button-wrapper-inner"
    >	
        <button 
            :type="button.type" 
            :class="[button.customClass]" 
        >
        {{button.label}}
        </button>
		</div>    
   </div>
		</div>		

</template>

<script>
export default {
  name: "button-field",
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
    buttonList: {
      type: Array,
		},
		componentClass: {
			type:String
		}
	},
	computed:{
	},
	methods:{
	},
	mounted(){
	}
};
</script>

<style scoped>
  .button-wrapper{
    display: flex;
    align-self: flex-end;
  }
  .button-wrapper-inner + .button-wrapper-inner{
    margin-left: 20px;
  }
</style>