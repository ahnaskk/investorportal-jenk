<template>
  <div class="agreement-box" @click="closeIfOutside">
    <div
      class="content-box"
      ref="box"
    >
      <!-- CLOSE BUTTON -->
      <button 
        class="close-btn"
        type="button"
        @click="close"
      >
        <img :src="require('@image/icons/close-icon.svg').default" alt="">
      </button>
      <!-- /CLOSE BUTTON -->
      <pdf
        @contextmenu.native.stop.prevent
        :src="responsePdfUrl"
        v-if="responsePdfUrl"
      />
      <div class="agreement-terms-wrapper" v-else>
        <agreementDetalis :data="data" />
        <div class="sign-pad">
          <div class="overlay" v-if="showverlay">
            <div class="msg" @click="showverlay=false">
              Click here to add Signature.
            </div>
          </div>
          <VueSignaturePad
            class="canvas"
            width="350"
            height="250"
            ref="signaturePad"
            :options="{
              backgroundColor: 'rgba(255,255,255,1)'
            }"
          />
          <div class="control-row">
            <button
              @click="undo"
              class="btn undo"
            >Undo</button>
          </div>
          <div class="submit-row">
            <div class="info">
              <p class="info">
                <span class="bold">
                  By :
                </span>
                &nbsp;{{ data.participant }}
              </p>
              <p class="info">
                <span class="bold">
                  Date :
                </span>
                &nbsp;{{ data.date_en }}
              </p>
              <p class="info">
                <span class="bold">
                  Server :
                </span>
                &nbsp;{{ server }}
              </p>
            </div>
            <p class="signature-warning" v-if="showSignatureWarning">
              Please add your signature
            </p>
            <!-- <button
              @click="requestCreditPay"
              class="btn credit-pay"
            >Pay With Credit Card</button> -->
            <button
              @click="saveSign"
              class="btn save"
              v-loader="loading"
            >Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import pdfvuer from 'pdfvuer'
import 'pdfjs-dist/build/pdf.worker.entry'
import agreementDetalis from './agreementDetalis';
const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g,'')
  export default {
    name: 'agreement',
    data(){
      return {
        showSignatureWarning: false,
        showverlay: true,
        server:null
      }
    },
    methods: {
      requestCreditPay(){
        const {
          isEmpty,
          data
        } = this.$refs.signaturePad.saveSignature();
        if(!isEmpty){
          const
            amount = strip(this.fundingAmount),
            merchantID = this.merchantID,
            investorId = this.$store.getters['auth/investorID']
          const a = document.createElement('a')
          a.target = '_blank'
          a.href = `/pm/${investorId}/make-payment/${amount}?type=investor`
          document.body.appendChild(a)
          a.click()
          document.body.removeChild(a)
          this.close()
          // const post = { amount, merchantID, investorId, signature: data }
          // this.$store.dispatch('api/call',{
          //   url: '/pm/make-payment',
          //   post
          // }).then(r=>{
          //   console.log(r)
          //   if(r&& r.status){
          //     this.$emit('payWithCreditCard',data)
          //   }
          // })
          // .catch(e=>{
          //   this.$emit('creditCardError')
          // })
        }else{
          this.warnSignEmpty()
        }
      },
      warnSignEmpty(){
        this.showSignatureWarning = true
        setTimeout(() => this.showSignatureWarning = false ,2000)
      },
      closeIfOutside(e){
        const outside = this.$clickOut(this.$refs.box,e.target,this.$el);
        if(outside) this.close();
      },
      close(){
        const done = this.responsePdfUrl != null;
        this.$emit('hide',done);
      },
      undo() {
        this.$refs.signaturePad.undoSignature();
      },
      saveSign() {
        const {
          isEmpty,
          data
        } = this.$refs.signaturePad.saveSignature();
        if(!isEmpty){
          this.$emit('signed',data);
        }else this.warnSignEmpty()
      },
      fetchIP(){
        fetch(' https://api.ipify.org?format=json')
        .then(res => res.json())
        .then(json => this.server = json.ip)
      }
    },
    components:{
      agreementDetalis,
      'pdf':pdfvuer
    },
    created(){
      document.body.style.overflow = 'hidden';
      this.fetchIP()
    },
    destroyed(){
      document.body.style.overflow = 'auto';
    },
    props:[
      'data',
      'loading',
      'responsePdfUrl',
      'fundingAmount',
      'merchantID'
    ]
  };
</script>

<style
  lang="scss"
  src="~c/marketplace/sub/agreement.scss"
  scoped
></style>