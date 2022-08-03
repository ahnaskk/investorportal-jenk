<template>
    <div
        ref="pdf"
        :style="{width:100+'%'}"
        :class="{active:reset}"
    >
    <canvas height="300" width="300" ref="pdf_renderer" :key="config.zoom"></canvas>
    </div>
</template>

<script>
import pdfvuer from 'pdfvuer'
import panzoom from 'panzoom'
import * as pdfjsLib from 'pdfjs-dist';
let instance = null;
    export default {
        data(){
            return {
                loadingTask: null,
                pages: 0,
                width:300,
                instance:null,
                reset:false,
                pdf:null,
                config:{
                    zoom:1,
                    currentPage:1
                }
            }
        },
        components:{
            'pdf':pdfvuer
        },
        props:{
            src:{
                required: true
            }
        },
        methods:{
            zoom(status){
                if(!status) {
                    instance.dispose();
                    this.reset = true;
                }else{
                    this.reset = false
                    instance = panzoom(this.$refs.pdf)
                    this.config.zoom += 0.5
                    this.renderPdf()
                }
            },
            renderPdf(){
                this.pdf.getPage(this.config.currentPage).then((page) => {
                    var canvas = this.$refs["pdf_renderer"];
                    var ctx = canvas.getContext('2d');
                    var viewport = page.getViewport({
                        scale:this.config.zoom
                    });
                    let containerWidth = this.$refs.pdf.clientWidth
                    canvas.width = containerWidth
                    canvas.height = viewport.height
                    page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    });
                });
            }
        },
        mounted(){
            pdfjsLib.disableWorker = true;
            pdfjsLib.getDocument(this.src).promise.then(function (pdf) { 
                this.pdf = pdf
                this.renderPdf()
            }.bind(this));
        },
    }
</script>

<style scoped>
    .active{
        transform: none!important;
    }
</style>