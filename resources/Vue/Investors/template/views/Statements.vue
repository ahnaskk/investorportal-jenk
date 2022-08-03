<template>
  <section class="dashboard-row">
    <!-- <emptyBox
      :idle="true"
      msg="Coming Soon!"
    /> -->
    <emptyBox
      :idle="true"
      :msg="errorMsg"
      v-if="error"
    />
    <preloader
      v-if="loading && !error"
    />
    <tableSection
      class="table-section"
      v-if="data && !loading && !error"
      :rangeSelector="false"
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
      :hideHeader="false"
      :showSlot="true"
      :filterRange="request.limit"
      :searchKeyword="request.keyword"
      :pagination="data.pagination"
      searchPlaceholder="Type here..."
    >
      <table class="data-table">
        <thead>
          <tr>
            <ths
              v-for="(th,i) in [
                'Statements',
                /*'PDF Statement',*/
                'Generated Date',
                'Period'
              ]"
              :exceptions="['Period']"
              :key="i"
            >{{th}}</ths>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(d,i) in data.data"
            :key="i"
          >
            <!-- CSV link -->
            <td>
              <button
                class="table-download-bt"
                @click="requestDownload(d,'csv')"
              >
                <img :src="require('@image/icons/download-icon.svg').default" />
                {{ d.file_name }}
              </button>
            </td>
            <!-- PDF link -->
            <!-- <td>
              <button
                class="table-download-bt"
                @click="requestDownload(d,'pdf')"
              >
                <img :src="require('@image/icons/download-icon.svg').default" />
                {{ d.file_name }}
              </button>
            </td> -->
            <td>
              {{ d.created_at }}
            </td>
            <td>
              {{ d.from_date && d.to_date ? `${d.from_date} to ${d.to_date}` : '' }}
            </td>
          </tr>
        </tbody>
      </table>
    </tableSection>
    <a
      v-if="downloadLink"
      ref="download-link"
      :href="downloadLink"
      target="_blank"
      style="display:none!important"
    ></a>
  </section>
</template>

<script>
import tableSection from '@c/tableSection'
import { mapState } from 'vuex'
const strip = v => 0 + +v.toString().replace(/[^0-9\.]/g,'')
const list =  [
  {
    'Merchant': 'ADVANCED PLUS LLC - 31',
    'Funded Date':'2019-06-17',
    'Merchant Id': 8833,
    'Debited': '$3,383.32',
    'Total Payments': '$1,501.51',
    'Management Fee': '$45.09',
    'Net amount': '$1,456.42',
    'Principal': '$1,150.00',
    'Profit': '$306.00',
    'Last Rcode': 'R01',
    'Last Payment Date': 'R01',
    'Last Payment Amount': '$845.83',
    'Participant RTR': '$1,400.00',
    'Participant RTR Balance': '$1,351.67'
  }
];
  export default {
    name: 'statements-view',
    data() {
      return {
        list,
        error: false,
        errorMsg: null,
        downloadLink: null,
        request:{
          limit: 10,
          keyword: '',
          page: 1,
          sort_by: '',
          sort_order: ''
        }
      }
    },
    computed: {
      ...mapState('api', {
        data: s => s.statements,
        loading: (s) => s.loading.statements
      }),
    },
    methods: {
      requestDownload(f,type){
        const url = f[`download_${type}_url`],
        a = document.createElement('a');
        [a.href, a.target] = [url, ''];
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      },
      download(link){
        this.downloadLink = link;
        this.$nextTick(()=>{
          this.$refs['download-link'].click();
          this.downloadLink = null;
        });
      },
      initPage(){
        const replace =  v => v.replace(/%/g,' ')
        const [q,r] = [this.$route.query,this.request]
        if(q.range) r.limit = strip(q.range)
        if(q.page) r.page = q.page
        if(q.keyword) r.keyword = replace(q.keyword)
        if(q.sortBy) r.sort_by = q.sortBy
        if(q.sortOrder) r.sort_order = q.sortOrder
      },
      getData(){
        let { limit, keyword, page, sort_by, sort_order } = this.request;
        let offset = limit * (page - 1)
        offset = offset < 0 ? 0 : offset 
        const post = {limit, offset, sort_by, sort_order }
        if(keyword) post.keyword = keyword
        this.$store.dispatch('api/getData',{
          force: true,
          url:'/statement',
          vals:['data','links','pagination'],
          post,
          field: 'statements'
        })
        .catch(e=>{
          if(e.message) this.errorMsg = e.message;
          if(e.msg) this.errorMsg = e.msg;
          this.error = true;
        });
      },
      on_create() {
        this.initPage();
        this.getData();
      }
    },
    created(){
      for(let a=0; a<10; a++) this.list.push(this.list[0])
    },
    components:{
      tableSection
    }
  }
</script>

<style lang="scss" scoped src="~v/statements.scss"></style>