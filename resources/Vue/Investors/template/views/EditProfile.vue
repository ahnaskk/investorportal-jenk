<template>
    <div class="wrapper">
        <preloader v-if="loading"></preloader>
        <div v-else class="form-outer">
            <form action="" method="post" autocomplete="false" @submit.prevent="editProfile" id="editprofile">
                <div 
                    class="form-group"
                    v-for="(field,index) in fields"
                    :key="index"
                >
                    <h4 class="label">{{field.label}} <span class="mandatory" v-if="field.required">*</span></h4> 
                    <input
                        class="input"
                        v-model="vals[field.field]"
                        v-if="field.mask"
                        v-mask="field.mask"
                        maskChar=" "
                        @blur="copy(field.field)"
                    />
                    <input
                        v-else
                        :type="field.type"
                        class="input"
                        v-model="vals[field.field]"
                        @blur="copy(field.field)"
                    />
                    <p class="error-msg" v-if="error[field.field]">{{ error[field.field] }}</p>
                </div>
                <div  class="submit-row">
                    <button  class="blue-bt loader">Update</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import preloader from "@c/preloader"

export default {
    components:{
        preloader
    },
    data(){
        return{
            loading: false,
            vals:{
                name: '',
                email: '',
                cell_phone: '',
                notification_email: '',
                password: '',
                confirm_password: '',
            },
            error:{
                name: false,
                email: false,
                cell_phone: false,
                notification_email: false,
                password: false,
                confirm_password: false,
            },
            fields:[
                {
                    label: 'Name',
                    field: 'name',
                    value: '',
                    type:'text',
                    required:true
                },
                {
                    label: 'Email',
                    field: 'email',
                    value: '',
                    type:'text',
                    required:true
                },
                {
                    label: 'Cell Phone',
                    field: 'cell_phone',
                    value: '',
                    mask: '(###) ###-####',
                    type:'text',
                    required:true
                },
                {
                    label: 'Notification Emails',
                    field: 'notification_email',
                    value: '',
                    type:'text',
                    required:true
                },
                {
                    label: 'Password',
                    field: 'password',
                    value: '',
                    type:'password'
                },
                {
                    label: 'Confirm Password',
                    field: 'confirm_password',
                    value: '',
                    type:'password'
                }
            ],
            reg: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/
        }
    },
    methods:{
        editProfile(){
            const { error, vals, $store } = this
            Object.keys(error).forEach(f =>  {
                if(f == 'password' || f == 'confirm_password' ){
                    error[f] = false
                }
                else {
                    error[f] = !vals[f] ? 'This field is required' : false
                }
            });
            if( this.vals.email!= '' &&  !this.reg.test(this.vals.email)) error.email = 'Please enter a valid email'
            if( this.vals.notification_email != '' ){
                let emailArray = this.vals.notification_email.split(",")
                emailArray.forEach( email =>{
                    if(!this.reg.test(email)) error.notification_email = 'Please enter a valid email'
                })
            }
            if ( this.vals.password != '' || this.vals.confirm_password !=''){
                if(this.vals.password != this.vals.confirm_password){
                    error.confirm_password = error.password = 'Passwords doesn\'t match'
                } 
                else {
                    if(this.vals.password.length < 6){
                        error.password = 'Your password must contain more than 6 characters.'
                    }
                }
            }
            if(this.vals.cell_phone !=null &&  this.vals.cell_phone != '') {
                if(this.checkPhone(this.vals.cell_phone)){
                    error.cell_phone = 'Please enter a valid cell phone number'
                }
            }
            const hasError = Object.values(error).some(e=>e)
            if(hasError) return
            const post = {...vals}
            this.loading = true
            $store.dispatch('api/call',{
                url: 'update-investor',
                post
            }).then(r=>{
            if(r.status){
                $store.dispatch('init/alert',{
                type: 'success',
                message: r.message || 'Profile edited successfully!'
                })
                $store.dispatch('init/refresh')
            }
            }).catch(e=>{
            $store.dispatch('init/alert',{
                type:'warning',
                message: e.msg || e.message || 'Something Went Wrong!'
            })
            }).finally(()=>{
            this.loading = false
            this.$emit('close')
            })
        },
        loadProfile(){
            this.loading = true
            this.$store.dispatch('api/getData',{
                force: true,
                method:'get',
                url: '/edit-investor',
                field: 'loadprofile',
                handler: this.assign
            })
            .catch(e => {})
        },
        assign(data){
            if(data.status) this.loading = false
            if(data.data){
               let  { cell_phone , email , name , notification_email } = data.data
               this.vals.cell_phone = cell_phone
               this.vals.email = email
               this.vals.name = name
               this.vals.notification_email = notification_email
            }
        },
        checkPhone(number){
            let e = number.replaceAll(/\s/g,'')
            if(/^\([0-9]{3}\)[0-9]{3}-[0-9]{4}$/.test(e)){
                return false
            }
            else return true
        },
        copy(f){
            if(f == 'email'){
                if (this.vals.notification_email == '' ) this.vals.notification_email = this.vals.email 
            }
        }
    },
    created(){
        this.loadProfile()
    }
}
</script>
<style 
    scoped
    src="~v/edit-profile.scss"
    lang="scss"
>
</style>