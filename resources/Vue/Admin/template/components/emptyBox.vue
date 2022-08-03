<template>
  <div :class="['warning-container',{idle}]">
    {{ msg ? msg : 'Something went wrong! Please try again later!' }}
  </div>
</template>

<script>
export default {
  name: 'empty-box',
  props:[
    'msg',
    'idle'
  ]
}
</script>

<style lang="scss" scoped>
.warning-container{
  min-height: 400px;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 35px;
  color: #fa5440;
  font-size: 20px;
  &.idle{
    color: #2a4a76;
  }
}
</style>