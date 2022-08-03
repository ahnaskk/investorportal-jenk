<template>
  <div id="transactionView" class="info-box" @click="click">
      <div class="justify-between">
          <h6 class="info-header pr">
            {{ dTitle }}
          </h6>
          <span class="date_sec cl_grey">
              <icon icon="clock" />
              {{ dDate }}
          </span>
      </div>
      <p class="info-content cl_grey">
          {{ dContent }}
      </p>
  </div>
</template>

<script>
export default {
    name:'notification-component',
    computed:{
        dDate(){
            if(this.date) return this.date;
            return '';
        },
        dContent(){
            if(this.content) return this.content;
            return '';
        },
        dTitle(){
            if(this.title) return this.title;
            return '';
        }
    },
    methods:{
        click(e){
            this.$emit('click',e);
        }
    },
    props:{
        date:String,
        content:String,
        title:String
    }
}
</script>