<template>
    <section class="merchant-dashboard">
        <preloader v-if="loading && !searching" />
        <emptyBox
            :msg="errorMsg"
            v-if="!loading && error"
        />
        <div
            class="download-btn-box"
            v-if="data && data['download-url']"
        >
            <a
                class="table-action-bt w-auto"
                type="button"
                :href="data['download-url']"
                target="_blank"
            >Download All</a>
        </div>
        <tableSection
            v-if="!loading && data && data.data && !error"
            class="table-section"
            :showSlot="true"
            :rangeFilters="[
                {
                  text: 10,
                  value: 10,
                },
                {
                  text: 20,
                  value: 20,
                },
                {
                    text: 30,
                    value: 30
                }
            ]"
            :filterRange="request.limit"
            :searchKeyword="request.keyword"
            :pagination="data.pagination"
        >
            <emptyBox
                msg="Nothing to Display"
                v-if="data && !data.data.length"
            />
            <table class="data-table" v-else>
                <thead>
                    <tr
                        class="sticky-title"
                        style="--bg:#f6f8fb"
                    >
                        <ths
                            v-for="(v,i) in [
                                '#',
                                'Merchant',
                                'Date Funded',
                                'Net Investment',
                                /*'Commission',
                                'Under Writing Fee',*/
                                'Syndication Fee',
                                'RTR',
                                'Rate',
                                'CTD',
                                'Annualized Rate',
                                'Complete',
                                'Status',
                                'Last Successful Payment Date',
                                'Action'
                            ]"
                            :key="i+0.1"
                            :exceptions="[
                                'Sl No',
                                'Action'
                            ]"
                        >
                            {{ v }}
                        </ths>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(m,i) in data.data"
                        :key="i"
                    >
                        <td class="index">{{ data.pagination.from+i }}</td>
                        <td>{{ m.name }}</td>
                        <td>{{ m.date_funded }}</td>
                        <td>{{ m.amount.value }}</td>
                        <!-- <td>{{ m.commission.value_percent }}</td>
                        <td>{{ m.under_writing_fee.value }}</td> -->
                        <td>{{ m.syndication_fee.value }}</td>
                        <td>{{ m.invest_rtr }}</td>
                        <td>{{ m.factor_rate }}</td>
                        <td>{{ m.ctd }}</td>
                        <td>{{ m.annualized_rate }}</td>
                        <td>{{ m.complete_percentage }}</td>
                        <td>{{ m.sub_statuses_name }}</td>
                        <td>{{ m.last_payment_date }}</td>
                        <td>
                            <!-- action button -->
                            <button
                                class="table-action-bt"
                                type="button"
                                @click="$router.push(`/merchants/${m.id}`).catch(e=>{})"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="index title">TOTAL</td>
                        <td></td>
                        <td></td>
                        <td class="title">
                            {{ data.total.funded_total }}
                        </td>
                        <!-- <td class="title">
                            {{ data.total.commission_total }}
                        </td>
                        <td></td> -->
                        <td></td>
                        <td class="title">
                            {{ data.total.rtr_total }}
                        </td>
                        <td></td>
                        <td class="title">
                            {{ data.total.ctd_total }}
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </tableSection>
    </section>
</template>

<script>
    import infoSection from '@c/merchantDashboard/infoSection';
    import tableSection from '@c/tableSection';
    import { mapState } from 'vuex';
    const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g,'');
    export default {
        name: 'merchants-view',
        data(){
            return {
                error: false,
                errorMsg: null,
                request:{
                    limit: 10,
                    keyword: '',
                    page: 1,
                    sort_by: '',
                    sort_order: '',
                    request_from:'web'
                },
                empty: false,
                searching: false
            }
        },
        computed:{
            ...mapState('api',{
                data: s => s.merchants,
                loading: (s) => s.loading.merchants
            })
        },
        components: {
            infoSection,
            tableSection
        },
        methods: {
            log(e){
                console.log(e)
            },
            initPage(){
                const [q,r] = [this.$route.query,this.request]
                if(q.range) r.limit = strip(q.range)
                if(q.keyword) r.keyword = q.keyword.replace(/%/g,' ')
                if(q.page) r.page = q.page
                if(q.sortBy) r.sort_by = q.sortBy
                if(q.sortOrder) r.sort_order = q.sortOrder
            },
            getData(){
                const {keyword,limit,page,sort_by,sort_order,request_from} = this.request;
                let offset = limit * (page - 1);
                offset = offset < 0 ? 0 : offset; 
                const post = {
                    keyword,
                    offset,
                    limit,
                    sort_order,
                    sort_by,
                    request_from
                };
                this.$store.dispatch('api/getData', {
                    force: true,
                    url: '/investor-merchant-list',
                    field: 'merchants',
                    vals:['data','total','pagination','download-url'],
                    post
                }).catch(e=>{
                    if(e.msg) this.errorMsg = e.msg;
                    this.error = true;
                });
            },
            on_create() {
                this.initPage();
                this.getData();
            }
        }
    }
</script>

<style lang="scss" scoped src="~v/merchants.scss"></style>