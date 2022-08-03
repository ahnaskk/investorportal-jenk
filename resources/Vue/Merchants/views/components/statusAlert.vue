<template>
  <div id="status-alert" :class="{active:activate}">
      <div :class="['status-alert',{success}]">
          <span :class="['icon',{success}]">
              <icon icon="check" v-if="success"/>
              <icon icon="times" v-else />
          </span>
          <div class="msgBox">
              {{
                  dMsg
              }}
          </div>
          <div class="actionBox">
              <button
                class="okBt"
                @click="ok"
              >
                  OK
              </button>
              <!-- <button class="cancelBt"></button> -->
          </div>
      </div>
  </div>
</template>

<script>
export default {
    computed:{
        dMsg(){
            if(this.msg){
                return this.msg.toLowerCase();
            }else if(this.success){
                return 'Yuor action has been completed successfully';
            }return 'Something went wrong! Please try again later';
        }
    },
    props:{
        img:Object,
        msg:String,
        actoin:Object,
        cancel:Boolean,
        success:Boolean,
        activate:Boolean
    },
    methods:{
        close(){
            this.$emit('close');
        },
        ok(){
            this.$store.dispatch('init/payoff',{
                showAlert:false
            })
        }
    }
}
</script>