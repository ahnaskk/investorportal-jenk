export default {
    computed:{
        headers(){
            let token=this.$store.getters.merchantToken;
            if(token) token="Bearer "+token;
            return {
                Authorization:token
            };
        }
    }
}