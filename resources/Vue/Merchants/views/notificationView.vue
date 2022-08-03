<template>
  <div id="notification-view">
      <div
        v-if="notifications && notifications.length"
        class="transaction_wrapper"
      >
        <div
          class="justify-center"
            v-for="(not,index) in notifications"
            :key="index"
        >
          <notification
            :content="not.content"
            :date="not.created_at"
            :title="not.title"
            @click="changeReadStatus"
          />
        </div>
      </div>
      <div class="loader flex-center" v-if="loading.notifications">
          <h4>Loading...</h4>
      </div>
  </div>
</template>

<script>
import notification from './components/notification';
import { EventBus } from './bus';
export default {
    name:'notification-view',
    data(){
      return {
        loading:{
          notifications:false
        },
        endOfContents:false,
        notifications:null,
        limit:40,
        offset:0
      }
    },
    methods:{
      changeReadStatus(){
        this.$api.post('/read-update',{},{
          headers:this.headers
        })
        .then(res=>res.data)
        .then(data=>{
          if(data.success){
          }else{
          }
        })
        .catch(err=>{
          if(err.response && err.response.status===401){
            EventBus.$emit('sessionExpired');
          }
        })
        .finally(()=>_);
      },
      loadNotifications(){
        this.loading.notifications=true;
        this.$api.post('/notification-list',{
          limit:this.limit,
          offset:this.offset
        },{
          headers:this.headers
        })
        .then(res=>res.data)
        .then(data=>{
            if(data.status){
              if(!this.notifications)
              this.notifications=data.data;
              else{
                if(!data.data.length)
                  this.endOfContents=true;
                else
                  this.notifications=this.notifications.concat(data.data);
              }
              this.offset+=this.notifications.length;
            }
        })
        .catch(err=>{
          if(err.response && err.response.status === 401){
            EventBus.$emit('sessionExpired');
          }
        })
        .finally(()=>{
          this.loading.notifications=false;
        })
      },
      loadOnScroll(){
        const currentHeight=document.documentElement.scrollTop + window.innerHeight;
        const totalHeight=document.documentElement.offsetHeight;
        if(currentHeight>totalHeight-300){
          if(!this.endOfContents && !this.loading.notifications){
            this.loadNotifications();
          }
        }
      }
    },
    created(){
      this.loadNotifications();
      window.addEventListener('scroll',this.loadOnScroll);
    },
    beforeDestroy(){
      // store all the datas if the app has to be progressive
      window.removeEventListener('scroll',this.loadOnScroll);
    },
    components:{
      notification
    }
}
</script>

<style>

</style>