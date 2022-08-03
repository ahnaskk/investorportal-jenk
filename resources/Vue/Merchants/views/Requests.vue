<template>
    <div class="wrapper">
        <preloader v-if="loading" />
        <emptyBox v-if="empty" :msg="emptyMessage"/>
        <emptyBox v-if="error" :msg="errorMsg"/>
        <div
        v-if="moneyRequests && moneyRequests.length && !loading && !error"
        class="transaction_wrapper"
      >
        <div
          class="justify-center"
            v-for="(trs,index) in moneyRequests"
            :key="index"
        >
          <request
            :status="trs.status"
            :amount="trs.amount"
            :date="trs.date"
          />
        </div>
      </div>
    </div>
</template>
<script>
import { ScrollEvent } from '@merchant/helpers/scroll';
import { ArrayFactory } from '@merchant/helpers/array';
import emptyBox from '@merchantComponents/emptyBox';
import request from '@merchantComponents/requests/request';

let scrollListener = new ScrollEvent
export default {
    data(){
        return {
            empty:false,
            emptyMessage:'No Requests made yet.',
            loading:false,
            moneyRequests:[],
            error:false
        }
    },
    components:{
        emptyBox,
        request
    },
    methods:{
        loadRequests(append = false){
            this.loading = true
            let settings = {
                force: true,
                url: '/merchant-money-requests',
                field: 'merchantRequest',
                post:{
                    limit:10,
                }
            }
            if(append){
                settings.post.offset = this.moneyRequests.length ?? 0
            }else{
                settings.post.offset = 0
            }
            this.$store.dispatch('api/getData',{
                ...settings
            })
            .then(res => {
                if( res && res.length < 1 && !append){
                    this.empty = true
                }
                if(append){
                    if(res && res.length){
                        let instance = new ArrayFactory
                        let newRes = instance.mergeArrays(this.moneyRequests,res)
                        this.moneyRequests = newRes
                    }
                }
                else this.moneyRequests = res
            })
            .catch(e=>{
                this.error =true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
            })
            this.loading = false
        },
        handleScrollHitBottom(e){
            let scroll = new ScrollEvent
            if( e && scroll.windowHitBottom() && !this.loading){
                this.loading = true
                if(this.moneyRequests && this.moneyRequests.length) this.loadRequests(true)
                else this.loadRequests()
                this.loading = false
            }
        }
    },
    created(){
        this.loading = true
        this.loadRequests()
        window.addEventListener('scroll', this.handleScrollHitBottom)
    },
    destroyed(){
        window.removeEventListener('scroll',this.handleScrollHitBottom)
    }
}
</script>
<style scoped>

</style>