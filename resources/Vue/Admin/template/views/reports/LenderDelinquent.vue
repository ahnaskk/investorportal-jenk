<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
    >
      <button type="submit" class="btn green-bt">Apply Filter</button>
      <button type="submit" class="btn blue-bt">Download</button>
    </formBase>
    <tableSection
      v-if="data && !loading && !error && calculatedData"
      :data="calculatedData"
      :columns="dataColumn"
      :rangeSelector="true"
      :showSlot="false"
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
          value: 30,
        },
      ]"
      :filterRange="request.per_page"
      :filterFrom="request.sDate"
      :filterTo="request.eDate"
      :filterMerchant="request.merchant_name"
      :searchKeyword="request.filter"
      :hideSearch="false"
      :pagination="data.meta"
    />
    <preloader v-else></preloader>
  </div>
</template>

<script>
import { cloneDeep } from "lodash";
import preloader from "@ac/preloader"
import tableSection from "@ac/tableSection.vue";
import formBase from "@ac/form/formBase.vue";
import { mapState } from "vuex";
export default {
  name: "lender-deliquent",
  components: {
    tableSection,
    preloader,
    formBase
  },
  data() {
    return {
      error: false,
      errorMsg: null
    };
  },
   computed: {
     ...mapState('api', {
      data: s => s.data.lenderDelinquent,
      loading: (s) => s.loading.lenderDelinquent,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
      lender: s => s.data.lender,
      lenderLoading: s => s.loading.lender,
      industry: s => s.data.industry,
      industryLoading: s => s.loading.industry,
    }),
    descriptors(){
      let [
        industryOptions,
        lenderOptions,
        merchantOptions
      ] = new Array(3).fill([])
      if (!this.industryLoading) industryOptions = this.industry.data
      if (!this.lenderLoading) lenderOptions = this.lender.data
      if (!this.merchantLoading) merchantOptions = this.merchant.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "industries",
                label: "Industry",
                type: "multiselect",
                placeholder: "Select Industry(s)",
                required: false,
                options: industryOptions,
                status: this.industryLoading
              },
              {
                name: "lenders",
                label: "Lenders",
                type: "multiselect",
                placeholder: "Select Lender(s)",
                required: false,
                options: lenderOptions,
                status: this.lenderLoading
              },
              {
                name: "merchants",
                label: "Merchants",
                type: "multiselect",
                placeholder: "Select Merchant(s)",
                required: false,
                options: merchantOptions,
                status: this.merchantLoading
              },
            ],
          },
        ],
      }
    },
    calculatedData(){
      let data = []
      let tr = {}
      if( !this.loading && !this.dataColumnLoading && this.data.data && this.dataColumn.data ){
        if (this.data.data.length < 1) return []
        this.data.data.forEach(td => {
          tr = {}
          this.dataColumn.data.forEach(th =>{
            if ( td.hasOwnProperty(th.data) ){
              tr[th.title] = td[th.data]
            }
          })
          data.push(tr)
        })
      }
      //Sum row
      if(!this.dataColumnLoading && this.dataColumn.data){
        let footer = {}
        this.dataColumn.data.forEach(th =>{
          footer[th.title] = ""
        })
        footer["Invested Amount"] = this.data.total_invested_amount
        footer["Default Invested"] = this.data.total_default_invested
        footer["CTD profit"] = this.data.total_default_ctd_profit
        data.push(footer)
      }
      return data
    },
    request(){
      let searchable = []
      if(!this.dataColumnLoading && this.dataColumn && this.dataColumn.data){
        searchable = this.dataColumn.data.reduce ( (searchables , field ) => {
          if (field.searchable){
            searchables.push(field.data)
          }
          return searchables
        },[])
      }
      return {
        per_page: 10,
        filter: "",
        page: 1,
        searchable
      }
    }
  },
  methods:{
    applyFilters(formData){
      console.log('submit',formData)
      let data = cloneDeep(formData)
      if (data.industries != null ){
        data.industries  =  data.industries.reduce((idArray , industry) => {
          idArray.push(industry.id)
          return idArray
        },[])
      }
      else data.industries = []
      if (data.merchants !=null){
        data.merchants = data.merchants.reduce((idArray , merchant) => {
          idArray.push(merchant.id)
          return idArray
        },[])
      }
      else data.merchant = []
      if (data.lenders != null){
        data.lenders = data.lenders.reduce((idArray,lender) =>{
          idArray.push(lender.id)
          return idArray
        },[])
      }
      else data.lenders = []
      this.$api({
        url: "/report/delinquent-lender",
        field:'lenderDelinquent',
        post:{...this.request,...data},
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
  },
  created() {
    if( this.$route.query.keyword ) this.request.filter = this.$route.query.keyword
    if( this.$route.query.range ) this.request.per_page = this.$route.query.range
    if( this.$route.query.page ) this.request.page = this.$route.query.page
    this.$api({
      url: "/report/delinquent-lender",
      field:'lenderDelinquent',
      post:this.request,
      force: true,
      catchErr: (d) => !d
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    //Table Headers
    if ( !this.dataColumn || !this.dataColumn.data ){
      this.$api({
        url: "/report/delinquent-lender-columns",
        field:'dataColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Lenders
    if( !this.lender || !this.lender.data){
      this.$api({
        url: "/filter/lender",
        field:'lender',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Merchants
    if(!this.merchant || !this.merchant.data){
      this.$api({
        url: "/filter/merchant",
        field:'merchant',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Industry
    if (!this.industry || !this.industry.data){
      this.$api({
        url: "/filter/industry",
        field:'industry',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
  },
};
</script>

<style>
</style>