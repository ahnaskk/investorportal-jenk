<template>
    <section class="dashboard-row">
        <emptyBox :msg="errorMsg" v-if="error" />
        <preloader v-if="loading && !error" />
        <tableSection
            class="table-section"
            v-if="data && merchantsList && !loading && !error"
            :rangeSelector="true"
            :showSlot="true"
            :downloadLink="data['download-url']"
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
            :filterFrom="request.sDate"
            :filterTo="request.eDate"
            :filterMerchant="request.merchant_id"
            :searchKeyword="request.keyword"
            :pagination="data.pagination"
            :merchants="merchantsList"
            :multiple="true"
        >
            <table class="data-table">
                <thead>
                    <tr>
                        <!-- <th></th> -->
                        <ths v-for="(th,i) in [
                                'Merchant Name',
                                'Funded Date',
                                'Share (%)',
                                'Net Investment',
                                'RTR',
                                'Management Fee',
                                /*'Commission',*/
                                'Syndication Fee',
                                /*'Under Writing Fee',*/
                                'Total Invested',
                                /*'Created On'*/
                            ]" :key="i"
                        >
                            {{ th }}
                        </ths>
                    </tr>
                </thead>
                <tbodyToggler
                    v-for="(td,i) in data.data"
                    :data="td"
                    :key="i+0.5"
                    :sDate="request.sDate"
                    :eDate="request.eDate"
                />
                <tfoot v-if="data.total">
                    <tr>
                        <!-- merchant name -->
                        <th></th>
                        <!-- funded date -->
                        <th></th>
                        <!-- share  (%) -->
                        <th></th>
                        <!-- funded amount -->
                        <th><b>{{ data.total.i_amount || "--" }}</b></th>
                        <!-- rtr -->
                        <th><b>{{ data.total.i_rtr || "--" }}</b></th>
                        <!-- management fee -->
                        <th><b>{{ data.total.mgmnt_fee || "--" }}</b></th>
                        <!-- commission -->
                        <!-- <th><b>{{ data.total.commission_amount || "--" }}</b></th> -->
                        <!-- syndication fee -->
                        <th><b>{{ data.total.pre_paid || "--" }}</b></th>
                        <!-- underwriting fee -->
                        <!-- <th><b>{{ data.total.under_writing_fee || "--" }}</b></th> -->
                        <!-- total invested -->
                        <th><b>{{ data.total.invested_amount || "--" }}</b></th>
                        <!-- <th></th> -->
                    </tr>
                </tfoot>
            </table>
        </tableSection>
    </section>
</template>

<script>
import tableSection from '@c/investmentTableSection'
import tbodyToggler from '@c/reports/InvestmentTbodyToggler'
import {
    mapState
} from 'vuex'
const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g, '')
export default {
    name: 'investment-reports',
    data() {
        return {
            error: false,
            errorMsg: null,
            request: {
                limit: 10,
                keyword: '',
                page: 1,
                sDate: '',
                eDate: '',
                merchant_id: '',
                sort_by: '',
                sort_order: ''
            },
            merchantsList: null
        }
    },
    computed: {
        ...mapState('api', {
            data: s => s.investmentReports,
            loading: (s) => s.loading.investmentReports
        }),
    },
    methods: {
        initPage() {
            const replace = v => v.replace(/%/g, ' ')
            const [q, r] = [this.$route.query, this.request]
            if (q.range) r.limit = strip(q.range)
            if (q.keyword) r.keyword = replace(q.keyword)
            if (q.merchant_id) r.merchant_id = replace(q.merchant_id)
            if (q.page) r.page = q.page
            if (q.from) r.sDate = replace(q.from)
            if (q.to) r.eDate = replace(q.to)
            if(q.sortBy) r.sort_by = q.sortBy
            if(q.sortOrder) r.sort_order = q.sortOrder
        },
        getMerchantList(){
            const post = {}
            this.$store.dispatch('api/call', {
                force: true,
                url: '/merchants-list',
                post
            }).then(r=>{
                if(r&&r.status){
                    this.saveMerchants(r.data.list)
                }
            })
            .catch(e=>console.log('merchant-list api error',e))
        },
        getData() {
            let {
                limit,
                sDate,
                eDate,
                merchant_id,
                page,
                keyword,
                sort_by,
                sort_order
            } = this.request;
            const generateDate = (d) => {
                if (d) {
                    const
                        dt = new Date(d),
                        y = dt.getFullYear(),
                        m = dt.getMonth(),
                        dy = dt.getDate()
                    return `${y}-${m+1}-${dy}`
                } else return null;
            }
            sDate = generateDate(sDate)
            eDate = generateDate(eDate)
            let offset = limit * (page - 1)
            offset = offset < 0 ? 0 : offset
            const post = {
                limit,
                offset,
                merchant_id,
                merchant_name: '',
                sort_by,
                sort_order
            }
            if (sDate) post.sDate = sDate
            if (eDate) post.eDate = eDate
            if(keyword) post.keyword = keyword
            this.$store.dispatch('api/getData', {
                    force: true,
                    url: '/investment-report',
                    field: 'investmentReports',
                    post
                })
                .catch(e => {
                    if (e.message) this.errorMsg = e.message
                    if (e.msg) this.errorMsg = e.msg
                    this.error = true
                })
        },
        saveMerchants(list){
            this.$set(
                this,
                'merchantsList',
                Object.entries(list).map(el=>({label:el[0],value:el[1]}))
            )
        },
        on_create() {
            this.initPage()
            this.getData()
            // call this method to save the merchants
            this.getMerchantList()
        }
    },
    components: {
        tableSection,
        tbodyToggler
    }
}
</script>

<style lang="scss" scoped src="~v/reports.scss"></style>