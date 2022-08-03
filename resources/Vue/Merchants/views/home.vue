<template>
  <div id="home">
      <!-- <HeaderComponent
        :notificationCount="notificationCount"
      /> -->
      <div class="contents">
          <!-- Sidebar menu -->
          <SideMenu
            :key="updateMenu"
            :menu="menu"
            :toggle="toggle"
            @logout="logout"
          />
          <!-- main contents -->
          <div class="right">
            <ContentHeader
                :title="$route.meta.breadcrumb"
                :date="date"
            />
            <!-- router view port -->
            <div class="router-view-container pr">
                <router-view
                    :passLoading="{
                        money:loading.submitRequest,
                        payoff:loading.requestPayoff
                    }"
                />
            </div>
          </div>
      </div>
      <div class="switch-account-merchats-list" v-if="showMerchantList">
          <div class="select-box" v-click-outside="()=>showMerchantList = false">
              <ul>
                <li v-for="(item,i) in merchants" :key="i+0.1">
                    <button
                        href="javascript:void(0)"
                        @click.prevent="switchAccount(item)"
                        v-loader="item.loading"
                        class="account-switch-bt"
                        type="button"
                    >
                        {{ item.name }}
                    </button>
                </li>
              </ul>
          </div>
      </div>
      <alert
        :toggle="popupToggle"
        :logout="logoutFlag"
        :money="requestMoney"
        @cancel="closeAlert"
        @submit="submitRequest"
        @logout="logout"
        v-if="popupToggle"
      />
  </div>
</template>

<script>
import alert from './components/alert';
// import HeaderComponent from './components/headerComponent';
import ContentHeader from  './components/contentHeader';
import SideMenu from './components/sideMenu';
import { EventBus } from './bus';
const switchLink = {
    title: 'Switch Account',
    icon: 'exchange-alt',
    emit: 'changeAccount',
    hidden: true
};
export default {
    name:'home-view',
    data(){
        return {
            paymentAmount:0,
            showMerchantList: false,
            updateMenu: 0,
            menu:[
                {
                    title: 'Dashboard',
                    icon:'desktop',
                    link:'/merchants'
                },
                {
                    title: 'Transaction',
                    icon:'user-tie',
                    link:'/merchants/transactions'
                },
                // {
                //     title: 'Notification',
                //     icon:'bell',
                //     link:'/merchants/notifications'
                // },
                switchLink,
                {
                    title:'Authentication',
                    link:'/merchants/two-factor',
                    icon:'sign-out-alt',
                },
                {
                    title: 'Logout',
                    icon:'sign-out-alt',
                    emit:'logout'
                },
            ],
            toggle:false,
            testMsg:'Message anyone from anywhere with the reliability of texting and the richness of chat.',
            popupToggle:false,
            payoff:false,
            logoutFlag:false,
            requestMoney:false,
            loading:{
                submitRequest:false,
                requestPayoff:false
            },
            notificationCount:0
        }
    },
    computed:{
        date(){
            const nd=new Date();
            const day=nd.getDate();
            const year=nd.getFullYear();
            const month=nd.getMonth()+1;
            return `${
                month < 10 ? '0'+ month : month 
                }/${
                 day < 10 ? '0' + day : day
                }/${year}`;
        },
        merchants(){
            const merchants = this.$store.getters.merchants;
            return merchants && merchants.merchants && merchants.merchants.length ? merchants.merchants : null;
        },
        merchantID(){
            return this.$store.getters.merchantID
        }
    },
    components:{
        ContentHeader,
        SideMenu,
        alert
        // just for testing
    },
    methods:{
        toggleAccountSelector(){
            console.log('event emitted');
            this.showMerchantList = !this.showMerchantList
        },
        switchAccount(m){
            const id = m.id;
            this.$set(m,'loading',true);
            this.$api.post('/change-merchant',{
                merchant_id: id
            },{
                headers: this.headers
            }).then(d=>{
                if(d.status==200) return d.data;
            }).then(d=>{
                if(d.status){
                    window.location = window.location;
                }else{
                    console.log('show the error message: home.vue > 156')
                }
            }).catch(e=>{
                console.log('error when trying to switch the account');
            })
            .finally(()=>{
                m.loading = false;
            })
        },
        loadNotificationCount(){
            this.$api.post('/notification-count',{},{
                headers:this.headers
            })
                .then(res=>res.data)
                .then(data=>{
                    if(data.status){
                        this.notificationCount=data.count;
                    }else{
                        this.notificationCount=0;
                    }
                })
                .catch(err=>{
                    if(err.response && err.response.status===401){
                        EventBus.$emit('sessionExpired');
                    }
                })
                .finally(()=>{
                    
                });
        },
        logout(confirm){
            if(confirm){
                // console.log('logging out at home.vue line:110');
                // this.$api.post('/logout',{},{
                //     headers:{
                //         authorization:"Bearer "+this.$store.getters.merchantToken
                //     }
                // })
                // .then(res=>res.data)
                // .then(data=>{
                //     if(data.status){
                        this.$cookies.remove('merchant-token');
                        EventBus.$emit('loggedOut');
                        if(this.$route.path != '/merchants'){
                            this.$router.go({
                                path:'/merchants'
                            })
                        }
                        // console.log('confirm the workflow of logout @ home 143');
                //     }else{
                //         console.log('confirm the workflow of logout @ home 143');
                //         // EventBus.$emit('loggedOut');
                //     }
                // })
                // .catch(err=>{
                //     if(err.response && err.response.status===401){
                //         EventBus.$emit('sessionExpired');
                //     }
                // })
                // .finally();
                // this.popupToggle=false;
                // // EventBus.$emit('loggedOut');
                // return;
            }
        },
        closeAlert(){
            this.popupToggle=false;
        },
        // request more money
        submitRequest(amount){
            this.loading.submitRequest=true;
            this.$api.post('/requestMoreMoney',{
                amount
            },{
                headers:this.headers
            })
                .then(res=>res.data)
                .then(data=>{
                    if(data.status){
                        EventBus.$emit('paymentRequestSuccess','Payment request Has been send successfully');
                    }else{
                        EventBus.$emit('paymentRequestFailed','Payment request is not complete. Please try again later');
                    }
                })
                .catch(err=>{
                    if(err.response && err.response.status===401){
                        EventBus.$emit('sessionExpired');
                    }
                })
                .finally(()=>{
                    this.loading.submitRequest=false;
                });
            this.popupToggle=false;
        },
        requestPayoff(){
            this.loading.requestPayoff=true;
            this.$api.post('/requestPayOff',{},{
                headers:this.headers
            }).then(res=>res.data)
            .then(data=>{
                if(data.status){
                    EventBus.$emit('payoffRequestSuccess','Payoff requested succesfully');
                }else{
                    EventBus.$emit('payoffRequestFailed','Payoff request failed!');
                }
            })
            .catch(err=>{
                if(err.response && err.response.status===401){
                    EventBus.$emit('sessionExpired');
                }
            })
            .finally(()=>{
                this.loading.requestPayoff=false;
            })
        }
    },
    mounted(){
        this.loadNotificationCount();
        EventBus.$on('paymentAmount',(amount)=>{
            if(amount) {
                if(typeof(amount) == 'string'){
                    amount = amount.replace(/[$,]/g,'')
                    this.paymentAmount = amount 
                }else{
                    this.paymentAmount = amount
                }
            }
        })
        // toggle menu view
        EventBus.$on('toggle',()=>{
            this.toggle ?
                this.toggle=false :
                this.toggle=true ;
        }); 
        // request payoff
        EventBus.$on('requestPayoff',()=>{
            this.requestPayoff();
        });
        // request money
        EventBus.$on('requestMoney',()=>{
            // hide logout in the popup
            this.logoutFlag=false;
            // show requstMoney in the popup
            this.requestMoney=true;
            // invoke the popup
            this.popupToggle=true;
        });
        // logout
        EventBus.$on('logout',(flag)=>{
            // hide requstMoney in the popup
            this.requestMoney=false;
            // show logout in the popup
            this.logoutFlag=true;
            // invoke the popup
            this.popupToggle=true;
            console.log(flag)
        });
        // switch account
        EventBus.$on('changeAccount',()=>this.showMerchantList = true);
    },
    watch:{
        $route(to,from){
            if(to){
                if(window.innerWidth<=700){
                    this.toggle ?
                        this.toggle=false : '' ;
                }
            }
        },
        merchants:{
            immediate: true,
            handler(to){
                if(to){
                    switchLink.hidden = false;
                }
            }
        },
        merchantID(to){
            if(to) this.menu.splice(this.menu.length-1,0,{
                title: 'Make Payment',
                icon: 'money-check-alt',
                link: `/pm/${to}/make-payment/${this.paymentAmount}`,
                anchorTag: true
            })
        }
    }
}
</script>