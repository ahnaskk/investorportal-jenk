<template>
    <div class="wrapper">
        <div>
            <h3>You have enabled two step verification,save this emergency recovery key</h3>
            <span>If you lose access to your phone, you won't be able to log in to your account without this key. Print or write down this key without letting anyone see it.</span>
            <ul class="r-codes" ref="rcodes">
                <li v-for="(code,i) in codes"
                :key="i"
                >
                    {{ code }}
                </li>
            </ul>
            <div class="controls">
                <v-select
                class="save-options"
                :options="saveOptions"
                placeholder="Save Options"
                v-model="selectFeild"
                ></v-select>
                <button class="btn blue-bt" @click="finish">{{ buttonText }}</button>
            </div>
        </div>
        <Alert
            v-if="success"
            @close="() => this.success =false"
            :data="data"
        />
    </div>
</template>

<script>
import Alert from '@c/alert';
export default {
    props:{
        codes:{
            type:Array,
            required:true
        }
    },
    components:{
        Alert
    },
    data(){
        return{
            saveOptions:[
                {"label":'Wrote it down',value:0},
                {"label":'Copy to clipboard',value:1},
            ],
            selectFeild:null,
            success:false,
            copyText:'Copy and finish',
            data:{
                message:'Two factor verification enabled successfully',
                type:'success'
            }
        }
    },
    methods:{
        finish(){
            if( this.selectFeild.value != null ){
                if(this.selectFeild.value == 1){
                    this.selectText(this.$refs.rcodes)
                    document.execCommand('copy');
                    this.success =true
                }
                this.success = true
            }
            else this.success = false
        },
        selectText(element) {
            var range;
            if (document.selection) {
                // IE
                range = document.body.createTextRange();
                range.moveToElementText(element);
                range.select();
            } else if (window.getSelection) {
                range = document.createRange();
                range.selectNode(element);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }
        }
    },
    computed:{
        buttonText(){
            if(this.selectFeild.value == 1){
                return this.copyText
            }
            else return "Let's Finish"
        }
    },
    created(){
        this.selectFeild = this.saveOptions[0]
    }
}
</script>

<style
  lang="scss"
  scoped
  src="../../styles/two-factor/recovery-codes.scss"
>
</style>