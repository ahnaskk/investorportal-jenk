<template>
    <section class="dashboard-row">
        <emptyBox :msg="errorMsg" v-if="error" />
        <preloader v-if="merchantLoading && !error" />
        <tableSection
            class="table-section"
            v-if="merchantData && !merchantLoading && !error"
            :rangeSelector="true"
            :showSlot="true"
            :downloadLink="merchantData['download-url']"
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
            :filterMerchant="request.days"
            :searchKeyword="request.keyword"
            :pagination="merchantData.pagination"
        >
            <table class="data-table">
                <thead>
                    <tr>
                        <ths v-for="(th,i) in [
                                'Id',
                                'Merchant',
                                'Funded Date',
                                'Default Date',
                                'Default Invested Amount',
                                'Default RTR Amount',
                            ]" :key="i">
                            {{ th }}
                        </ths>
                    </tr>
                </thead>
                <tbodyToggler v-for="(td,i) in merchantData.data" :data="td" :key="i+0.5" />
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="title">{{merchantData.total.default_amount}}</td>
                        <td class="title">{{merchantData.total.investor_rtr}}</td>
                    </tr>
                </tfoot>
            </table>
        </tableSection>
    </section>
</template>

<script>
import tableSection from '@c/DefaultRateMerchantTableSection'
import tbodyToggler from '@c/reports/DefaultRateMerchantTbodyToggler'
import {
    mapState
} from 'vuex'
const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g, '')
export default {
    name: 'reports-view',
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
                days: '',
                sort_by: '',
                sort_order: ''
            }
        }
    },
    computed: {
        ...mapState('api', {
            merchantData: s => s.defaultRateReports,
            merchantLoading: (s) => s.loading.defaultRateReports
        }),
    },
    methods: {
        initPage() {
            const replace = v => v.replace(/%/g, ' ')
            const [q, r] = [this.$route.query, this.request]
            if (q.range) r.limit = strip(q.range)
            if (q.keyword) r.keyword = replace(q.keyword)
            if (q.days) r.days = replace(q.days)
            if (q.page) r.page = q.page
            if (q.from) r.sDate = replace(q.from)
            if (q.to) r.eDate = replace(q.to)
            if(q.sortBy) r.sort_by = q.sortBy
            if(q.sortOrder) r.sort_order = q.sortOrder
        },
        getData() {
            let {
                limit,
                sDate,
                eDate,
                days,
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
                days,
                sort_by,
                sort_order
            }
            if (sDate) post.sDate = sDate
            if (eDate) post.eDate = eDate
            if (keyword) post.keyword = keyword
            this.$store.dispatch('api/getData', {
                    force: true,
                    url: '/default-rate-merchant-report',
                    post,
                    field: 'defaultRateReports',
                    transformer: () => {}
                })
                .catch(e => {
                    console.log('reports data api error', e)
                    if (e.message) this.errorMsg = e.message
                    if (e.msg) this.errorMsg = e.msg
                    this.error = true
                })
        },
        on_create() {
            this.initPage()
            this.getData()
        }
    },
    components: {
        tableSection,
        tbodyToggler
    }
}
</script>

<style lang="scss" scoped src="~v/reports.scss"></style>