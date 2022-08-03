<template>
  <div>
    <div class="switch-account-merchats-list">
        <div class="select-box" v-click-outside="()=> close()">
            <ul>
                <li v-for="(item,i) in merchants" :key="i+0.1">
                    <button
                        href="javascript:void(0)"
                        @click.prevent="switchAccount(item)"
                        v-loader="item.loading"
                        class="account-switch-bt"
                        type="button"
                    >
                        {{ item.name }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
  </div>
</template>

<script>
export default {
    data(){
        return {

        }
    },
    props:{
        merchants:{
            default:null,
            required:true
        }
    },
    methods:{
        close(){
            this.$store.dispatch('init/popup',{
                switchAccount:{
                    show:false
                }
			})
        },
        switchAccount(m){
            const id = m.id;
            this.$store.dispatch('api/getData',{
                force: false,
                url: '/change-merchant',
                field: 'changeMerchant',
                post:{
                    merchant_id:id
                },
                handler:this.switchHandler
            })
            .catch(e => {
                
            })
            // this.$api.post('/change-merchant',{
            //     merchant_id: id
            // },{
            //     headers: this.headers
            // }).then(d=>{
            //     if(d.status==200) return d.data;
            // }).then(d=>{
            //     if(d.status){
            //         window.location = window.location;
            //     }else{
            //         console.log('show the error message: home.vue > 156')
            //     }
            // }).catch(e=>{
            //     console.log('error when trying to switch the account');
            // })
            // .finally(()=>{
            //     m.loading = false;
            // })
        },
        /**
         * @param d resolved data from the "getData" action
         */
        switchHandler(d){
            if(d.status){
                this.close()
                this.reloadDashboard()
            }
        },
        reloadDashboard(){
            this.$store.dispatch('init/refresh')
            if(this.$route.path != '/merchants/dashboard'){
                this.$router.push({
                    path:'/merchants/dashboard'
                })
            }
        }
    }
}
</script>