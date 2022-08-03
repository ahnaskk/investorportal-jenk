<template>
  <div id="dashboard-contents" class="content-box centerRule pr">
    <!-- second row -->
    <div class="row">
        <!-- Payment Left Amount -->
        <div class="col-lg-4">
            <div class="card g-c">
                <h2 class="strong pr">
                    {{
                        totalPaymentsAmount || ''
                    }}
                </h2>
                <h3 class="slim">
                    Payment Amount
                </h3>
            </div>
        </div>
        <!-- Payments Amount -->
        <!-- <div class="card r-c">
            <h2 class="strong pr">
                {{
                    filterPrice(totalPaymentsCount)
                }}
            </h2>
            <h3 class="slim">
                Payments
            </h3>
        </div> -->
        <!-- RTR -->
        <div class="col-lg-4">
            <div class="card xldown o-c">
                <h2 class="strong pr">
                    {{
                        rtr || ''
                    }}
                </h2>
                <h3 class="slim">
                    RTR
                </h3>
            </div>
        </div>
        <!-- 4 -->
        <div class="col-lg-4">
            <div class="card xldown i-c">
                <h2 class="strong pr">
                    {{
                        balance || ''
                    }}
                </h2>
                <h3 class="slim">
                    Balance
                </h3>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
export default {
    name:'dashboard-content-component',
    data(){
        return {
            contents:[
                {
                    count:2,
                    countTitle:'test',
                    amount:232332,
                    amountTitle:'title'
                },
                {
                    count:2,
                    countTitle:'test',
                    amount:232332,
                    amountTitle:'title'
                },
                {
                    count:2,
                    countTitle:'test',
                    amount:232332,
                    amountTitle:'title'
                },
                {
                    count:2,
                    countTitle:'test',
                    amount:232332,
                    amountTitle:'title'
                },
            ]
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
        },
        filterPrice(val){
            let amount=Number(val);
            if(amount< 0 ){
                amount=amount-amount*2;
                amount=`- $${
                    this.formatMoney(Number(amount.toFixed(2)))
                }`;
            }else amount=`$${
                    this.formatMoney(Number(amount.toFixed(2)))
                }`;
            return amount;
        }
    },
    props:[
        'totalPaymentsAmount',
        'lender',
        'paymentsLeftAmount',
        'rtr',
        'balance'
    ]
}
</script>
