<template>
  <div id="dashboard-view">
      <dashboardHeader
        v-if="merchantDetails"
        :name="merchantDetails.merchant.name" 
        :payoffRequested="merchantDetails.merchant.pay_off" 
        :moneyRequested="merchantDetails.merchant.money_request_status" 
        :funded="{
          amount:merchantDetails.merchant.funded,
          date:merchantDetails.merchant.date_funded
        }"
        :loading="passLoading"
        :factorRate="merchantDetails.merchant.factor_rate"
        :totalPaymentsCount="merchantDetails.merchant.pmnts"
        :paymentsLeftCount="merchantDetails.merchant.actual_payment_left"
      />
      <!-- :lender="merchantDetails.merchant.lendor.name" -->
      <dashboardContent
        v-if="merchantDetails"
        :totalPaymentsAmount="merchantDetails.current_payment_amount"
        :balance="merchantDetails.balance_merchant"
        :rtr="merchantDetails.merchant.rtr"
      />
      <div class="loader flex-center" v-if="loading.merchantDetails">
          <h4>Loading...</h4>
      </div>
  </div>
</template>

<script>
import dashboardHeader from './components/dashboardHeader';
import dashboardContent from './components/dashboardContent';
import { EventBus } from './bus';
export default {
  name:'dashboard-view',
  data(){
    return {
      loading:{
        merchantDetails:false
      },
      merchantDetails:null
    }
  },
  computed:{
    token(){
      return this.$store.getters.merchantToken;
    }
  },
  components:{
      dashboardHeader,
      dashboardContent
  },
  methods:{
    loadMerchantDetails(){
      // return if the auth token is not set in the store
      if(!this.token) return;
      const token=this.token;
      this.loading.merchantDetails=true;
      this.$api.post('/merchant-details',{},{ headers : this.headers })
        .then(res=>res.data)
        .then(data=>{
          if(data.status){
            this.merchantDetails=data.data;
            const id = data.data && data.data.merchant ? data.data.merchant.encrypted_id : null;
            this.$store.dispatch('saveMerchantID',id);
            EventBus.$emit('paymentAmount', data.data.current_payment_amount)
          }
        })
        .catch(err=>{
          if(err.response && err.response.status===401){
            EventBus.$emit('sessionExpired');
          }
        })
        .finally(()=>{
          this.loading.merchantDetails=false;
        });
    }
  }, 
  mounted(){
    this.loadMerchantDetails();
    // Listen for events
    // on success of request more money
    EventBus.$on('paymentRequestSuccess',()=>{
      this.$merchantDetails.merchant.money_request_status=1;
    });
    // on success of request payoff
    EventBus.$on('payoffRequestSuccess',()=>{
      this.$merchantDetails.merchant.pay_off=1;
    });

  },
  props:{
    passLoading:Object
  }
}
</script>

<style>

</style>