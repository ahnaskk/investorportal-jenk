<template>
  <div class="wrapper">
    <preloader
      v-if="loading && !error"
    />
    <emptyBox
      :msg="errorMsg"
      v-if="error"
    />
    <ul class="list" v-if="notifications && !error && !loading">
      <li
        v-for="(n,i) in notifications"
        :key="i"
        @click="read(n)"
      >
        <div class="icon">
          <div class="icon-tray">
            <icon :icon="['fas','bell']" />
            <div class="indicator" v-if="!n.read_status"></div>
          </div>
        </div>
        <div class="head">
          <h4>{{ n.title }}</h4>
        </div>
        <div class="body">
          <span>{{ n.content }}</span>
        </div>
        <div class="timestamp">
          <span>{{ n.created_at }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import { mapState } from 'vuex'
  export default {
    name: 'notifications-view',
    data(){
      return {
        error: false,
        errorMsg: 'Something went wrong. Please try again later'
      }
    },
    computed:{
      ...mapState('api',{
        notifications: s => s.notifications,
        loading: s => s.loading.notifications
      })
    },
    methods:{
      read(n){
        this.$store.dispatch('api/call',{
          url: '/read-update',
          post:{
            id: n.id
          }
        }).then(r=>{
          if(r.status){
            n.read_status = 1
          }
        })
      },
      on_create(){
        if(!this.loading && !this.notifications){
          const post = {};
          this.$store.dispatch('api/getData',{
            force: true,
            post,
            url: '/notification-list',
            field: 'notifications'
          });
        }
      }
    }
  }
</script>

<style
  lang="scss"
  scoped
  src="~v/notifications.scss"
></style>