<template>
  <div class="form-outer">
    <form @submit.prevent="submit">
      <div
        class="form-group"
        v-for="(f,i) in fields"
        :key="i"
      >
        <h4 class="label">{{f.label}} <span class="mandatory" v-if="f.mandatory">*</span></h4>
        <input
          class="input"
          v-model="vals[f.field]"
          v-if="f.mask"
          maskChar=""
          @input="fetchBank(f)"
          :mask="f.mask"
        />
        <input
          v-else
          type="text"
          :class="['input',{'read-only':f.readOnly}]"
          v-model="vals[f.field]"
          :readonly="isReadOnly(f)"
        />
        <p class="error-msg" v-if="error[f.field]">{{ error[f.field] }}</p>
      </div>
      <div class="form-group">
        <h4 class="label">Bank Type <span class="mandatory">*</span></h4>
        <div class="input type-input">
          <div class="input-row">
            <div class="col">
              <span class="type">
                <span :class="['check-box',{active: debit }]" @click.stop.prevent="debit = !debit">
                  <icon :icon="['fas','check']" />
                </span>
                <span class="label" @click.stop.prevent="debit = !debit">Debit</span>
              </span>
            </div>
            <div class="col">
              <span class="type">
                <span :class="['check-box',{active: credit }]" @click.stop.prevent="credit = !credit">
                  <icon :icon="['fas','check']" />
                </span>
                <span class="label" @click.stop.prevent="credit = !credit">Credit</span>
              </span>
            </div>
          </div>
        </div>
        <p class="error-msg" v-if="error.type">Please Select The Type of Your Account</p>
      </div>
      <div class="submit-row">
        <button class="blue-bt" v-loader="loading">Add Bank</button>
      </div>
    </form>
  </div>
</template>

<script>
const brnv = require('bank-routing-number-validator')
const axios = require('axios').default
import {isValidAccountNumber} from './validation'
export default {
    name: 'add-bank',
    data(){
      return {
        bankCredit: false,
        bankDebit: false,
        loading: false,
        credit: false,
        debit: false,
        vals:{
          acc_number: '',
          conrirm_acc_number: '',
          account_holder_name: '',
          bank_address: '',
          name: '',
          routing: '',
          type: []
        },
        error:{
          acc_number: false,
          conrirm_acc_number: false,
          account_holder_name: false,
          bank_address: false,
          name: false,
          routing: false,
          type: false
        },
        fields:[
          {
            label: 'The Account Holder\'s Name',
            field: 'account_holder_name',
            value: '',
            mandatory:true
          },
          {
            label: 'Account Number',
            field: 'acc_number',
            value: '',
            mask: '9999999999999999999',
            mandatory:true
          },
          {
            label: 'Confirm Account Number',
            field: 'conrirm_acc_number',
            value: '',
            mask: '9999999999999999999',
            mandatory:true
          },
          {
            label: 'Routing',
            field: 'routing',
            value: '',
            mask: '9999999999',
            mandatory:true
          },
          {
            label: 'Bank Name',
            field: 'name',
            value: '',
            readOnly:true,
            mandatory:true
          },
          {
            label: 'Bank Address',
            field: 'bank_address',
            value: '',
            mandatory:true
          }
        ]
      }
    },
    methods:{
      submit(){
        const { error, vals, $store } = this
        // validate if the field is empty
        Object.keys(error).forEach(f => error[f] = !vals[f] ? 'This field is required' : false);
        if(
          vals.acc_number != vals.conrirm_acc_number
        ) error.conrirm_acc_number = 'Account Numbers doesn\'t match';
        if(!isValidAccountNumber(vals.acc_number)){
          error.acc_number = 'Please enter a valid account number'
        }
        // format bank account type for api request
        ['credit','debit'].forEach(v=>{
          if(this[v] && !vals.type.includes(v)) vals.type.push(v)
          else if(!this[v] && vals.type.includes(v))
            vals.type.splice(
              vals.type.indexOf(v),1
            )
        })
        // validate account type
        error.type = vals.type.length ? false : true
        // validate routing number
        if(vals.routing && !brnv.ABARoutingNumberIsValid(vals.routing.toString().trim()))
          error.routing = 'Please Enter a Valid Routing Number'
        // return if there's any error
        const hasError = Object.values(error).some(e=>e)
        if(hasError) return
        // proceed with the request if there's no error
        const post = {...vals}
        delete post.conrirm_acc_number
        this.loading = true
        $store.dispatch('api/call',{
          url: 'bank-create',
          post
        }).then(r=>{
          if(r.status){
            $store.dispatch('init/alert',{
              type: 'success',
              message: r.message || 'Successfully Added New Bank Account'
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
      fetchBank(field){
        const { vals , error } = this
        if(field?.field == 'routing' ){
          if(!(vals.routing && !brnv.ABARoutingNumberIsValid(vals.routing.toString().trim()))){
            axios.get('https://www.routingnumbers.info/api/data.json',{
              params:{
                rn:vals.routing.trim()
              }
            })
            .then(function(response){
              if(response?.data?.customer_name){
                vals.name = response.data.customer_name
                error.routing = false 
              }
              else{
                if(vals.routing != "" ){
                  error.routing = 'Please Enter a Valid Routing Number'
                  vals.name = ''
                }
                return;
              }
            })
            .catch(function(error){
              console.log(error)
            })
          }
          else{
            if(vals.routing.trim().length > 0){
              error.routing = 'Please Enter a Valid Routing Number'
              vals.name = ''
            }
            else{
              error.routing = false 
            }
            return;
          }
        }
      },
      isReadOnly(f){
        if(f.readOnly !=null ){
          if(f.readOnly == true)
          return true
        } 
        else return false 
      }
    }
}
</script>

<style
    src="~c/banking/sub/addBank.scss"
    scoped
    lang="scss"
></style>