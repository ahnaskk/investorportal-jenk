<template>
  <div id="transaction-view">
      <preloader v-if="loading" />
      <emptyBox v-if="error" :msg="errorMsg" />
      <emptyBox v-if="empty" :msg="emptyMsg" />
      <div
        v-if="transactions && transactions.length"
        class="transaction_wrapper"
      >
        <div
          class="justify-center"
            v-for="(trs,index) in transactions"
            :key="index"
        >
          <transaction
            :count="trs.payment_balance"
            :amount="trs.payment"
            :date="trs.payment_date"
            :rcode="trs.rcode"
          />
        </div>
      </div>
  </div>
</template>

<script>
import transaction from './components/transaction';
import { EventBus } from './bus';
import emptyBox from '@merchantComponents/emptyBox';
import { mapState } from 'vuex';

export default {
    name:'transaction-view',
    data(){
      return {
        endOfContents:false,
        limit:10,
        offset:0,
        error:false,
        errorMsg:'',
        emptyMsg:'No transactions made yet.'
      }
    },
    computed:{
      ...mapState('api',{
        /**
         * These should be initialized in the store/api.js.
         */
        transactions: state => state.transaction,
        loading: ({loading}) => loading.transaction
      }),
      paymentCount(){
        const ar=[];
        if(this.transactions){
          let count=1;
          this.transactions.forEach(el=>{
            if(!el.payment==0){
              ar.push(count);
              count++;
            }else{
              ar.push(0);
            }
          });
        }
        const counts=[];
        let idx=1;
        ar.reverse().forEach(el=>{
          if(el==0){
            counts.unshift(0);
          }else{
            counts.unshift(idx);
            idx++;
          }
        });
        return counts;
      },
      empty(){
        return this.transactions && this.transactions.length < 1
      }
    },
    methods:{
      // loadTransactions(){
      //   this.loading.transactions=true;
      //   this.$api.post('/payments',{
      //     limit:this.limit,
      //     offset:this.offset
      //   },{
      //     headers:this.headers
      //   })
      //   .then(res=>res.data)
      //   .then(data=>{
      //       if(data.status){
      //         if(!this.transactions)
      //         this.transactions=data.data;
      //         else{
      //           if(data.data.length){
      //             this.transactions=this.transactions.concat(data.data);
      //           }else this.endOfContents=true;
      //           // write the end of content indicator here if no more items are available
      //         }
      //         this.offset=this.transactions.length;
      //       }
      //   })
      //   .catch(err=>{
      //     if(err.response && err.response.status===401){
      //       EventBus.$emit('sessionExpired');
      //     }
      //   })
      //   .finally(()=>{
      //     this.loading.transactions=false;
      //   });
      // },
      loadTransactions(){
        this.$store.dispatch('api/getData',{
            force: true,
            url: '/payments',
            field: 'transaction',
            post:{
               limit:this.limit,
            }
        })
        .catch(e=>{
            this.error =true;
            this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
        })
      },
      loadOnScroll(){
        const currentHeight=document.documentElement.scrollTop + window.innerHeight;
        const totalHeight=document.documentElement.offsetHeight;
        if(currentHeight>totalHeight-300){
          if(!this.endOfContents && !this.loading.transactions){
            this.loadTransactions();
          }
        }
      }
    },
    created(){
      this.loadTransactions();
      // window.addEventListener('scroll',this.loadOnScroll);
    },
    beforeDestroy(){
      // store all the datas if the app has to be progressive
      window.removeEventListener('scroll',this.loadOnScroll);
    },
    components:{
        transaction,
        emptyBox
    }
}
</script>
