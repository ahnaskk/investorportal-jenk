<template>
  <div class="form-field">
    <label v-if="label">{{ label }}</label>
    <div class="date-picker-wrapper">
      <!-- <span class="icon">
        <img src="@image/icons/calander.svg" />
      </span> -->
      <vue-timepicker
        :placeholder="placeholder"
				format="HH:mm:ss"
        :name="name"
        @input="retrieve"
        v-model="form[name]"
      >
      </vue-timepicker>
    </div>
  </div>
</template>

<script>
import VueTimepicker from "vue2-timepicker";
import "vue2-timepicker/dist/VueTimepicker.css";
export default {
  props: {
    placeholder: {
      type: String,
    },
    label: {
      type: String,
    },
    value: {
      type: String,
    },
    name: {
      type: String,
    },
    confirm: {
      type: Boolean,
    },
    formRef: {
      type: String,
    },
    componentClass: {
      type: String,
    },
  },
  components: {
    VueTimepicker,
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
		}
	},
};
</script>
<style scoped>
	::v-deep .time-picker{
		width: 100%;
	}
	::v-deep .vue__time-picker .controls .clear-btn{
		margin-right: 10px;
	}
	::v-deep .vue__time-picker .dropdown,::v-deep .vue__time-picker .dropdown .select-list{
		width: 100%;
	}
	::v-deep .vue__time-picker .dropdown .select-list ul li.active{
		background-color: #3246d3;
	}
	::v-deep .vue__time-picker input::placeholder{
		color: #9c9fb4;
	}
</style>