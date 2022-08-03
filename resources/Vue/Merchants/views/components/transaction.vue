<template>
  <div id="transactionView" class="info-box justify-between pr">
      <div class="info info-date">
          <h6 class="info_title">
              {{ dDate }}
          </h6>
          <p class="info_content">Payment Date</p>
      </div>
      <div class="info info-amount">
            <h6 class="info_title">
              {{ amount }}
              <span v-if="rcode!=0">({{rcode}})</span>
            </h6>
            <p class="info_content">Payment Amount</p>
      </div>
      <!-- <div class="info info-amount">
          <h6 class="info_title">
              {{
                  rcode
              }}
          </h6>
          <p class="info_content">Rcode</p>
      </div> -->
      <div class="info info-count" v-if="Number(amount)!=0">
            <h6 class="info_title">
              {{ count }}
            </h6>
            <p class="info_content">Payment Balance</p>
      </div>
      <div class="info info-count flex-center" v-else>
            <p class="info_content">Payment Failed</p>
      </div>
  </div>
</template>
<script>
export default {
    name:'transaction-component',
    computed:{
        dDate(){
            if(this.date) return this.date;
            return '';
        },
        dAmount(){
            if(Number(this.amount) || Number(this.amount) === 0){
                let amount=Number(this.amount);
                if(amount< 0 ){
                    amount=amount-amount*2;
                    amount=`- $${
                        this.formatMoney(amount.toFixed(2))
                    }`;
                }else amount=`$${
                    this.formatMoney(amount.toFixed(2))
                }`;
                return amount;
            }
            return '';
        },
        dCount(){
            if(this.count) return this.count;
            return '';
        }
    },
    methods:{
        formatMoney(money){
            money = money.toString();
            var pattern = /(-?\d+)(\d{3})/;
            while (pattern.test(money)) {
                money = money.replace(pattern, '$1,$2');
            }
            return money;
        }
    },
    props:[
        'date',
        'amount',
        'count',
        'rcode'
    ]
}
</script>