<template>
    <div id="select-box">
        <div
            class="selected"
            @click="toggleSelect"
            v-if="value"
        >
            <span class="icon">
                <img :src="require('@image/icons/calander.svg').default" />
            </span>
            <span class="sort-by">{{ value[label] || value.text || value.value}}</span>
            <span
                class="arrow"
                :class="{open:!closed}"
            >
                <img :src="require('@image/icons/angle-down.svg').default" />
            </span>
        </div>
        <div
            class="options-box"
            :class="{closed}"
        >
            <div
                class="option-wrapper"
                v-for="(option,index) in options"
                :key="index"
            >
                <button class="option"
                    v-if="
                        option != value &&
                            option.text ||
                        option != value &&
                            label && option[label]
                    "
                    @click="select(option)"
                >
                    {{
                        typeof option == String ?
                            option :
                        option[label] || option.text || option.value
                    }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'custom-select-box',
    data(){
        return {
            closed: true,
            value: null
        }
    },
    methods:{
        select(option){
            this.value = option;
            this.toggleSelect();
            this.$emit('select',option);
        },
        clickOutside(e){
            let currentTarget = e.target;
            const base = this.$el;
            while(currentTarget){
                if(currentTarget==document.documentElement){
                    this.closed = true;
                    return;
                }else if(currentTarget == base) return;
                else currentTarget = currentTarget.parentNode;
            }
        },
        toggleSelect(){
            this.closed = !this.closed;
        }
    },
    props:[
        'options',
        'classes',
        'selected',
        'label'
    ],
    created(){
        window.addEventListener('click',this.clickOutside);
        if(this.selected) this.value = {
            text:this.selected,
            value: this.selected,
            selected:true
        };
        else this.value = this.options[0];
    },
    beforeDestroy(){
        window.removeEventListener('click',this.clickOutside);
    },
    watch:{
        selected(){
            this.$emit('change',this.selected);
        }
    }
}
</script>

<style
    lang="scss"
    scoped
    src="~c/selectBox.scss"
></style>