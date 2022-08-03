<template>
  <div class="form-outer">
    <form @submit.prevent="submit">
      <div
        class="form-group"
        v-for="(f,i) in fields"
        :key="i"
      >
        <h4 class="label">{{f.label}}</h4>
        <input
          class="input"
          v-model="vals[f.field]"
          :type="inputType(f)"
          v-if="f.mask"
          :mask="f.mask"
          :maskChar="maskChar(f)"
          :placeholder="placeholder(f)"

        />
        <input
          v-else
          class="input"
          v-model="vals[f.field]"
        />
        <p class="error-msg" v-if="error[f.field]">{{ error[f.field] }}</p>
      </div>
      <div class="form-group">
        <h4 class="label">Bank Type</h4>
        <div class="input type-input">
          <div class="input-row">
            <div class="col">
              <span class="type">
                <span :class="['check-box',{active: debit }]" @click="debit = !debit">
                  <icon :icon="['fas','check']" />
                </span>
                <span class="label" @click.stop.prevent="debit = !debit">Debit</span>
              </span>
            </div>
            <div class="col">
              <span class="type">
                <span :class="['check-box',{active: credit }]" @click="credit = !credit">
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
        <button class="blue-bt" v-loader="loading">Update Bank</button>
      </div>
    </form>
  </div>
</template>

<script>
const brnv = require('bank-routing-number-validator')
import {isValidAccountNumber} from './validation'
export default {
    name: 'add-bank',
    data(){
      return {
        bankCredit: false,
        bankDebit: false,
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
            value: ''
          },
          {
            label: 'Bank Name',
            field: 'name',
            value: ''
          },
          {
            label: 'Account Number',
            field: 'acc_number',
            value: '',
            mask: '9999999999999999999',
          },
          {
            label: 'Confirm Account Number',
            field: 'conrirm_acc_number',
            value: '',
            mask: '9999999999999999999',
          },
          {
            label: 'Routing',
            field: 'routing',
            value: '',
            mask: '9999999999'
          },
          {
            label: 'Bank Address',
            field: 'bank_address',
            value: ''
          }
        ]
      }
    },
    methods:{
      submit(){
        const { error, vals, $store } = this
        // validate
        Object.keys(error).forEach(f => {
          if ( f ==  'conrirm_acc_number' || f == 'acc_number') {
            error[f] = false;
          }
          else{
            error[f] = !vals[f] ? 'This field is required' : false
          }
        });
        if(
          vals.acc_number != vals.conrirm_acc_number
        ) error.conrirm_acc_number = 'Account Numbers doesn\'t match';
        // format bank account type for api request
        ['credit','debit'].forEach(v=>{
          if(this[v] && !vals.type.includes(v)) vals.type.push(v)
          else if(!this[v] && vals.type.includes(v))
            vals.type.splice(
              vals.type.indexOf(v),1
            )
        })
        if(!isValidAccountNumber(vals.acc_number,true)){
          error.acc_number = 'Please enter a valid account number'
        }
        // validate account type
        error.type = vals.type.length ? false : true
        // validate routing number
        if(vals.routing && !brnv.ABARoutingNumberIsValid(vals.routing.toString().trim()))
          error.routing = 'Please Enter a Valid Routing Number'
        // return if there's any error
        const hasError = Object.values(error).some(e=>e)
        if(hasError) return
        // request
        const post = {...vals}
        delete post.conrirm_acc_number
        this.$emit('submit',post)
      },
      inputType(f){
        return f?.type ?? 'text'
      },
      maskChar(f){
        return f?.mask_char ?? " ";
      },
      placeholder(f){
        if(f.field == 'acc_number' || f.field == 'conrirm_acc_number'){
          return this.bank.acc_number
        }
      }
    },
    props:{
        bank: Object,
        loading: [Boolean]
    },
    created(){
        if(this.bank){
            for(const val in this.vals){
              if ( val ==  'conrirm_acc_number' || val == 'acc_number') continue
              this.$set(this.vals,val,this.bank[val] || '')
            }
            this.vals.type.forEach(v=>{
              this[v] = true
            })
            this.vals.conrirm_acc_number = this.vals.acc_number
        }
    }
}
</script>

<style
    src="~c/banking/sub/addBank.scss"
    scoped
    lang="scss"
></style>