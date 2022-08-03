<template>
    <bg 
        @close="closePrompt"
        ref="bg"
    >
        <div class="prompt-box">
            <div class="content row">
                <p class="message">
                    {{ data.message }}
                </p>
            </div>
            <div class="action row">
                <button
                    class="bt cancel"
                    type="button"
                    @click="answer(0)"
                >NO</button>
                <button
                    class="bt ok"
                    type="button"
                    @click="answer(1)"
                >YES</button>
            </div>
        </div> 
    </bg>
</template>

<script>
import bg from '@c/alertBg'
export default {
    name: 'prompt',
    components:{
        bg
    },
    methods:{
        closePrompt(){
           this.$emit('close')
        },
        answer(a){
            this.data.res(a)
            this.closePrompt()
        }
    },
    props:[
        'data'
    ]
}
</script>

<style
    lang="scss"
    scoped
    src="~c/prompt.scss"
></style>