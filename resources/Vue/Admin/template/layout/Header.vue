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
              :to="link.to"
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
              @click.prevent="notificationMenu = !notificationMenu"
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
              <div class="user-menu" v-if="userMenu">
                <div class="user-details">
                  <icon :icon="['fas','user']" class="avatar-icon" />
                  {{ authData.name }}
                </div>
                <button
                  class="blue-bt sign-out"
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
    data(){
      return {
        navigationList:[
          {
            to: '/',
            name: 'Home'
          },
        ],
        userMenu:false,
        toggle:false,
        notificationMenu:false,
        messageMenu:false
      }
    },
    computed:{
      ...mapState('auth',{
        authData: s => s.authData
      }),
      // ...mapGetters({
        marketplaceFlag: ()=>0,
        notifications: ()=>0,
        notsToRead: ()=>0
      // })
    },
    methods:{
      signOut(){
        this.$store.dispatch('auth/logout');
      },
      toggleNavbar(){
        this.toggle = !this.toggle
      }
    },
    watch:{
      marketplaceFlag:{
        immediate: true,
        handler(to){
          if(to){
            this.navigationList.push({
              to: '/marketplace',
              name: 'Marketplace'
            });
          }
        }
      }
    }
  }
</script>

<style
  lang="scss"
  scoped
  src="~al/header.scss"
></style>