<template>
    <div class="pagination-container" v-if="pagination && !hidePagination">
        <div class="pagination" v-if="pagination.last_page > 1">
            <paginate
            :page-count="pagination.last_page"
            :value="pagination.current_page"
            :click-handler="goToPage"
            :prev-text="'Prev'"
            :next-text="'Next'"
            prevClass="prev"
            nextClass="next"
            :container-class="'paginator'"
            ></paginate>
        </div>
    </div>
</template>

<script>
export default {
    props:{
        pagination:{
            type:Object,
            required:true,
            default:{
                from:0,
                to:0,
                total:0
            }
        },
        hidePagination:{
            type:Boolean,
            default:false
        }
    },
    data(){
        return {
            updatePaginate:0
        }
    },
    methods:{
        goToPage(page) {
            this.$emit('pageChange',page)
        },
    },
    watch: {
        pagination: {
            deep: true,
            immediate: true,
            handler() {
                this.updatePaginate++;
            }
        }
    }
}
</script>
<style lang="scss" scoped src="~c/pagination.scss"></style>

