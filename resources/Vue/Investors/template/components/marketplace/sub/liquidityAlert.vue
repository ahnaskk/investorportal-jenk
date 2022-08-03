<template>
  <div class="alert-bg" @click="click">
      <div class="alert-box" ref="box">
          <div class="sec msg">
                <img
                :src="require('@image/icons/liquidity-alert-icon.svg').default"
                class="img"
                >
              <p class="msg-text">
                {{ liquidytyMsg || "You don't have enough liquidity to fund this deal" }}
              </p>
          </div>
          <div class="sec user-input">
              <div class="check line" @click="check = !check">
                  <span class="inline-block">
                    <div class="check-box" :class="{active:check}">
                        <icon
                            :icon="['fas','check']"
                            class="tick"
                        />
                    </div>
                  </span>
                  I hereby authorize Velocity Group USA to debit {{amount|formatMoney}}{{ accNo? '':'.'}} <br v-if="accNo" />
                  {{accNo ? `from my account ending in ${ acHash(accNo) }.` : ''}}
              </div>
              <div class="input-group line" :class="{error}">
                  <span class="sign">$</span>
                  <currency-input
                    type="text"
                    class="input-amount"
                    v-model="amount"
                    :value="amount"
                    :currency="null"
                    :distraction-free="true"
                    ref="input"
                  />
              </div>
              <div class="account-details" :class="{error}">
                  {{ error ? 'Please Enter an Amount!' : '' }}
              </div>
          </div>
          <div class="sec submit">
            <button
                class="blue-bt"
                :class="{disabled:!check}"
                type="button"
                @click="proceed"
                v-loader="loading"
                :key="check"
            >Send ACH & Continue</button>
            <!-- <button
                class="blue-bt credit-card-payment-link"
                :class="{disabled:!check}"
                target="_blank"
                @click="creditPay"
            >Pay with credit card</button> -->
          </div>
      </div>
  </div>
</template>

<script>
export default {
    name: 'liquidity-alert',
    data(){
        return {
            check: false,
            amount: 0,
            error: false
        }
    },
    created(){
        if(this.requestAmount) this.amount = this.requestAmount;
        document.body.style.overflow = 'hidden'
    },
    beforeDestroy(){
        document.body.style.overflow = 'auto'
    },
    methods:{
        acHash(v){
            return 'xxx'+v.toString().slice(-4)
        },
        creditPay(){
            if(!this.check) return
            const a = document.createElement('a')
            a.target = '_blank'
            a.href = `/pm/${this.id}/make-payment/${this.amount}?type=investor`
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            this.$emit('close')
        },
        click(e){
            const out = this.$clickOut(this.$refs.box,e.target,this.$el);
            if(out) this.rej()
        },
        proceed(){
            if(this.check){
                if(!this.amount) this.showError()
                else this.res({amount:this.amount})
            }
        },
        showError(){
            this.error = true
            this.$refs.input.$el.focus()
            setTimeout(function(){
                this.error = false
            }.bind(this),1200)
        }
    },
    props:[
        'requestAmount',
        'res',
        'rej',
        'accountNoMsg',
        'id',
        'liquidytyMsg',
        'accNo',
        'loading'
    ]
}
</script>

<style
  lang="scss"
  src="~c/marketplace/sub/liquidityAlert.scss"
  scoped
></style>