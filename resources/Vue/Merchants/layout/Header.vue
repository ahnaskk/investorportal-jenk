<template>
  <header class="app-header">
    <!-- logo box -->
    <div class="logo-box">
      <router-link to="/">
        <img
          :src="require('@image/icons/velocity_logo_lg.png').default"
          alt="velocity logo"
        />
      </router-link>
      <div class="mobile hidden" @click="toggleNavbar">
        <icon :icon="['fa','bars']" class="avatar-icon" />
      </div>
    </div>
    <div class="collapse" v-bind:class="{active:toggle}">
      <!-- navigation -->
      <div class="nav-box">
        <ul class="nav-list">
          <li
            class="nav-item"
            v-for="(link,i) in navigationList"
            :key="i"
          >
            <router-link
              class="nav-link"
              v-if="link.submenu && link.to"
              :to="link.to"
            >
              {{ link.name }}
              <ul v-if="link.submenu" class="submenu">
                <li
                  v-for="(sl,si) in link.submenu"
                  :key="si+0.1"
                >
                  <router-link
                    :to="sl.to"
                    class="nav-link"
                    :class="{last: si == link.submenu.length-1}"
                  >
                    {{ sl.name }}
                  </router-link>
                </li>
              </ul>
            </router-link>
            <router-link
              class="nav-link"
              :to="link.to"
              v-if="!link.submenu && !link.anchorTag && link.to"
            >
              {{ link.name }}
            </router-link>
			<a
                v-else-if="link.anchorTag && link.to"
                :href="link.to"
                class="nav-link" 
                target="_blank"
            >
            	{{link.name}}
            </a>
			<a v-else href="javascript:void(0)" @click.prevent="clickAction(link.action)">
				{{link.name}}
			</a>
          </li>
        </ul>
      </div>
      <!-- search box -->
      <div class="search-box">
        <form @submit.prevent>
          <div class="input-group" @click="$refs.search.focus()">
            <div class="lens">
              <img :src="require('@image/icons/search.svg').default" alt="">
            </div>
            <span class="divider"></span>
            <input
              ref="search"
              type="text"
              placeholder="Search keyword"
              class="search-input"
            />
          </div>
        </form>
      </div>
      <!-- quick nav icons -->
      <div class="quick-menu-box">
        <ul class="quick-list">
          <li class="quick-item"
            v-click-outside ="() => messageMenu = false"
            v-if="0"
          >
            <a
              href="#"
              class="quick-link"
              @click.prevent="() => messageMenu = !messageMenu"
            >
              <icon :icon="['fas','envelope']" />
                <div class="notification-area" :class="{expand:messageMenu}">
                  <ul>
                    <div class="actions">
                      <span>Messages</span>
                        <router-link :to="'/messages'">View all</router-link>
                    </div>
                    <li>
                      <span class="wrapper">
                        <!-- <img src="" alt="" /> -->
                      </span>
                      <span>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Nisi doloribus, la</span>
                    </li>
                  </ul>
              </div>
            </a>
          </li>
          <li class="quick-item" 
            v-click-outside ="() => notificationMenu = false"  
            v-if="notifications && notifications.length"
          >
            <button
              class="quick-link"
              type="button"
              @click.prevent="clickNotIcon"
            >
              <div class="icon-tray">
                <icon :icon="['fas','bell']" />
                <div v-if="notsToRead" class="indicator"></div>
              </div>
              <div
                class="notification-area"
                :class="{expand:notificationMenu}"
              >
                <ul>
                  <div class="actions">
                    <span>Notifications</span>
                    <router-link :to="'/notifications'">View all</router-link>
                  </div>
                  <li
                    v-for="(n,i) in notifications"
                    :key="i+0.1"
                  >
                    <span class="wrapper">
                      <!-- <img src="" alt="" /> -->
                    </span>
                    <span>{{ n.content }}</span>
                  </li>
                </ul>
                <div class="clear">
                  <div class="wrapper" @click="clearNotification">
                    <icon :icon="['fas','times']" class="avatar-icon" />
                    <button type="button">Clear All</button>
                  </div>
                </div>
            </div>
            </button>
          </li>
          <li
            class="quick-item"
            v-click-outside="()=>userMenu=false"
          >
            <a
              href="#"
              class="quick-link"
            >
              <span
                class="icon-wrapper"
                @click.prevent="userMenu = !userMenu"
              >
                <icon :icon="['fas','user']" />
              </span>
              <div class="user-menu" :class="{active: userMenu}">
                <div class="user-details">
                 
                    <icon :icon="['fas','user']" class="avatar-icon" />
                    {{ authData.name }}
                </div>
                 <!-- <router-link to="/edit-profile" class="blue-bt edit" @click.native="userMenu = !userMenu">
                  Edit
                </router-link> -->
                <router-link to="/merchants/two-factor" class="blue-bt edit  tf" @click.native="userMenu = !userMenu">Two-Factor Authentication</router-link>
                <button
                  class="blue-bt sign-out edit"
                  @click="signOut"
                >
                  Sign Out
                </button>
              </div>
            </a>
          </li>
        </ul>
      </div>
      <!-- mobile menu -->
      <div class="close-row">
          <button class="close-btn" @click="toggleNavbar">
              <img :src="require('@image/icons/close-icon.svg').default" alt="">
          </button>
      </div>
    </div>
  </header>
</template>

<script>
  import { mapState, mapGetters } from 'vuex';
  export default {
	name: 'layout-header',
	props:{
		merchants:{
			default:null
		}
	},
    data(){
      return {
        userMenu:false,
        toggle:false,
        notificationMenu:false,
        messageMenu:false,
      }
    },
    computed:{
		...mapState('auth',{
			authData: s => s.authData,
		}),
		...mapState('api',{
			paymentAmount: s=> s.header
		}),
		...mapGetters({
			// marketplaceFlag: 'auth/marketplaceFlag',
			notifications: 'api/notificationsMiniList',
     		merchantID:'auth/merchantID',
      		hasTransactions:'api/hasTransactions'
			// notsToRead: 'api/notsToRead',
		}),
     	navigationList(){
       		let navItems = [
            {
              to: '/merchants/dashboard',
              name: 'Dashboard'
            },
            {
              to: '/merchants/requests',
              name:'Requests'
            },
            {
              to: '/merchants/faq',
              name: 'FAQ'
            }
			]
			if(this.hasTransactions){
				navItems.splice(1,0,{
             	to: '/merchants/transactions',
              	name: 'Transaction'
            })}
			if(this.merchants && this.merchants.length > 1){
				navItems.splice(navItems.length -1 , 0 , {
					name: 'Switch Account',
					action:'switchAccount'
				})
			}
			if(this.paymentAmount && this.paymentAmount.investor_data.length){
				let paymentAmount = 0
				if(typeof(this.paymentAmount.current_payment_amount) == 'string'){
					let amount = this.paymentAmount.current_payment_amount.replace(/[$,]/g,'')
					paymentAmount = amount 
				}else{
					paymentAmount = this.paymentAmount.current_payment_amount
				}
				if( Boolean(this.paymentAmount.add_payment_permission) && paymentAmount > 0){
					navItems.splice(navItems.length-1,0,{
						name: 'Make Payment',
						icon: 'money-check-alt',
						to: `/pm/${this.merchantID}/make-payment/${paymentAmount}`,
						anchorTag: true
					})
				}
			}
			return navItems
    	},
    },
    methods:{
      clickNotIcon(){
        window.innerWidth > 990 ?
        this.notificationMenu = !this.notificationMenu :
          this.$router.push({path: '/notifications'})
          .catch(e=>e)
      },
      clearNotification(){
        this.$store.dispatch('api/getData',{
          force: false,
          url: '/clear-notifications',
          field: 'clearNotifications',
          handler: this.notificationHandler
        })
        .catch(e => {
        })
      },
      signOut(){
        this.$store.dispatch('auth/logout');
      },
      toggleNavbar(){
        this.toggle = !this.toggle
      },
      notificationHandler(e){
        console.log(e)
        if(e.status){
          this.$emit('refresh')
        }
	  },
	  clickAction(action){
		switch(action){
			case 'switchAccount':
				this.$store.dispatch('init/popup',{
					switchAccount:{
						show:true
					}
				})
			break;
		}
	  }
    },
	created(){
		this.$store.dispatch('api/getData',{
          force: true,
          url: '/merchant-details',
          field: 'header'
        })
        .catch(e=>{
          this.error =true;
          this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
		})
		this.$store.dispatch('api/getData',{
			force: true,
			url: '/merchant-details',
			post:{
				limit:10
			},
			url: '/payments',
			field: 'transaction',
        })
        .catch(e=>{
          this.error =true;
          this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
        })
        
    }
    
  }
</script>

<style
  lang="scss"
  scoped
  src="~merchant/layout/header.scss"
></style>