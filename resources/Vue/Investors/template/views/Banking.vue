<template>
  <section class="dashboard-row">
    <preloader v-if="combinedLoading" />
    <section class="banking-row" v-else>
      <div class="col actions">
        <div class="banks-row">
          <div
            class="bank-col"
            v-for="(bank,i) in data.data"
            :key="i"
          >
            <bank
              :bank="bank"
              :liquidity="data.liquidity"
            />
          </div>
          <div class="bank-col">
            <bank empty />
          </div>
        </div>
      </div>
      <div class="col overview" v-if="transactions">
        <div class="content-box overflow">
          <paymentInfo :bank="true">
            <template v-slot:body>
              <transactions
                title="Payout Frequency:"
                :data="transactions"
                :bank="hasBank"
              >
                <template v-slot:action>
                  <div class="action-wrapper">
                    <div
                      class="info-icon"
                    >
                      ?
                      <div class="tip"></div>
                    </div>
                    <div class="tooltip">
                      Please select how often you would like to receive your payouts
                    </div>
                    <div
                      class="drop-wrapper"
                      title="Please select how often you would like to receive your payouts:"
                    >
                      <filterButton
                        settings
                        :title="payFrequency.label"
                        @click="showDrop = !showDrop"
                        v-click-outside="()=>showDrop = false"
                        v-loader="loading.payFrequency"
                      />
                      <div class="list" v-if="showDrop">
                        <ul>
                          <li
                            v-for="(m,i) in payoutOptions"
                            :key="i"
                            @click="changePayFrequency(m)"
                          >
                            <span
                              class="content"
                              type="button"
                            >
                              {{ m.label }}
                            </span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </template>
              </transactions>
            </template>
          </paymentInfo>
        </div>
      </div>
    </section>
  </section>
</template>

<script>
  import { mapState } from 'vuex'
  import paymentInfo from '@c/dashboard/paymentInfo'
  import transactions from '@c/transactionList/list'
  import filterButton from '@c/filterButton'
  import tooltip from '@c/tooltip'
  import bank from '@c/banking/bank'
  export default {
    name: 'banking-view',
    data() {
      return {
        error: false,
        errorMsg: null,
        active: false,
        completed: true,
        payFrequency:{
          label: 'Monthly',
          value: 'monthly'
        },
        selectedOption:null,
        payoutOptions:[],
        transactions: null,
        showDrop: false,
        loading:{
          transactions: false,
          payFrequency: false
        }
      }
    },
    computed: {
      hasBank(){
        return this.data.data.length > 0 
      },
      ...mapState('api', {
        data: s => s.banks,
        dataLoading: (s) => s.loading.banks
      }),
      combinedLoading(){
        return this.dataLoading || this.loading.transactions
      }
    },
    methods: {
      changePayFrequency(f){
        console.log(f)
        if(f.value == this.payFrequency.value || this.loading.payFrequency) return
        this.$store.dispatch('init/prompt',{
          type: 'binary',
          message:'Are you sure you want to change the Payout Frequency?'
        })
        .then(r=> r ? changeFQ.call(this) : '')
        .finally(()=>this.showDrop = false)
        // request
        function changeFQ(){
          this.loading.payFrequency = true
          this.$store.dispatch('api/call',{
            url: '/update-user',
            post: {notification_recurence:f.value}
          }).then(r=>{
            if(r.status){
              this.payFrequency = f
            }else{
              this.$store.dispatch('init/alert',{
                type: 'warning',
                message: r.errors.message
              })
            }
          }).catch(e=>{
            // this.$store.dispatch('init/alert',{
            //   type: 'warning',
            //   message: e.message || 'Something went wrong!'
            // })
          }).finally(()=>{
            // stop loading here
            this.payFrequency = f
            this.loading.payFrequency = false
          })
        }
      },
      getTransactions(){
        let data
        const tmp = [
          {
            amount: '$230.00',
            merchant_name: '9415merchant',
            payment_date: '11-28-2020',
            type: 1
          },
          {
            amount: '$230.00',
            merchant_name: '9415merchant',
            payment_date: '11-28-2020',
            type: 0
          },
          {
            amount: '$230.00',
            merchant_name: '9415merchant',
            payment_date: '11-28-2020',
            type: 1
          },
          {
            amount: '$230.00',
            merchant_name: '9415merchant',
            payment_date: '11-28-2020',
            type: 1
          }
        ];
        this.loading.transactions = true
        this.$store.dispatch('api/call',{
          url: '/transaction-report',
          post:{
            limit:10,
            offset:0,
            sort_by:"date",
            sort_order:"0"
          },
          handler:r=>{
            console.log(r)
            return r
          }
        }).then(r=>{
          if(r.status){
            data = r.data.data.map(item => {
              item["merchant_name"] = item.transaction_category
              item["type"] = item.transaction_type ==2  ? 1 : 0
              if(parseInt(item.amount.replace("$","")) < 0 ){
                //item["amount"] = item.amount.replace("-","") 
                item["amount"] = item.amount
              }
              item["payment_date"] = item.date
              return item
            });
            this.$set(this,'transactions',data)
            this.payoutOptions = r.data.recurrence_types
            this.selectedOption = r.data.notification_recurence
            this.payFrequency = r.data.recurrence_types.find(option => {
              return option.value == r.data.notification_recurence
            })
          }
        }).catch(e=>console.log(e))
        .finally(()=>{
          this.$set(this,'transactions',data)
          this.loading.transactions = false
        })
      },
      on_create() {
        const post = {};
        this.$store.dispatch('api/getData',{
          url:'/banks',
          post,
          field: 'banks',
          handler(d){
            d.data  = d.data ? {
              data: d.data,
              liquidity: d.liquidity
            } : d.data
            return d
          }
        })
        .catch(e=>{
          if(e.message) this.errorMsg = e.message
          if(e.msg) this.errorMsg = e.msg
          this.error = true
        })
        this.getTransactions()
      }
    },
    components:{
      paymentInfo,
      transactions,
      filterButton,
      bank,
      tooltip
    }
  }
</script>

<style
  lang="scss"
  scoped
  src="~v/banking.scss"
></style>