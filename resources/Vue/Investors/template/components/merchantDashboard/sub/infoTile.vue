<template>
  <div
    :class="['info-tile',type]"
    :style="`--color:${color||'#EEECFF'}`"
  >
      <div class="icon" :style="`--color:${color||'#EEECFF'}`">
        {{ icon }}
      </div>
      <div class="data">
        <h3 class="label">
          {{ label }}
          <tooltip
            :class="tooltipPosition || 'right'"
            :tooltipText="tooltipText || label"
            :width="tooltipWidth || 200"
          />
        </h3>
        <h2 class="value">
          {{ value }}
        </h2>
      </div>
  </div>
</template>

<script>
import tooltip from '@c/tooltip.vue'
export default {
  components: { tooltip },
  name: 'info-tile',
  props:[
    'icon',
    'label',
    'value',
    'color',
    'type',
    'tooltipPosition',
    'tooltipText',
    'tooltipWidth'
  ]
}
</script>

<style
    lang="scss"
    scoped src="~c/merchantDashboard/sub/tile.scss"
></style>