<template>
   <div class="form-field">
        <label v-if="label">{{label}}</label>
        <input 
            type="number" 
            :placeholder="placeholder" 
            class="" 
            :name="name"
            v-model="form[name]"
            @input="retrieve"
        />
   </div>
</template>

<script>
export default {
    name:'number-field',
    props:{
        placeholder:{
            type: String,
        },
        label:{
            type: String,
        },
        name:{
            type: String,
        }
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
            this.$emit('retrieve',obj);
        }
    }
}
</script>

<style scoped>
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }
</style>