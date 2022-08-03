
<template>
  <div class="tooltip-wrapper">
    <icon :icon="['fa','info-circle']" class="avatar-icon" />
    <span class="tooltiptext">{{tooltipText}}</span>
  </div>
</template>

<script>
  export default {
    name: 'tooltip',
    props: ['tooltipText']
  }
</script>

<style lang="scss" scoped src="~ac/tooltip.scss"></style>