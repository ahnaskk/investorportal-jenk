<template>
   <div class="form-field">
        <label v-if="label">{{label}}</label>
        <input type="text" 
            :placeholder="placeholder" 
            class="" 
            :name="name"
            @input="retrieve"
            v-model="form[name]"
        />
   </div>
</template>

<script>
export default {
    name:'text-field',
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
}
</script>
