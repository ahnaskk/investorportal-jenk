<template>
  <div class="bank-wrapper" :class="wrapperBg()">
      <preloader class="loader" v-if="loading.submit" />
    <!-- add bank -->
    <div class="add-bank-seg" v-if="empty">
        <div class="add-action" @click="al.add = true">
            <icon class="add-icon" :icon="['fas','plus']" />
            <h2 class="add-label">
                Add New Bank
            </h2>
        </div>
    </div>
    <!-- bank info -->
    <div class="bank-info-seg" v-else>
        <div class="bank-info">
            <bgdec class="bgsec" :idle="!bank.default_credit && !bank.default_debit" />
            <div class="row head">
                <div class="col aside">
                    <bank-icon :idle="!bank.default_credit && !bank.default_debit" />
                </div>
                <div class="col main">
                    <h2 class="bank-title">
                        {{ bank.name }}
                    </h2>
                    <div class="crud-actions">
                        <edit-icon
                            :idle="!bank.default_credit && !bank.default_debit" @edit="al.edit = !al.edit"
                            class="edit-bt"
                        />
                        <delete-icon
                            :idle="!bank.default_credit && !bank.default_debit"
                            @click.native="deleteBank"
                            class="edit-bt delete"
                        />
                    </div>
                </div>
            </div>
            <div class="row body">
                <div class="col aside"></div>
                <div class="col main">
                    <div class="detail-group">
                        <h3 class="label">Account Name</h3>
                        <h4 class="value">
                            {{ bank.account_holder_name }}
                        </h4>
                    </div>
                    <div class="detail-group">
                        <h3 class="label">Account</h3>
                        <h4 class="value">
                            {{ bank.acc_number}}
                        </h4>
                    </div>
                    <div class="detail-group">
                        <h3 class="label">Routing</h3>
                        <h4 class="value">
                            {{ bank.routing }}
                        </h4>
                    </div>
                    <div class="detail-group">
                        <h3 class="label">Bank Type</h3>
                        <h4 class="value bank-type">
                            <span class="credit" v-if="bank.type.includes('credit')">
                                <icon :icon="['fas','check']"></icon> Credit
                            </span>
                            <span class="debit" v-if="bank.type.includes('debit')">
                                <icon :icon="['fas','check']"></icon> Debit
                            </span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="actions">
            <div class="set-default">
                <div class="action credit" :class="{active:bank.default_credit}">
                    <span class="check-box active" @click.stop.prevent="changeType('credit')">
                        <icon :icon="['fas','check']" />
                    </span>
                    <span class="label" @click.stop.prevent="changeType('credit')">
                        Default Credit
                    </span>
                </div>
                <div class="action debit" :class="{active:bank.default_debit}">
                    <span class="check-box active" @click.stop.prevent="changeType('debit')">
                        <icon :icon="['fas','check']" />
                    </span>
                    <span class="label" @click.stop.prevent="changeType('debit')">
                        Default Debit
                    </span>
                </div>
            </div>
            <div class="credit-debit">
                <span class="bt-spacer">
                    <button
                        type="button"
                        class="blue-bt credit action"
                        @click="al.credit=!al.credit"
                    >
                        Add Money
                    </button>
                </span>
                <span class="bt-spacer">
                    <button
                        type="button"
                        class="blue-bt debit action" 
                        @click="al.debit=!al.debit"
                    >
                        Withdraw Money
                    </button>
                </span>
            </div>
        </div>
    </div>
    <!-- add money -->
    <alert
        v-if="al.credit"
        @close="al.credit = false"
    >
        <add-money
            @submit="transactionRequest('debit',$event)"
            :loading="loading.credit"
            :accNo="bank.acc_number"
        />
    </alert>
    <!-- withdraw money -->
    <alert
        v-if="al.debit"
        @close="al.debit = false"
    >
        <withdraw-money
            @submit="transactionRequest('credit',$event)"
            :loading="loading.credit"
            :balance="liquidity"
            :accNo="bank.acc_number"
        />
    </alert>
    <!-- add bank -->
    <alert
        v-if="al.add"
        @close="al.add = false"
    >
        <add-bank @close="al.add = false" />
    </alert>
    <!-- edit bank -->
    <alert
        v-if="al.edit"
        @close="al.edit = false"
    >
        <edit-bank
            @close="al.edit = false"
            :bank="bank"
            :loading="loading.submit"
            @submit="submitUpdation"
        ></edit-bank>
    </alert>
  </div>
</template>

<script>
import bankIcon from './icons/bank'
import editIcon from './icons/edit'
import deleteIcon from './icons/delete'
import bgdec from './icons/bgdec'
import addBank from './sub/addBank'
import editBank from './sub/editBank'
import addMoney from './sub/addMoney'
import withdrawMoney from './sub/withdrawMoney'
import AddBank from './sub/addBank.vue'
import alert from '@c/alertBg'
import { bus } from './bus'
export default {
    name: 'bank',
    components:{
        bankIcon,
        editIcon,
        deleteIcon,
        bgdec,
        addBank,
        editBank,
        addMoney,
        withdrawMoney,
        alert
    },
    data(){
        return {
            active: {
                credit: false,
                debit: false
            },
            al:{
                add: false,
                credit: false,
                debit: false,
                edit: false
            },
            loading:{
                submit: false, // update bank details
                credit: false,
                debit: false
            },
            submissionFailed: false // used in changeType method
        }
    },
    methods:{
        async changeType(type){
            this.bank['default_'+type] = !this.bank['default_'+type]
            await this.submitUpdation(this.bank)
            if(this.submissionFailed)
                [ this.bank['default_'+type], this.submissionFailed ] = [ !this.bank['default_'+type], false ]
            else this.$store.dispatch('init/refresh')
        },
        deleteBank(){
            // console.log('deleting bank account ')
            this.$store.dispatch('init/prompt',{
                type: 'binary',
                message: 'Are you sure you want to delete this account?'
            }).then(r=>{
                if(r){
                    this.loading.submit = true
                    this.$store.dispatch('api/call',{
                        url: '/bank-delete',
                        post:{
                            id: this.bank.id
                        }
                    }).then(r=>{
                        if(r.status){
                            this.$store.dispatch('init/alert',{
                                type: 'success',
                                message: 'Account Deleted Successfully'
                            })
                            this.$store.dispatch('init/refresh')
                        }else throw new Error(r.errors && r.errors.message ? r.errors.message : 'Something went wrong!')
                    })
                    .catch(e=>{
                        this.$store.dispatch('init/alert',{
                            type: 'warning',
                            message: e.message || e.msg || 'Something went wrong!'
                        })
                    })
                    .finally(()=>this.loading.submit = false)
                }
            }).catch(e=>e)
            .finally(()=>console.log('promise finally'))
        },
        transactionRequest(type,amount){
            const post = {
                transaction_type: type,
                amount,
                id: this.bank.id
            }
            this.loading[type] = true
            this.$store.dispatch('api/call',{
                url: '/investor-ach-request-send',
                post
            }).then(r=>{
                if(r.status){
                    this.$store.dispatch('init/alert',{
                        type: 'success',
                        message: r.message || 'Request Added Successfully',
                        dropdown:{
                            key:"al."+type,
                            value:false
                        }
                    })
                }else{
                    this.$store.dispatch('init/alert',{
                        type: 'warning',
                        message: r.errors.message
                    })
                }
            }).catch(e=>{
                this.$store.dispatch('init/alert',{
                    type: 'warning',
                    message: e.message || e.msg || 'Something went wrong!'
                })
            }).finally(()=>this.loading[type] = this.al[type] = false)
        },
        async submitUpdation(post){
            if(this.loading.submit || !post) return
            this.loading.submit = true
            await this.$store.dispatch('api/call',{
                url: `bank-update/${this.bank.id}`,
                post
            }).then(r=>{
                if(r.status){
                    this.$store.dispatch('init/alert',{
                        type: 'success',
                        message: r.message || 'Account Details Updated Successfully!'
                    })
                    this.$store.dispatch('init/refresh')
                }
                else{
                    let e = r.errors
                    this.$store.dispatch('init/alert',{
                        type:'warning',
                        message: e.msg || e.message || 'Something Went Wrong!'
                    })
                }
            }).catch(e=>{
                this.$store.dispatch('init/alert',{
                    type:'warning',
                    message: e.msg || e.message || 'Something Went Wrong!'
                })
                // for using in changeType method
                this.submissionFailed = true
            }).finally(()=>{
                this.loading.submit = this.al.edit = false
            })
        },
        wrapperBg(){
            // {active:bank && bank.default_credit},
            if(this.bank){
                if (this.bank.default_credit && this.bank.default_debit) return "active-debit-credit"
                else if(this.bank.default_credit) return "active"
                else if(this.bank.default_debit) return "active-debit"
                else return 
            }
            else return
        }
    },
    props:{
        empty: Boolean,
        bank: Object,
        liquidity:[ String, Number ]
    },
    created(){
        bus.$on("closeAlert",(data)=>{
            if(data.key == "al.credit"){
                this.al.debit = data.value
            }
            if(data.key == "al.debit"){
                this.al.credit = data.value
            }
        })
    }
}
</script>

<style
    lang="scss"
    src="~c/banking/bank.scss"
    scoped
></style>