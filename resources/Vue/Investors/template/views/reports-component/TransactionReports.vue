<template>
    <section class="dashboard-row">
        <emptyBox :msg="errorMsg" v-if="error" />
        <preloader v-if="transactionLoading && !error" />
        <tableSection class="table-section" v-if="transactionData && !transactionLoading && !error" :rangeSelector="true" :showSlot="true" :downloadLink="transactionData['download-url']" :rangeFilters="[
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
            ]" :filterRange="request.limit" :filterFrom="request.sDate" :filterTo="request.eDate" :filterMerchant="request.account_no" :searchKeyword="request.keyword" :pagination="transactionData.pagination">
            <table class="data-table">
                <thead>
                    <tr>
                        <!-- <th></th> -->
                        <ths v-for="(th,i) in [
                                'Category',
                                'Method',
                                'Type',
                                'Amount',
                                'Date',
                               /* 'AccountNo'*/
                            ]" :key="i">
                            {{ th }}
                        </ths>
                    </tr>
                </thead>
                <tbodyToggler v-for="(td,i) in transactionData.data" :data="td" :key="i+0.5" />
                <tfoot v-if="transactionData && transactionData.total">
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="title">{{transactionData.total.amount}}</td>
                        <td></td>
                        <!-- <td></td> -->
                    </tr>
                </tfoot>
            </table>
        </tableSection>
    </section>
</template>

<script>
import tableSection from '@c/TransactionTableSection'
import tbodyToggler from '@c/reports/TransactionTbodyToggler'
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
                account_no: '',
                sort_by: '',
                sort_order: ''
            }
        }
    },
    computed: {
        ...mapState('api', {
            transactionData: s => s.transactionReports,
            transactionLoading: (s) => s.loading.transactionReports
        }),
    },
    methods: {
        initPage() {
            const replace = v => v.replace(/%/g, ' ')
            const [q, r] = [this.$route.query, this.request]
            if (q.range) r.limit = strip(q.range)
            if (q.keyword) r.keyword = replace(q.keyword)
            if (q.account_no) r.account_no = replace(q.account_no)
            if (q.page) r.page = q.page
            if (q.from) r.sDate = replace(q.from)
            if (q.to) r.eDate = replace(q.to)
            if (q.sortBy) r.sort_by = q.sortBy
            if (q.sortOrder) r.sort_order = q.sortOrder
        },
        getData() {
            let {
                limit,
                sDate,
                eDate,
                account_no,
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
                account_no,
                sort_by,
                sort_order
            }
            if (sDate) post.sDate = sDate
            if (eDate) post.eDate = eDate
            if (keyword) post.keyword = keyword
            this.$store.dispatch('api/getData', {
                    force: true,
                    url: '/transaction-report',
                    post,
                    field: 'transactionReports',
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