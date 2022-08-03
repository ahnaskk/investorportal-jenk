<template>
    <div class="info-card">
        <header class="card-header">
            <div class="icon">
                <img :src="icon" />
            </div>
            <div class="titles">
                <h3 class="label">
                    {{label}}
                    <tooltip
                        :tooltipText="tooltipText || label"
                        :class="tooltipPosition || 'right'"
                    />
                </h3>
                <h2 class="date">
                    {{ date }}
                </h2>
            </div>
        </header>
        <div class="slot-outer">
            <slot />
        </div>
    </div>
</template>

<script>
import tooltip from '@c/tooltip.vue'
    export default {
        name: 'info-card',
        props:[
            'icon',
            'label',
            'date',
            'tooltipText',
            'tooltipPosition'
        ],
        components:{
            tooltip
        }
    }
</script>

<style
    lang="scss"
    scoped src="~c/merchantDashboard/sub/infoCard.scss"
></style>