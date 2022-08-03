<template>
    <th @click="sortBy(th,isSortable(th))" class="th" :class="{sortable:isSortable(th)}">
        <slot />
        <span v-if="isSortBy(th)" class="sorted-icon">
            <icon class="icon" v-if="sortOrder" :icon="['fas','arrow-down']" />
            <icon class="icon" v-else :icon="['fas','arrow-up']" />
        </span>
        <span v-else-if="isSortable(th)" class="sortable-icon">
            <icon :icon="['fas','arrow-up']" />
        </span>
    </th>
</template>

<script>
import { snakeCase } from 'lodash';
    export default {
        methods:{
            sortBy(val,pass){
                if(!pass) return;
                val = snakeCase(val)
                const r = this.$route,
                so = r.query.sortOrder,
                sb = r.query.sortBy,
                switchOrder = so ? !+so.toString().replace(/[^0-9\.]/g, '') : 0,
                sortOrder = val == sb ? (switchOrder ? 1 : 0) : 1
                this.$router.push({
                    path: r.path,
                    query: {
                    ...r.query,
                    sortBy: val,
                    sortOrder
                    }
                }).catch(e=>e)
            },
            isSortBy(val){
                return this.sortByVal == snakeCase(val)
            },
            isSortable(val){
                return !(this.exceptions && this.exceptions.some(ex=>ex==val))
            }
        },
        computed:{
            th(){
                const sl = this.$slots.default[0].text
                return sl.trim()
            },
            sortByVal(){
                return this.$route.query.sortBy
            },
            sortOrder(){
                return +(this.$route.query.sortOrder || '1')
            }
        },
        props:[
            'exceptions'
        ]
    }
</script>

<style scoped lang="scss">
    .sortable-icon{
        opacity: 0;
    }
    .th{
        &.sortable{
            cursor: pointer;
        }
        &:hover {
            .sortable-icon{
                opacity: 0.5;
            }
            .sorted-icon .icon{
                transform: scaleY(-1);
                opacity: 0.5;
            }
        }
    }
</style>