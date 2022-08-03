<template>
  <div>
    <preloader v-if="loading.docs" />
    <emptyBox v-if="!loading.docs && error" :msg="errorMsg" />
    <table class="data-table" v-if="data && !loading.docs && !error">
      <thead>
        <tr role="row">
          <!-- <th>Id</th> -->
          <th>Title</th>
          <th>Document type</th>
          <th>Upload Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(item,i) in data"
          :key="i"
        >
          <!-- <td>{{ item.documentId }}</td> -->
          <td>{{ item.documentTitle }}</td>
          <td>{{ item.documentType }}</td>
          <td>{{ item.uploadDate}}</td>
          <td>
            <button
              class="table-action-bt"
              type="button"
              @click="downloadFile(item.documentUrl)"
            >Download</button>
          </td>
        </tr>
      </tbody>
    </table>
    <empty v-if="!loading.docs&& !error && !data"/> 
    <!-- pdf modal -->
    <div class="pdf-viewer" v-if="viewPdf || viewImg" 
      
    >
      <!-- view port -->
      <div class="view-port" v-click-outside="close">
        <div class="controls">
          <button 
            class="close-btn"
            type="button"
            @click="viewPdf = viewImg = zoomState = false"
          >
            <img :src="require('@image/icons/close-icon.svg').default" alt="">
          </button>
          <button v-if="viewPdf" @click="downloadPdf(pdfUrl)" class="zoom"> Download </button>
        </div>
        <div class="pdf-viewe">
          <pdf
            :src="pdfUrl"
            v-if="viewPdf"
            width="100%"
            ref="pdfRef"
          />
          <img :src="imgUrl" v-if="viewImg" />
        </div>
      </div>
       <!-- /view port -->
    </div>
  </div>
</template>

<script>
  import pdf from '@c/pdf'
  import downloadFile from '@h/downloadFile'
  import empty from './docs/empty'
  export default {
    data(){
      return {
        viewPdf : false,
        zoomState:false,
        extensions:{
          printable:[],
          downloadable:['csv','docx','doc'],
          viewable:[]
        },
        viewImg: false,
        pdfUrl: null,
        imgUrl: null,
        data: null,
        loading:{
          docs: false
        },
        error: false,
        errorMsg: null
      }
    },
    components: {
      pdf,
      empty
    },
    methods:{
      close(){
        this.viewImg = this.viewPdf = false
      },
      progress(e,a){console.log(e,a)},
      on_create(){
        const merchantId = this.$route.params.id
        this.loading.docs = true
        this.$store.dispatch('api/call',{
          url: 'marketplace-documents',
          post:{
            merchantId
          }
        }).then(r=>{
          if(r.status && r.data && r.data.length)
            this.$set(this,'data',r.data)
        }).catch((e = {})=>{
          this.showErr(e.msg || e.message || 'Something went wrong! Please try again later')
        }).finally(()=>this.loading.docs = false)
      },
      // showPdf(src){
      //   const ext = src.split('.').pop()
      //   if(ext=='pdf'){
      //     this.pdfUrl = src
      //     this.viewPdf = true
      //   }else if( this.extensions.downloadable.includes(ext) ){
      //     let a = document.createElement('a')
      //     a.href = src
      //     a.target= '_blank'
      //     document.body.appendChild(a)
      //     a.click()
      //     document.body.removeChild(a)
      //   }
      //   else{
      //     this.viewImg = true
      //     this.imgUrl = src
      //   }
      // },
      showErr(msg){
        this.error = true
        this.errorMsg = msg || 'Something went wrong! Please try again later'

      },
      // zoomToggle(){
      //   this.zoomState = !this.zoomState
      //   this.$refs.pdfRef.zoom(this.zoomState)
      // },
      downloadFile(url){
        new downloadFile(url).download()
      }
    },
    watch:{
      viewPdf(to){
        if(to) document.body.style.overflow = 'hidden'
        else document.body.style.overflow = 'auto'
      }
    },
    computed:{
      zoomText(){
        return this.zoomState ? 'Exit zoom mode' : 'Zoom mode'
      }
    },
  }
</script>

<style
  src="~v/marketplace/docs.scss"
  lang="scss"
  scoped
></style>