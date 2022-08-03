<template>
    <button
        class="filters"
        v-on="wrapper"
    >
        {{ title || 'Filters' }}
        <svg v-if="settings" class="icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"><path d="M80.27,73A3.072,3.072,0,0,1,82,70.384a9.537,9.537,0,0,0-.741-1.791,2.9,2.9,0,0,1-2.906-.947,2.807,2.807,0,0,1-.731-2.906A9.409,9.409,0,0,0,75.831,64a3.182,3.182,0,0,1-5.663,0,9.409,9.409,0,0,0-1.791.741,2.809,2.809,0,0,1-.731,2.906,2.9,2.9,0,0,1-2.9.947A9.13,9.13,0,0,0,64,70.384,3.068,3.068,0,0,1,65.734,73,3.4,3.4,0,0,1,64,75.831a9.41,9.41,0,0,0,.741,1.791,2.919,2.919,0,0,1,3.633,3.637A9.536,9.536,0,0,0,70.164,82a3.182,3.182,0,0,1,5.663,0,9.409,9.409,0,0,0,1.791-.741,2.809,2.809,0,0,1,.731-2.906,2.9,2.9,0,0,1,2.906-.947A9.668,9.668,0,0,0,82,75.616,3.067,3.067,0,0,1,80.27,73ZM73,77.608A4.608,4.608,0,1,1,77.608,73,4.61,4.61,0,0,1,73,77.608Z" transform="translate(-64 -64)" fill="#6f6f9c"/></svg>
        <img v-else class="icon" :src="require('@image/icons/filter-list.svg').default" />
    </button>
</template>

<script>
export default {
    name: 'filter-button',
    computed:{
        wrapper() {
            return this.$listeners
        }
    },
    props:{
        title: String,
        settings: Boolean
    }
}
</script>

<style lang="scss" scoped>
button.filters {
    width: auto;
    height: 49px;
    border: 1px solid #DDE2EC;
    border-radius: 10px;
    background: white;
    outline: none;
    color: #6F6F9C;
    font-size: 17px;
    font-weight: bold;
    padding: 0 15px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    &.loading{
        background: #afd9d1;
        .icon{
            display: none;
        }
    }
    .icon {
        margin-left: 12px;
    }
}
</style>