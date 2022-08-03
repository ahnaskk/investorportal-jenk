<template>
  <section class="merchant-dashboard">
    <emptyBox :msg="errorMsg" v-if="error && !loading" />
    <infoSection
      v-if="data && data.length && !error && !loading"
      :data="data[0]"
    />
    <preloader v-if="loading || tableLoading && !error" />
    <div
      class="table-section"
    >
      <tableSection
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
        :pagination="tableData ? tableData.pagination : null"
        :searchKeyword="request.keyword"
      >
        <emptyBox
            v-if="!tableLoading && !loading && tableData.data.length == 0"
            msg="Nothing to display"
            :idle="true"
        />
        <table
          class="data-table"
          v-if="data && tableData && tableData.data.length && !error"
        >
          <thead>
            <tr>
              <ths
                v-for="(th,i) in [
                  'Date Settled',
                  'Total Payment',
                  'Management Fee',
                  'To Participant',
                  /*'Transaction Type',
                  'Reason'*/
                ]"
                :key="i"
              >{{th}}</ths>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(d,i) in tableData.data"
              :key="i+0.1"
            >
              <td>
                {{ d.date_settled }}
              </td>
              <td>
                {{ d.total_payment }}
              </td>
              <td>
                {{ d.management_fee }}
              </td>
              <td>
                {{ d.to_participant }}
              </td>
              <!-- <td>
                {{ d.transaction_type }}
              </td>
              <td>
                {{ d.rcode || '--' }}
              </td> -->
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td class="title"></td>
              <td class="title">{{ tableData.total.total_payment }}</td>
              <td class="title">{{ tableData.total.total_mgmnt_fee }}</td>
              <td class="title">{{ tableData.total.total_to_participant }}</td>
              <!-- <td></td>
              <td></td> -->
            </tr>
          </tfoot>
        </table>
      </tableSection>
    </div>
  </section>
</template>

<script>
  import infoSection from '@c/merchantDashboard/infoSection'
  import tableSection from '@c/tableSection'
  import { mapState } from 'vuex'
  const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g,'')
  export default {
    name: 'merchant-dashboard-view',
    data() {
      return {
        error: false,
        errorMsg: null,
        id: null,
        request:{
          limit: 10,
          keyword: null,
          page: 1,
          from: 0,
          sort_by: '',
          sort_order: '',
          request_from:'web'
        }
      }
    },
    components: {
      infoSection,
      tableSection
    },
    computed: {
      ...mapState('api', {
        data: s => s.merchantData,
        loading: (s) => s.loading.merchantData,
        tableData: s => s.merchantPaymentData,
        tableLoading: s => s.loading.merchantPaymentData
      }),
    },
    methods: {
      initPage() {
        this.id = this.$route.params.id
        const
          replace =  v => v.replace(/%/g,' '),
          [q,r] = [this.$route.query,this.request]
        if(q.range) r.limit = strip(q.range)
        if(q.keyword) r.keyword = replace(q.keyword)
        if(q.page) r.page = q.page
        if(q.from) r.sDate = replace(q.from)
        if(q.to) r.eDate = replace(q.to)
        if(q.sortBy) r.sort_by = q.sortBy
        if(q.sortOrder) r.sort_order = q.sortOrder
      },
      getData(
        config = {
          url: '/investor-merchant-view',
          field: 'merchantData',
          post: {},
          force: true
        }
      ){
        return new Promise((res,rej)=>{
          if (this.id) config.post.merchantId = this.id
          this.$store.dispatch('api/getData', config)
            .then(()=>res())
            .catch(e => {
              console.log('merchant data api error', e)
              if (e.message) this.errorMsg = e.message
              if (e.msg) this.errorMsg = e.msg
              this.error = true
              rej()
            })
        })
      },
      getPaymentData(){
        if(this.data && !this.error){
          const { limit, page, keyword, sort_by, sort_order,request_from } = this.request
          let offset = limit * (page - 1)
          this.getData({
            url: '/investor-merchant-payment-view',
            field: 'merchantPaymentData',
            vals:['data','pagination','total'],
            post: {
              limit,
              offset,
              keyword,
              sort_by,
              sort_order,
              request_from
            },
            force: true
          })
        }
      },
      on_create() {
        this.initPage()
        this.getData()
          .then(()=>{
            this.getPaymentData()
          }).catch(e=>e)
      }
    }
  }
</script>

<style lang="scss" scoped src="~v/merchants.scss"></style>