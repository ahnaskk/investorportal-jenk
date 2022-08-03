<template>
    <div class="marketplace-view">
        <div
            class="filter-wrapper"
            v-if="filters"
            v-click-outside="()=>open=false"
        >
            <!-- filter-->
            <button
                class="filters"
                @click="open = true"
                v-if="0 && $route.name == 'marketplace' && filters && filters.length"
            >
                Filters
                <img :src="require('@image/icons/filter-list.svg').default" alt="">
            </button>
            <filterButton
                @click="open = true"
            ></filterButton>
            <!-- Side Menu -->
            <div
                class="side-menu"
                :class="{open}"
            >
                <div class="close-row">
                    <button class="close-btn" @click="open = false">
                        <img :src="require('@image/icons/close-icon.svg').default" alt="">
                    </button>
                </div>
                <div class="form-box">
                    <form @submit.prevent>
                        <div
                            class="input-group"
                            v-for="(f,i) in filters"
                            :key="i"
                        >
                            <h2 class="input-title">
                                {{ f.name }}
                            </h2>
                            <selectBox
                                :options="f.filters"
                                label="name"
                                @select="filterModel[f.name] = $event"
                            />
                        </div>
                        <div class="input-group">
                            <button
                                class="btn"
                                @click.prevent="filter"
                            >
                                Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /filter -->
        </div>
        <preloader v-if="loading" />
        <emptyBox
            :msg="errorMsg"
            v-if="!loading && error || !loading && !data && !error" />
        <emptyBox
            msg="No Data to Display"
            v-if="!loading && data && !data.length"
        />
        <div class="servicebox-row" v-if="!loading && data && data.length &&!error">
            <div
                class="col"
                v-for="(item,i) in data"
                :key="i"
                :class="{hidden:!item}"
            >
                <serviceBox
                    v-if="item"
                    :data="item"
                    :openPopUp="item.id == merchantId"
                    :amountPassed="item.id == merchantId ? amount : null"
                    :uniqueIndex="i"
                    @remove="data.splice(i,1)"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import serviceBox from '@c/marketplace/serviceBox'
    import selectBox from '@c/selectBox'
    import filterButton from '@c/filterButton'

    import { mapGetters, mapState } from 'vuex'
    export default {
        name: 'market-place-view',
        data(){
            return {
                error: false,
                errorMsg:  null,
                open: false,
                filterModel:{},
                merchantId:null,
                amount:null
            }
        },
        computed:{
            ...mapState('api',{
                data: s => s.marketplace,
                loading: (s) => s.loading.marketplace
            }),
            ...mapGetters({
                filters: 'api/marketplaceFilters'
            })
        },
        methods:{
            filter(e){
                Object.entries(this.filterModel).forEach(el=>{
                    if(el[1] && el[1].id == undefined) this.filterModel[el[0]] = null;
                });
                this.getMerchants(true);
                this.open = false;
            },
            getMerchants(force){
                this.error = false;
                this.$store.dispatch('api/getData',{
                    force: force || false,
                    url: '/marketplace',
                    field: 'marketplace',
                    post:{
                        offset: 0,
                        filter: this.filterModel
                    }
                })
                .catch(e=>{
                    if(e && e.message) this.errorMsg = e.message;
                    if(e && e.msg) this.errorMsg = e.msg;
                    this.error = true;
                })
            },
            getFilters(){
                this.$store.dispatch('api/getData',{
                    force: false,
                    url: '/marketplace-filters',
                    field: 'marketplaceFilters',
                    transformer: (data)=>{
                        if(data.filters.length){
                            data.filters.forEach(fl=>{
                                this.$set(this.filterModel,fl.name,null);
                                fl.filters.unshift({
                                    text: `- - Select ${fl.name} - -`,
                                    value: null
                                });
                            });
                        }
                    }
                })
                .catch(e=>{
                    if(e && e.msg) this.errorMsg = e.msg;
                    this.error = true;
                })
            }
        },
        created(){
            if(!this.data) this.getMerchants();
            if(!this.filters) this.getFilters();
            let merchantId = localStorage.getItem('merchant_id')
            let amount = localStorage.getItem('amount')
            if (merchantId) {
                this.merchantId = merchantId 
                localStorage.removeItem('merchant_id')
            }
            else this.merchantId = null
            if(amount){
                this.amount = amount
                localStorage.removeItem('amount')
            }
        },
        components: {
            serviceBox,
            selectBox,
            filterButton
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~v/marketplace/marketplace.scss"
></style>