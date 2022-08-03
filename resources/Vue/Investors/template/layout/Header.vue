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
              v-if="link.submenu"
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
              v-else
            >
              {{ link.name }}
            </router-link>
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
              placeholder="Search Keyword"
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
          <!-- <li class="quick-item" 
            v-if="collectionNotes" 
            @mouseover="() => tooltips.collectionNotes = true" 
            @mouseout="() => tooltips.collectionNotes = false"
          >
            <router-link to="/collection-notes" class="quick-link">
              <BaseIcon :name="'collection'" :font="12"/>
            </router-link>
            <nav-tooltip tooltip="Collection notes"  v-show="tooltips.collectionNotes"/>
          </li> -->
          <!-- <li class="quick-item" 
            v-click-outside ="() => notificationMenu = false"  
            v-if="notifications && notifications.length"
          >
            <button
              class="quick-link"
              type="button"
              @click.prevent="clickNotIcon"
            >
              <div class="icon-tray">
                <icon :icon="['fas','bell']" 
                    @mouseover="() => tooltips.notification = true" 
                    @mouseout="() => tooltips.notification = false"
                />
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
            <nav-tooltip tooltip="Notifications"  v-show="tooltips.notification"/>
          </li> -->
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
                @mouseover="() => tooltips.profile = true" 
                @mouseout="() => tooltips.profile = false"
              >
                <icon :icon="['fas','user']" />
              </span>
              <div class="user-menu" :class="{active: userMenu}">
                <div class="user-details">
                 
                    <icon :icon="['fas','user']" class="avatar-icon" />
                    {{ authData.name }}
                </div>
                 <router-link to="/edit-profile" class="blue-bt edit" @click.native="userMenu = !userMenu">
                  Edit
                </router-link>
                <router-link to="/two-factor" class="blue-bt edit  tf" @click.native="userMenu = !userMenu">Two-Factor Authentication</router-link>
                <button
                  class="blue-bt sign-out edit"
                  @click="signOut"
                >
                  Sign Out
                </button>
              </div>
            </a>
            <nav-tooltip tooltip="Profile"  v-show="tooltips.profile"/>
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
  import BaseIcon from '@c/BaseIcon'
  import navTooltip from '@c/navTooltip'

  export default {
    name: 'layout-header',
    components:{
      BaseIcon,
      'nav-tooltip':navTooltip
    },
    data(){
      return {
        userMenu:false,
        toggle:false,
        notificationMenu:false,
        messageMenu:false,
        tooltips:{
          collectionNotes:false,
          profile:false,
          notification:false
        }
      }
    },
    computed:{
      ...mapState('auth',{
        authData: s => s.authData
      }),
      ...mapGetters({
        //  marketplaceFlag: 'auth/marketplaceFlag',
         notifications: 'api/notificationsMiniList',
        notsToRead: 'api/notsToRead',
         collectionNotes:'api/collectionNotes',
        hasFAQ:'api/hasFAQ'
      }),
      navigationList(){
        const FAQ = {
			to: '/faq',
			name: 'FAQ'
        }
        const MARKETPLACE = {
            to: '/marketplace',
            name: 'Marketplace'
        }
        const routes = [
          {
            to: '/dashboard',
            name: 'Dashboard'
          },
          /*{
            to: '/banking',
            name: 'Banking'
          },*/
          {
            name: 'Reports',
            to: '/reports',
            submenu: [
                {
                  to: '/reports/payment-reports',
                  name: 'Payment Report'
                },
                {
                  to: '/reports/investment-reports',
                  name: 'Investment Report'
                },
                {
                  to: '/reports/transaction-reports',
                  name: 'Transaction Report'
                },
                /*{
                  to: '/reports/default-rate-merchant-reports',
                  name: 'Default Rate Report'
                }*/
            ]
          },
          {
            to: '/statements',
            name: 'Statements'
          },
          {
            to: '/merchants',
            name: 'Merchants'
          },
         /* {
            to: '/analytics',
            name: 'Analytics'
          }*/
        ]
        if(this.hasFAQ) routes.push(FAQ)
		if(this.marketplaceFlag) routes.splice(-1,0,MARKETPLACE)
        return routes
      }
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
      }
    },
    watch:{
      $route(){
        this.toggle = false;
      }

    }
  }
</script>

<style
  lang="scss"
  scoped
  src="~l/header.scss"
></style>