<template>
  <div id="content_header" class="align-center justify-between">
      <div class="left">
            <h1 class="ch_title t-cpt">{{ contentTitle }}</h1>
            <breadcrumbs />
       </div>
       <div class="date_sec align-center">
           <icon icon="clock" />
           <span class="ml-10">{{ date || '' }}</span>
       </div>
  </div>
</template>

<script>
export default {
    name:'content-header',
    props:{
        title:String,
        date:String
    },
    computed:{
        contentTitle(){
            if(this.title) return this.title;
            else return '';
        }
    }
}
</script>

<style lang="scss">
    #content_header{
        margin: 0 0 25px 0;
    }
    .ch_title{
        font-size: 20px;
        font-weight: 700;
        color: #4f526b;
        margin: 0 0 3px;
    }
    .breadcrumb{
        margin: 0;
        font-size: 12px;
        color: #d88681;
        &.active{
            color: #6c757d;
        }
    }
    .date_sec{
        color: #858c9a;
        font-size: 14px;
    }
</style>