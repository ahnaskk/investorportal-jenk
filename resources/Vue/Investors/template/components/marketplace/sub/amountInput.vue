<template>
    <input
        type="text"
        :key="update"
        @change="change"
        v-currency="{
            currency: null,
            distractionFree: true,
            precision: 2
        }"
        :value="fix(grossAmount)"
        @keydown.enter.prevent="change"
    />
</template>

<script>
    import { CurrencyDirective } from 'vue-currency-input';
    const strip =  v => +v.toString().replace(/[^0-9\.]/g,'');
    export default {
        name: 'amount-input',
        props: [
            'grossAmount',
            'grossPercent',
            'isGrossValue',
            'max'
        ],
        data(){
            return {
                update: 0
            }
        },
        methods: {
            fix(n){
                const v = n.toString().split('.');
                if(v[1] && v[1].length > 2){
                    v[1] = v[1].slice(0,2);
                }
                return +v.join('.').replace(/[^0-9\.]/g,'');
            },
            change(e) {
                let val = strip(e.target.value);
                if(this.isGrossValue){
                    const totalPercent = 100 + this.grossPercent;
                    val = val/totalPercent*100;
                }
                this.$emit('input', val);
            }
        },
        directives: {
            currency: CurrencyDirective
        }
    }
</script>