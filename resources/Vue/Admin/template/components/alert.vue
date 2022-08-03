<template>
  <div class="alert-bg" @click="click">
      <div class="msg-box" ref="msg">
            <div class="icon-box">
                <img
                    v-if="data.type=='warning'"
                    :src="require('@image/icons/error.svg').default"
                    class="icon"
                />
                <img
                    v-if="data.type=='success'"
                    :src="require('@image/icons/tick.svg').default"
                    class="icon"
                />
            </div>
            <!-- message -->
            <p class="msg">
                {{
                    data.message
                }}
            </p>
            <!-- button -->
            <button
                class="ok"
                :class="data.type || ''"
                type="button"
                @click="$emit('close')"
            >
                OK
            </button>
      </div>
  </div>
</template>

<script>
export default {
    name: 'alert',
    props:{
        data:{
            type: Object,
            required: false
        }
    },
    created(){
        document.documentElement.style.overflow = 'hidden';
    },
    beforeDestroy(){
        document.documentElement.style.overflow = 'auto';
    },
    methods:{
        click(e){
            let t = e.target;
            const mb = this.$refs.msg;
            while(t){
                if(t==mb) return;
                else if( t== this.$el ) return this.$emit('close');
                else t=t.parentNode;
            }
        }
    }
}
</script>
<style
    lang="scss"
    scoped
    src="~ac/alert.scss"
></style>