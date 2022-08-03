<template>
  <div id="transactionView" class="info-box justify-between pr">
      <div class="info info-date">
          <h6 class="info_title">
              {{ dDate }}
          </h6>
          <p class="info_content">Date</p>
      </div>
      <div class="info info-amount">
            <h6 class="info_title">
              {{ amount }}
            </h6>
            <p class="info_content">Request</p>
      </div>
      <div class="info info-status">
            <h6 class="info_title">
              {{ status }}
            </h6>
            <p class="info_content">Status</p>
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
        'status',
    ]
}
</script>