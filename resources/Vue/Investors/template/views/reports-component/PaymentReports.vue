<template>
  <section class="dashboard-row">
    <emptyBox
      :msg="errorMsg"
      v-if="error"
    />
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
      hideSearch
      :pagination="data.pagination"
      :merchants="merchantsList"
      :multiple="true"
    >
      <table class="data-table">
        <thead>
          <tr>
            <th></th>
            <ths
              v-for="(th,i) in [
                'Merchant',
                'Merchant Id',
                'Funded Date',
                'Debited',
                'Total Payments',
                'Management Fee',
                'Net amount',
               /* 'Principal',
                'Profit',
                'Last Rcode',*/
                'Last Successful Payment Date',
                /*'Last Payment Amount',
                'Participant RTR',
                'Participant RTR Balance'*/
              ]"
              :key="i"
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
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <!-- total debited -->
                <td class="title">{{ data.total.total_debited }}</td>
                <!-- total payments -->
                <td class="title">{{ data.total.total_participant_share }}</td>
                <!-- management fee -->
                <td class="title">{{ data.total.total_mgmnt_fee }}</td>
                <!-- net amount -->
                <td class="title">{{ data.total.total_net_participant_payment }}</td>
                <!-- principal -->
                <!-- <td class="title">{{ data.total.total_pricipal }}</td> -->
                <!-- profit -->
                <!-- <td class="title">{{ data.total.total_profit }}</td> -->
                <!-- <td></td> -->
                <td></td>
                <!-- last payment amount -->
                <!-- <td></td>
                <td></td>
                <td></td> -->
            </tr>
        </tfoot>
      </table>
    </tableSection>
  </section>
</template>

<script>
  import tableSection from '@c/tableSection'
  import tbodyToggler from '@c/reports/tbodyToggler'
  import { mapState } from 'vuex'
  const strip = v => +v.toString().replace(/[^0-9\.]/g,'')
  export default {
    name: 'payment-reports',
    data() {
      return {
        error: false,
        errorMsg: null,
        request:{
          limit: 10,
          keyword: '',
          page: 1,
          sDate: '',
          eDate: '',
          merchant_name: '',
          merchant_id: '',
          sort_by: '',
          sort_order: ''
        },
        merchantsList: null
      }
    },
    computed: {
      ...mapState('api', {
        data: s => s.reports,
        loading: (s) => s.loading.reports
      }),
    },
    methods: {
      initPage(){
        const replace =  v => v.replace(/%/g,' ')
        const [q,r] = [this.$route.query,this.request]
        if(q.range) r.limit = strip(q.range)
        if(q.keyword) r.keyword = replace(q.keyword)
        if(q.merchant_name) r.merchant_name = replace(q.merchant_name)
        if(q.merchant_id) r.merchant_id = replace(q.merchant_id)
        if(q.page) r.page = q.page
        if(q.from) r.sDate = replace(q.from)
        if(q.to) r.eDate = replace(q.to)
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
      saveMerchants(list){
          this.$set(
              this,
              'merchantsList',
              Object.entries(list).map(el=>({label:el[0],value:el[1]}))
          )
      },
      getData(){
        let { limit, sDate, eDate, merchant_name, merchant_id, page, keyword, sort_by, sort_order } = this.request;
        const generateDate = (d)=>{
          if(d){
            const
              dt = new Date(d),
              y = dt.getFullYear(),
              m = dt.getMonth(),
              dy = dt.getDate()
              return `${y}-${m+1}-${dy}`
          }else return null;
        }
        sDate = generateDate(sDate)
        eDate = generateDate(eDate)
        let offset = limit * (page - 1)
        offset = offset < 0 ? 0 : offset 
        const post = {limit, offset, merchant_name, merchant_id, sort_by, sort_order}
        if(sDate) post.sDate = sDate
        if(eDate) post.eDate = eDate
        if(keyword) post.keyword = keyword
        this.$store.dispatch('api/getData',{
          force: true,
          url:'/payment-report',
          post,
          field: 'reports'
        })
        .catch(e=>{
          if(e.message) this.errorMsg = e.message
          if(e.msg) this.errorMsg = e.msg
          this.error = true
        })
      },
      on_create() {
        this.initPage()
        this.getData()
        // call this method to save the merchants
        this.getMerchantList()
      }
    },
    components:{
      tableSection,
      tbodyToggler
    }
  }
</script>

<style
  lang="scss"
  scoped
  src="~v/reports.scss"
></style>