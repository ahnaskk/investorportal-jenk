<template>
  <div id="message-component">
      <!-- title section -->
      <div class="title_sec justify-between">
          <h3 class="title msg_title">
              {{ dTitle }}
          </h3>
          <p class="date msg">
              {{ dDate }}
          </p>
      </div>
      <!-- message section -->
      <p class="message_text msg">
          {{ dMessage }}
      </p>
  </div>
</template>

<script>
export default {
    name:'message-component',
    computed:{
        // d stands for 'display' eg: displayTitle
        dTitle(){
            if(this.title){
                return this.title;
            }return '';
        },
        dDate(){
            if(this.date){
                return this.date;
            }return ''
        },
        dMessage(){
            if(this.message){
                return this.message;
            }return ''
        }
    },
    props:{
        title:String,
        date:String,
        message:String
    }
}
</script>

<style>

</style>