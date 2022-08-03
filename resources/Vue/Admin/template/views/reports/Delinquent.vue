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
      :filterRange="request.limit"
      :filterFrom="request.sDate"
      :filterTo="request.eDate"
      :filterMerchant="request.merchant_name"
      :searchKeyword="request.keyword"
      :hideSearch="false"
      :pagination="data.meta"
    />
    <preloader v-else></preloader>
  </div>
</template>

<script>
import { cloneDeep } from "lodash";
import tableSection from "@ac/tableSection.vue";
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import { mapState } from "vuex";
export default {
  name: "deliquent",
  components: {
    tableSection,
    formBase,
    preloader
  },
  data() {
    return {
      error: false,
      errorMsg: null,
    };
  },
   computed: {
    ...mapState('api', {
      data: s => s.data.delinquent,
      loading: (s) => s.loading.delinquent,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
      lender: s => s.data.lender,
      lenderLoading: s => s.loading.lender,
      industry: s => s.data.industry,
      industryLoading: s => s.loading.industry,
    }),
    descriptors(){
      let [companyOptions, lenderOptions, IndustryOptions ] = new Array(3).fill([])
      if (!this.lenderLoading) lenderOptions = this.lender.data
      if (!this.industryLoading) IndustryOptions = this.industry.data
      if (!this.companyLoading) companyOptions = this.company.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className:"",
            fields:[
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
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                required: false,
                options: companyOptions,
                status : this.companyLoading
              },
            ]
          },
          {
            className: "",
            fields: [
              {
                name: "start_date",
                label: "From Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "end_date",
                label: "To Date",
                type: "date",
                placeholder: "",
              },
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "industries",
                label: "Industry",
                type: "multiselect",
                placeholder: "Select Industry",
                width:1,
                required: false,
                options: IndustryOptions,
                status: this.industryLoading
              }
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
      // sum row
      if(!this.dataColumnLoading && this.dataColumn.data){
        let footer = {}
        this.dataColumn.data.forEach(th =>{
          footer[th.title] = ""
        })
        footer["Total Invested"] = this.data.total_invested_amount
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
      if (data.lenders != null){
        data.lenders = data.lenders.reduce ((idArray,lender) =>{
          idArray.push(lender.id)
          return idArray
        },[])
      }
      else data.lenders = []      
      if (data.industries != null){
        data.industries.reduce ((idArray,industry) =>{
          idArray.push(industry.id)
          return idArray
        },[])
      }
      else data.industries = []
      data.company = data.company ? data.company.id : null
      this.$api({
        url: "/report/delinquent",
        field:'delinquent',
        post:{...data,...this.request},
        force: false,
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
      url: "/report/delinquent",
      field:'delinquent',
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
        url: "/report/delinquent-columns",
        field:'dataColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Company
    if( !this.company || !this.company.data){
      this.$api({
        url: "/filter/company",
        field:'company',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Lenders
    if( !this.lender || !this.lender.data ){
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