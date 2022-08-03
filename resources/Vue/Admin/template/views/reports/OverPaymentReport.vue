<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
    >
      <button type="submit" class="btn green-bt">Apply Filter</button>
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
import { download } from "@ac/form/download"
import { cloneDeep } from "lodash";
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "over-payment-report",
  components: {
    tableSection,
    formBase,
    preloader
  },
 data() {
    return {
      error: false,
      errorMsg: null
    };
  },
   computed: {
     ...mapState('api', {
      data: s => s.data.overpayment,
      loading: (s) => s.loading.overpayment,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading : s => s.loading.dataColumn,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor, 
      status: s => s.data.status,
      statusLoading: s => s.loading.status,
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
      lender: s => s.data.lender,
      lenderLoading: s => s.loading.lender,
    }),
    descriptors(){
      let [
        merchantOptions,
        statusOptions, 
        companyOptions, 
        lenderOptions,
        investorOptions 
      ] = new Array(5).fill([])
      if (!this.merchantLoading) merchantOptions = this.merchant.data 
      if (!this.statusLoading) statusOptions = this.status.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.companyLoading) companyOptions = this.company.data
      if (!this.lenderLoading) lenderOptions = this.lender.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "from_date",
                label: "From Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "to_date",
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
                name: "merchants",
                label: "Merchants",
                type: "multiselect",
                placeholder: "Select Merchant(s)",
                required: false,
                options: merchantOptions,
                status:this.merchantLoading
              },
              {
                name: "investors",
                label: "Investors",
                type: "multiselect",
                placeholder: "Select Investor(s)",
                required: false,
                options: investorOptions,
                status:this.investorLoading
              }
            ],
          },
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
                status:this.lenderLoading
              },
              {
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                required: false,
                options: companyOptions,
                status:this.companyLoading
              }
            ]
          },
          {
            className:"",
            fields:[
              {
                name: "sub_status",
                label: "Status",
                type: "multiselect",
                placeholder: "Select Sub status(es)",
                required: false,
                options: statusOptions,
                status:this.statusLoading
              },
            ]
          }
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
        footer["Default Invested Amount"] = this.data.total_collection
        footer["Default RTR Amount"] = this.data.total_default_amount
        footer["Overpayment"] = this.data.total_overpayment
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
      let data = cloneDeep(formData)
      if (data.investors != null ){
        data.investors  =  data.investors.reduce((idArray , status) => {
          idArray.push(status.id)
          return idArray
        },[])
      }
      else data.investors = []
      if (data.sub_status != null ){
        data.sub_status  =  data.sub_status.reduce((idArray , status) => {
          idArray.push(status.id)
          return idArray
        },[])
      }
      else data.sub_status = []
      if (data.merchants !=null){
        data.merchants = data.merchants.reduce((idArray , merchant) => {
          idArray.push(merchant.id)
          return idArray
        },[])
      }
      else data.merchant = []
      if (data.lenders != null){
        data.lenders = data.lenders.reduce ((idArray,lender) =>{
          idArray.push(lender.id)
          return idArray
        },[])
      }
      else data.lenders = []
      data.company = data.company ? data.company.id : null
      console.log('submit',data)
      this.$api({
        url: "/report/overpayment",
        field:'overpayment,',
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
      url: "/report/overpayment",
      field:'overpayment,',
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
        url: "/report/overpayment-columns",
        field:'dataColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Status
    if(!this.status || !this.status.data){
      this.$api({
        url: "/filter/sub-status",
        field:'status',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Company
    if(!this.company || !this.company.data){
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
    //Investor
    if( !this.investor || !this.investor.data){
      this.$api({
        url: "/filter/investor",
        field:'investor',
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