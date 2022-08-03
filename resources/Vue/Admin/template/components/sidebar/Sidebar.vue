<template>
  <div id="menu-panel" :class="{collapsed}">
    <ul class="menu-list">
      <li
        v-for="(item,index) in menuList"
        :key="'item'+index"
        class="menu-item"
      >
        <!-- title  if submenu is available-->
        <router-link
          class="menu-title"
          :to="item.link"
          event
          v-if="item.submenu"
          @click.native.prevent="item.open = !item.open"
          :class="{collapsed}"
        >
          <!-- Icon -->
            <BaseIcon 
              :name="item.icon"
              v-if="item.icon"
              class="item-icon"
            />
            <!-- <span class="item-indicator" v-else style="color:white">
              {{
                item.title.substr(0,1).toUpperCase()
              }}
            </span> -->
          <!-- /Icon -->
          <span class="collapsible">
            {{
              item.title
            }}
          </span>
          <!-- icon to show toggle status -->
          <icon
            v-if="item.submenu"
            class="sortArrowIcon"
            :class="{ turn:item.open}"
            :icon="['fa','angle-left']"
          />
          <!-- <icon
            v-if="item.submenu && !item.open"
            class="sortArrowIcon"
            :icon="['fas','sort-down']"
          /> -->
        </router-link>
        <!-- link if no submenu is there -->
        <router-link
          v-else
          class="menu-title link"
          :class="{collapsed}"
          :to="item.link"
        >
          <!-- Icon -->
            <BaseIcon
              class="item-icon"
              :name="item.icon"
              v-if="item.icon"
            />
            <!-- <span class="item-indicator" v-else style="color:white">
              {{
                item.title.substr(0,1).toUpperCase()
              }}
            </span> -->
          <!-- /Icon -->
          <span class="collapsible">
            {{
              item.title
            }}
          </span>
        </router-link>
        <!-- submenu -->
        <Dropdown
          v-if="item.submenu && item.open"
          :submenu="item.submenu"
          :class="{open:item.open}"
        />
      </li>
    </ul>
  </div>
</template>

<script>
  import Dropdown from './dropdown';
  import BaseIcon from './BaseIcon';
  export default {
    name:'Sidebar',
    components:{
      Dropdown,
      BaseIcon
    },
    computed:{
      menuList(){
        if(this.list) return this.list;
        return [];
      },
      collapsed(){
        return this.collapse || false;
      }
    },
    props:{
      list:Array,
      collapse:Boolean
    }
  }
</script>
<style
  scoped
  lang="scss"
  src="~a/sidebar/sidebar.scss"
></style>