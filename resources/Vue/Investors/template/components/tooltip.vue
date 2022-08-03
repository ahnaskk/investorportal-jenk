<template>
  <div class="tooltip-wrapper">
    <icon :icon="['fa','info-circle']" class="avatar-icon" />
    <span class="tooltiptext" :style="`width:${width?width+'px':'auto'}`">{{tooltipText}}</span>
  </div>
</template>

<script>
  export default {
    name: 'tooltip',
    props: ['tooltipText','width']
  }
</script>

<style
  lang="scss"
  scoped
  src="~c/tooltip.scss"
></style>