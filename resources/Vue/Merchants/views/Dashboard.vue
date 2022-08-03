<template>
  <div class="view-wrapper">
    <preloader v-if="combineLoading && !showIframe" />
    <emptyBox v-if="error" :msg="errorMsg" />
    <section class="dashboard-row" v-if="data && !error">
		<div class="iframe-wrapper" v-if="showIframe">
			<div class="close" @click="closeIframe">
              <img :src="require('@image/icons/close-icon.svg').default" alt="">
			</div>
			<iframe :src="iframeLink" frameborder="0" @load="iframeIsLoaded" ></iframe>
		</div>
		<div v-if="!combineLoading" class="strech-row">
			      <div class="col overview">
        <div class="content-box">
          <!-- page title -->
          <header class="page-title-sec">
            <h1 class="title">
              Dashboard
            </h1>
            <div class="controls">
                <button class="btn blue" 
					@click="payoffAlert()"
				> 
					{{ paymentRequestedText }} 
				</button>
                <button class="btn blue" 
					@click="moneyRequestAlert()"
				>
					{{ moneyRequestedText }}
				</button>
            </div>
          </header>
          <!-- overview row -->
          <section class="info-row">
            <div class="col full">
              <div class="content-box bg no-min">
                <investmentInfo :data="data" />
              </div>
            </div>
          </section>
          <!-- graph row -->
          <section class="info-row graph">
            <div class="col left">
              <div class="content-box bg">
                <graph :data="data" />
              </div>
            </div>
            <div class="col right">
                <ratesBox :data="data" />
            </div>
          </section>
        </div>
      </div>
      <!-- payment info col -->
      <div class="col payments" v-if="latest_payments">
        <div class="content-box overflow">
          <paymentInfo>
            <template v-slot:body>
              <transactions
                v-if="data && latest_payments"
                :data="latest_payments"
                title="Latest Payments"
              >
                  <template v-slot:action>
                      <router-link
                        to="/merchants/reports"
                        class="view-all-bt"
                      >View All</router-link>
                  </template>
              </transactions>
            </template>
          </paymentInfo>
        </div>
      </div>
		</div>
    </section>
    <alert
      :toggle="popupToggle"
      :money="requestMoney"
	  :confirm="confirm"
	  :confirmText="confirmText"
      @cancel="closeAlert"
      @submit="submitRequest"
	  @confirmAction="confirmPayoffRequest"
      v-if="popupToggle"
    />
  </div>
</template>

<script>
	import graph from '@merchantComponents/dashboard/graph'
	import investmentInfo from '@merchantComponents/dashboard/investmentInfo'
	import ratesBox from '@merchantComponents/dashboard/ratesBox'
	import paymentInfo from '@merchantComponents/dashboard/paymentInfo'
	import transactions from '@merchantComponents/transactionList/list'
	import { mapGetters, mapState } from 'vuex';
	import emptyBox from '@merchantComponents/emptyBox';
	import alert from './components/alert';
	import {redirect} from '@merchant/helpers/redirect';
	import { ArrayFactory } from '@merchant/helpers/array';
	export default {
		name: 'dashboard-view',
		components:{
			graph,
			investmentInfo,
			paymentInfo,
			ratesBox,
			transactions,
			alert,
			emptyBox
		},
		data(){
			return {
				error: false,
				errorMsg : false,
				toggle:false,
				testMsg:'Message anyone from anywhere with the reliability of texting and the richness of chat.',
				popupToggle:false,
				payoff:false,
				logoutFlag:false,
				requestMoney:false,
				paymentRequestedText:null,
				confirm:false,
				confirmText:'',
				latest_payments:null,
				showIframe:false,
				iframeLoading:false,
				iframeLink:null,
				moneyRequestedText : 'Request more money',
				paymentRequestedText : 'Request payoff'	,
				count:1,
				throttle:null,
				crm:{
					id:null,
					token:null,
				}
			}
		},
		computed:{
		...mapState('api',{
			loading: ({loading}) => loading.header,
			moneyLoading: ({loading}) => loading.requestMoneyField
		}),
		...mapGetters({
			data: 'api/investorDashboard'
		}),
		/**
		 * Disable request payoff button if already requested.
		 * Change the button text accordingly.
		 * @return {Boolean} payment already requested or not.
		 */
		// paymentRequested(){
		// 	if(this.data.merchant.pay_off){
		// 		this.paymentRequestedText = 'Payoff requested'
		// 	}else{
		// 		this.paymentRequestedText = 'Request payoff'
		// 	}
		// 	return Boolean(this.data.merchant.pay_off)
		// },
		/**
		 * Disable request more money button if already requested.
		 * Change the button text accordingly.
		 * @return {Boolean} money already requested or not.
		 */
		// moneyRequested(){
		// 	if(this.data.merchant.money_request_status){
		// 		this.moneyRequestedText = 'Money requested'
		// 	}else {
		// 		this.moneyRequestedText = 'Request more money'
		// 	}
		// 	return Boolean(this.data.merchant.money_request_status) 
		// },
		combineLoading(){
			let iframeLoading = this.iframeLoading ?? false
			return this.loading || this.moneyLoading || iframeLoading
		}
		},
		created(){
			// this.loadDashboard()
			this.latestPayments()
		},
		methods:{
			/**
			 * @param { Number } amount 
			 * amount to request 
			 */
			submitRequest(amount){
				this.popupToggle=false;
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/requestMoreMoney',
					field: 'requestMoneyField',
					post:{
						amount
					},
				})
				.then(res =>{
					this.crm.id = res.id
					this.crm.token = res.token
					if(res.link){
						this.showIframe = true
						this.iframeLink = res.link
						setTimeout(() => {
							this.observeSuccess()						
						}, 2000 * 60 );
					}
				})
				.catch(e=>{
					const errorMsg = e.msg || 
					e.message || 
					ArrayFactory.errors(e.errors.message) ||
					'Something went wrong! Please try again later'
					this.$store.dispatch('init/alert',{
						type: 'warning',
						message: errorMsg
					})
				})
			},
			requestPayoff(){
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/requestPayOff',
					field: 'requestPayOffField',
				})
				.then(res =>{
					this.popupToggle=false;
					this.loadDashboard();
					this.$store.dispatch('init/payoff',{
						alertMsg:res.message,
						reqSuccess:true,
						showAlert:true
					})
				})
				.catch(e=>{
					this.error =true;
					this.errorMsg = e.msg || 
					e.message || 
					'Something went wrong! Please try again later'
				})
			},
			closeAlert(){
				this.popupToggle=false;
			},
			loadDashboard(){
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/merchant-details',
					field: 'investorDashboard'
				})
				.catch(e=>{
					this.error =true;
					this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
				})
			},
			confirmPayoffRequest(s){
				if(s){
					// throttle
					this.popupToggle=false;
					if(this.throttle){
						clearTimeout(this.timer)
					}
					this.throttle = setTimeout(() => {
						this.requestPayoff()
					}, 200);
				} 
			},
			moneyRequestAlert(){
				this.popupToggle = true
				this.setMoneyAlert()
			},
			payoffAlert(){
				this.popupToggle = true
				this.setPayoffAlert()
			},
			resetAlert(){
				this.confirm = false
				this.requestMoney = false
			},
			setMoneyAlert(){
				this.confirm = false
				this.requestMoney = true
			},
			setPayoffAlert(){
				this.confirm = true
				this.requestMoney = false
				this.confirmText = 'Are you sure you want to request payoff ?'
			},
			latestPayments(){
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/latest-payments',
					field: 'latesPayments',
				})
				.then(res => {
					if(res){
						this.latest_payments = res
					}
				})
			},
			crmSuccess(){
				if(!this.showIframe) return
				if(this.count === 4){
					clearInterval(this.timer)
					this.count = 1
					this.crmTimedOut();
				}	
				this.callCrmApi()
			},
			observeSuccess(){
				this.iframeLoading = true
				if(!this.showIframe) return
				this.callCrmApi();
				this.count = 1
				this.timer = setInterval(() => {
					this.crmSuccess()
					this.count++
				},  1000 * 60);
			},
			iframeIsLoaded(){
				this.iframeLoading = false
			},
			closeIframe(){
				this.showIframe = false
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/call-crm-api',
					field: 'crmapi',
					post:{
						id:this.crm.id,
						token:this.crm.token
					},
				})
				.then(res => {
					if(res && res.status.toLowerCase() == 'failed'){
						this.$store.dispatch('init/payoff',{
							alertMsg:res.message ?? 'failed',
							reqSuccess:false,
							showAlert:true
						})
					}
				})
				.catch( err => {
					this.$store.dispatch('init/payoff',{
						alertMsg:error.errors.message ?? 'Something went wrong',
						reqSuccess:false,
						showAlert:true
					})
				})
			},
			crmTimedOut(){
				this.$store.dispatch('init/payoff',{
					alertMsg:'Request timed out',
					reqSuccess:false,
					showAlert:true
				})
				this.showIframe =false
			},
			callCrmApi(){
				this.$store.dispatch('api/getData',{
					force: true,
					url: '/call-crm-api',
					field: 'crmapi',
					post:{
						id:this.crm.id,
						token:this.crm.token
					},
				})
				.then(res => {
					if(res && res.status.toLowerCase() == 'failed'){
						clearInterval(this.timer)
						this.showIframe = false
						this.iframeLoading = false
						this.$store.dispatch('init/payoff',{
							alertMsg:res.message ?? 'failed',
							reqSuccess:true,
							showAlert:true
						})
						this.loadDashboard();
					}
					if(res && res.status.toLowerCase() == 'success'){
						clearInterval(this.timer)
					}
					else{
						this.showIframe = true
					}
					this.iframeLoading = false
				})
				.catch(error => {
					clearInterval(this.timer)
					this.$store.dispatch('init/payoff',{
						alertMsg:error?.errors?.message ?? 'Something went wrong',
						reqSuccess:false,
						showAlert:true
					})
					this.showIframe = false
					this.iframeLoading = false
				})
			}
		}
	}
</script>

<style
  lang="scss"
  scoped
  src="~merchant/views/dashboard.scss"
></style>