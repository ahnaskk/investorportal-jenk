<template>
    <div :class="[
        'side_section',
        {'collapsed':toggle}
        ]"
        v-if="menu"
    >
    <ul class="menu_list">
        <li
          v-for="(link,index) in menu"
          :key="index"
        >
            <span v-if="link.hidden"></span>
            <router-link
                v-else-if="!link.emit && !link.anchorTag"
                :to="link.link"
                class="menu_link plane"
            >
                <icon :icon="link.icon" />
                <span class="ml-10">{{ link.title }}</span>
            </router-link>
            <a
                v-else-if="link.anchorTag"
                :href="link.link"
                class="menu_link plane" 
                target="_blank"
            >
                <icon :icon="link.icon" />
                <span class="ml-10">{{link.title}}</span>
            </a>
            <!-- logout / any action button -->
            <a
                v-else
                class="menu_link bt_blank"
                href="javascript:void(0)"
                @click.prevent="emitEvent(link.emit)"
            >
                <icon :icon="link.icon" />
                <span class="ml-10">{{ link.title }}</span>
            </a>
        </li>
    </ul>
    </div>
</template>

<script>
import { EventBus } from '../bus'
export default {
    name:'side-bar-menu',
    props:{
        menu:Array,
        toggle:Boolean
    },
    computed:{
        toggleMenu(){
            if(this.toggle != undefined){
                return this.toggle
            }return false
        }
    },
    methods:{
        emitEvent(event){
            EventBus.$emit(event)
        }
    }
}
</script>

<style lang="scss">
.menu_list{
    margin: 0;
    padding: 0;
    list-style: none;
}
</style>