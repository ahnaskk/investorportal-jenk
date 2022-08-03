<template>
  <transition
    name="expand"
    @appear="Appear"
    @leave="leave"
  >
    <ul class="menu-list sub">
        <li
          v-for="(item,index) in submenuList"
          :key="'item'+index"
          class="menu-item sub"
          :class="{open:item.open}"
        >
          <!-- title  if submenu is available-->
          <span
            class="menu-title sub"
            v-if="!item.link"
            @click="item.open = !item.open"
          >
            <!-- Icon -->
              <icon
                class="item-icon"
                :icon="item.icon"
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
            <!-- icon to show toggle status -->
            <icon v-if="item.submenu && item.open" class="sortArrowIcon" icon="sort-up"></icon>
            <icon v-if="item.submenu && !item.open" class="sortArrowIcon" icon="sort-down" />
          </span>
          <!-- link if no submenu is there -->
          <router-link
            v-else
            class="menu-title sub link"
            :to="item.link"
          >
            <!-- Icon -->
              <icon
                class="item-icon"
                :icon="item.icon"
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
          />
        </li>
    </ul>
  </transition>
</template>

<script>
export default {
    name: 'Dropdown',
    data(){
      return {
        speed:20
      }
    },
    computed:{
        submenuList(){
            if(this.submenu) return this.submenu;
            return [];
        }
    },
    props:['submenu'],
    methods:{
      Appear(el,done){
        const rect=el.getBoundingClientRect();
        const h=rect.height;
        let ph=0;
        let speed=this.speed;
        function expand(){
          if(ph>=h){
            el.style.maxHeight='unset';
            el.style.height='auto';
            done();
            return;
          }else{
            ph+=speed;
            el.style.maxHeight=ph+'px';
            window.requestAnimationFrame(expand);
          }
        }
        expand();
      },
      leave(el,done){
        const rect=el.getBoundingClientRect();
        const h=rect.height;
        let ph=h;
        const speed=this.speed;
        function shrink(){
          if(ph<=0){
            el.style.maxHeight='unset';
            el.style.height='auto';
            done();
            return;
          }else{
            ph-=speed;
            el.style.maxHeight=ph+'px';
            window.requestAnimationFrame(shrink);
          }
        }
        shrink();
      }

    }
}
</script>