<template>
  <component
    :is="name"
    role="img"
    class="inline-block fill-current"
    :style="{height:size+'px'}"
  />
</template>

<script>
import { kebabCase } from 'lodash';

const IconsContext = require.context('./icons', false, /Icon[\w]+\.vue$/),
components = {};

IconsContext.keys().forEach(path => {
  const
    Icon = IconsContext(path),
    iconName = kebabCase(path.replace(/^\.\/Icon/,'').replace(/\.vue$/,''))
    components[iconName] = Icon.default || Icon
});

export default {
  props: {
    name: {
      type: String,
      required: true
    },
    size:{
      default:22,
      type:Number
    }
  },
  components
}
</script>