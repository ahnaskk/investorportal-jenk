<template>
    <div class="wrapper">
        <preloader v-if="loading" />
        <div class="inner-wrapper" v-if="collectionNotes && notes">
            <div 
                v-for="(note,index) in notes"
                :key="index"
                class="note">
                <span class="date">{{note.date}}</span>
                <div 
                    v-for="(details,index) in note.data"
                    :key="index"
                    class="note-card">
                    <div class="split">
                        <BaseIcon :name="'collection'" class="item-icon"/>
                    </div>
                    <div class="split">
                        <h2>{{details.name}}</h2>
                        <p v-html="details.note" class="notes">
                            {{details.note}}
                        </p>
                    </div>
                </div>
            </div>
            <pagination 
                :pagination="collectionNotes.pagination"
                @pageChange="handlePageChange($event)"
            />
        </div>
    </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import pagination from '@c/pagination'
import BaseIcon from '@c/BaseIcon'
export default {
    components:{
        pagination,
        BaseIcon
    },
    data(){
        return {
            limit:10
        }
    },
    computed:{
        ...mapGetters({
            collectionNotes:'api/collectionNotes'
        }),
        ...mapState('api',{
            loading: s => s.loading.collectionNotes
        }),
        notes(){
            return this.collectionNotes.notes
        },
        currentLimit(){
            return this.collectionNotes.notes.length
        }

    },
    methods:{
        handlePageChange(page){
            if(page){
                this.$store.dispatch('api/getData',{
                    force:true,
                    post:{
                        limit:this.limit,
                        offset:this.limit* (page - 1)
                    },
                    url:'collection-notes',
                    field:'collectionNotes'
                }).catch(e=>{})
            }
        }
    },
    created(){

    }
}
</script>

<style scoped lang="scss" src="~v/collection-notes.scss">
</style>