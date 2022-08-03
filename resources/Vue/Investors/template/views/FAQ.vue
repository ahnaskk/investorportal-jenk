<template>
    <div class="wrapper">
        <preloader v-if="loading" />
        <emptyBox v-if="error" :msg="errorMsg" />
        <div v-if=" data && !loading && !error">
            <div
                class="outer-card"
                v-for="(card,index) in data"
                :key="index"
            >
                <span class="title">{{card.title}}</span>
                <div class="video" v-if="card.link">
                    <div class="video-wrapper" v-show="card.playVideo">
                        <youtube
                            :video-id="splitId(card.link)" 
                            ref="youtube"  
                            :player-vars="playerVars"
                            :width="100+'%'"
                            :height="100+'%'"
                            @playing="playing(card,index)"
                        >
                        </youtube>
                    </div>
                    <div v-show="!(card.playVideo)" class="thumb" @click="toggle(card,index)">
                        <div class="img-wrapper">
                            <img :src="loadPoster(card.link)" alt="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="72.937" height="51.226" viewBox="0 0 72.937 51.226">
                                <g id="Group_65" data-name="Group 65" transform="translate(-5.371 -13.433)">
                                    <path id="Path_26" data-name="Path 26" d="M46.4,34.5c-6.687-3.574-13.091-6.86-19.637-10.287V44.729c6.888-3.744,14.148-7.173,19.665-10.232H46.4Z" transform="translate(7.558 3.802)" fill="#fff"/>
                                    <path id="Path_27" data-name="Path 27" d="M46.4,34.5c-6.687-3.574-19.637-10.287-19.637-10.287l17.264,11.6S40.881,37.556,46.4,34.5Z" transform="translate(7.558 3.802)" fill="#e8e0e0"/>
                                    <path id="Path_28" data-name="Path 28" d="M35.579,64.6c-13.891-.257-18.635-.486-21.552-1.087a9.473,9.473,0,0,1-4.945-2.572A10.414,10.414,0,0,1,6.739,56.42a29.53,29.53,0,0,1-1-6.631,157.511,157.511,0,0,1,0-21.495c.445-3.969.662-8.683,3.628-11.432A9.73,9.73,0,0,1,14.226,14.4c2.858-.543,15.034-.972,27.64-.972,12.577,0,24.78.429,27.641.972a9.509,9.509,0,0,1,5.687,3.372c2.707,4.257,2.754,9.551,3.029,13.692.114,1.973.114,13.176,0,15.149-.427,6.544-.772,8.86-1.743,11.26a8.3,8.3,0,0,1-2,3.2,9.587,9.587,0,0,1-5.088,2.6c-12.021.9-22.228,1.1-33.813.915ZM53.985,38.3C47.3,34.727,40.894,31.412,34.349,27.982V48.506C41.238,44.761,48.5,41.33,54.014,38.272Z" fill="#cd201f"/>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
                <div v-if="card.description">
                <hr />
                <p>
                    {{card.description}}
                </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VueYoutube from 'vue-youtube'
import { mapState } from 'vuex';
import emptyBox from '@merchantComponents/emptyBox'
import {getYoutubeID} from '@h/getYoutubeID'
export default {
    name:'FAQ',
    data(){
        return{
            playerVars: {
                resize: false,
                fitParent:true,
                rel: 0,
                showinfo: 0,
                autoplay: 0,
            },
            error:false,
            errorMsg:""
        }
    },
    computed: {
        ...mapState('api',{
            data: state => state.faq,
            loading: (s) => s.loading.faq
        })
    },
    methods:{
        splitId(url){
            return getYoutubeID(url)
        },
        toggle(card,index){
            this.data.map((faq,index) =>{
                faq["playVideo"] = false
                this.$set(this.data,index,faq)
            })
            card.playVideo = !(card.playVideo)
            this.$set(this.data,index,card)
            for (let i=0 ; i<this.$refs.youtube.length; i++){
                this.$refs.youtube[i].player.pauseVideo()
            }
            this.$refs.youtube[index].player.playVideo()
        },
        loadPoster(url){
            return `https://img.youtube.com/vi/${getYoutubeID(url)}/0.jpg`
        },
        playing(item,index){
            item.playVideo = true
            this.$set(this.data,index,item)
        },
        on_create(){
            this.$store.dispatch('api/getData',{
                force: true,
                url: '/faq',
                field: 'faq'
            })
            .catch(e=>{
                this.error =true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later'
            })
      }
    },
}
</script>

<style
    lang="scss"
    scoped
    src="~v/faq.scss"
></style>