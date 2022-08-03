<template>
    <div
        id="popup"
        class="popup"
    >
        <div
            :class="[
                    'absolute-center',
                    'alert-box',
                    {logout}
                ]"
                v-click-outside="closeAlert">
            <!-- request more money -->
            <div class="contentRow money_prompt" v-if="money">
                <h3 class="request_title">
                    Enter The Amount
                </h3>
                <currency-input
                    type="text"
                    placeholder="$ 0.00"
                    v-model="inputData"
                    :class="{ err:errors.reqAmount || errors.minAmount }"
                    @change="validateMoney"
                    ref="currency"
                    @keydown.enter="submit"
                />
                <p class="err_msg" v-if="!errors.reqAmount && errors.minAmount">
                    Please enter an amount larger than $10
                </p>
                <p class="err_msg" v-if="errors.reqAmount">
                    Please enter an amount
                </p>
                <!-- buttons -->
                <div class="action_buttons action_sec justify-between">
                    <!-- cancel the action -->
                    <button
                        type="button"
                        class="bt_smooth bg_dark m-10"
                        @click="cancel"
                    >
                        Cancel
                    </button>
                    <!-- confirm the amount -->
                    <button
                        type="button"
                        class="bt_smooth bg_orange"
                        @click="submit"
                    >
                        Confirm
                    </button>
                </div>
            </div>
            <!-- logout -->
            <div class="contentRow logout_prompt" v-if="logout && !money">
                <div class="action_sec">
                    <h3 class="">
                        Come back soon
                    </h3>
                    <h4 class="slim">
                        Are you sure you want to Log out?
                    </h4>
                    <div class="action_buttons justify-between">
                        <button
                            type="button"
                            class="bt_smooth bg_dark m-10"
                            @click="cancel"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="bt_smooth bg_orange"
                            @click="confirmLogout"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
            <div class="contentRow confirm_prompt fd-cloumn" v-if="confirm">
                <h3 class="request_title">
                    {{ confirmText || 'Are you sure?' }}
                </h3>
                <!-- buttons -->
                <div class="action_buttons action_sec justify-between">
                    <!-- cancel the action -->
                    <button
                        type="button"
                        class="bt_smooth bg_dark m-10"
                        @click="cancel"
                    >
                        No, cancel
                    </button>
                    <!-- confirm the amount -->
                    <button
                        type="button"
                        class="bt_smooth bg_orange"
                        @click="confirmAction"
                    >
                        Yes, continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name:'popup',
    data(){
        return {
            toggleData:true,
            inputData:null,
            toggleKey:this.toggle,
            errors:{
                reqAmount:false,
                minAmount:false
            }
        }
    },
    methods:{
        cancel(){
            this.inputData=null;
            this.errors.reqAmount=false;
            this.errors.minAmount=false;
            this.$emit('cancel');
        },
        submit(){
            this.validateMoney();
            if(this.errors.reqAmount || this.errors.minAmount){
                // console.log('Please fill all the required fields');
                return;
            }else{
                this.$emit('submit',this.inputData);
                this.inputData=null;
                this.errors.reqAmount=false;
                this.errors.minAmount=false;
            }
        },
        confirmAction(){
            this.$emit('confirmAction',true)
        },
        closeAlert(){
            this.$emit('cancel');
        },
        confirmLogout(){
            this.$emit('logout',true);
        },
        validateMoney(){
            if(!this.inputData){
                this.errors.minAmount=true;
                this.errors.reqAmount=true;
            }else if(this.inputData && this.inputData <10){
                // console.log('2');
                this.errors.reqAmount=false;
                this.errors.minAmount=true;
            }else{
                // console.log('3');
                this.errors.minAmount=false;
                this.errors.reqAmount=false;
            }
        },
        focus(e){
            // console.log(e);
            e.target.focus();
        }
    },
    mounted(){
        // console.log('mounted the popup component');
        setTimeout(()=>{
            if(this.$refs.currency){
                console.log(this.$refs.currency)
            }
        },0);
    },
    props:{
        toggle:Boolean,
        logout:Boolean,
        money:Boolean,
        confirm:{
            default:false,
            type:Boolean
        },
        confirmText:{
            default:'',
            type:String
        }
    },
    mounted(){
        if(this.$refs.currency) this.$refs.currency.$el.focus();
    }
}
</script>