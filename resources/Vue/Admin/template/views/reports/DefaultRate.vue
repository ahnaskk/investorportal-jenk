<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
    >
      <button type="submit" class="btn green-bt">Apply Filter</button>
      <button type="button" class="btn blue-bt" @click="download()">Download</button>
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
import {download} from "@ac/form/download"
import { cloneDeep } from "lodash";
import formBase from "@ac/form/formBase.vue";
import preloader from "@ac/preloader"
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "default-rate",
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
      data: s => s.data.defaultRae,
      loading: (s) => s.loading.defaultRae,
      dataColumn: s => s.data.defaultRateColumn,
      dataColumnLoading : s => s.loading.defaultRateColumn,
      status: s => s.data.status,
      statusLoading: s => s.loading.status,
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
      lender: s => s.data.lender,
      lenderLoading: s => s.loading.lender,
      investorType: s => s.data.investorType,
      investorTypeLoading: s => s.loading.investorType,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor,
      overpayment: s => s.data.overpayment,
      overpaymentLoading: s => s.loading.overpayment,
      days: s => s.data.days,
      daysLoading: s => s.loading.days
    }),
    //Computed Descriptors
    descriptors(){
      // Merchants dropdown
      let [
        merchantOptions,
        status, 
        companyOptions, 
        lenderOptions, 
        investorTypeOptions, 
        investorOptions,
        daysOptions,
        overpaymentOptions 
      ] = new Array(8).fill([])
      if (!this.merchantLoading) merchantOptions = this.merchant.data 
      if (!this.statusLoading) status = this.status.data
      if (!this.investorTypeLoading) investorTypeOptions = this.investorType.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.companyLoading) companyOptions = this.company.data
      if (!this.lenderLoading) lenderOptions = this.lender.data
      if (!this.overpaymentLoading) overpaymentOptions = this.overpayment.data
      if (!this.daysLoading) daysOptions = this.days.data
      return{
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "merchants",
                label: "Merchants",
                type: "multiselect",
                placeholder: "Select Merchants",
                width:1/3,
                validations: {
                  required: {
                    params: null,
                    message: "This field is required"
                  }
                },
                options:merchantOptions,
                status:this.merchantLoading,
                trackBy:"id"
              },
              {
                name: "sub_status",
                label: "Status",
                type: "multiselect",
                placeholder: "Select Sub status(es)",
                required: false,
                validations: {
                  required: {
                    params: null,
                    message: "This field is required"
                  }
                },
                options: status,
                status:this.statusLoading,
                trackBy:"id"
              },
              {
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                required: false,
                validations: {
                  required: {
                    params: null,
                    message: "This field is required"
                  }
                },
                options: companyOptions,
                status: this.companyLoading,
                trackBy:"id"
              },
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "lenders",
                label: "Lenders",
                type: "multiselect",
                placeholder: "Select Lender(s)",
                required: false,
                options: lenderOptions,
                status:this.lenderLoading,
                trackBy:"id"
              },
              {
                name: "investor_type",
                label: "Investor Type",
                type: "select",
                placeholder: "Select Investor Type",
                required: false,
                status:this.investorTypeLoading,
                options: investorTypeOptions,
                trackBy:"id"
              },
              {
                name: "investor",
                label: "Investor",
                type: "select",
                placeholder: "Select Investor",
                required: false,
                options: investorOptions,
                status:this.investorLoading,
                trackBy:"id"
              },
            ],
          },
          {
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
              {
                name: "funded_date",
                label:"Filter with Funding Date",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
            ],
          },
          {
            fields: [
              {
                label:"Disable/Enable Investors",
                name:"Disable/Enable",
                type: "radio",
                placeholder: "",
                componentClass:"dark",
                radioList: [{ "label": "All","name":"radio2","value":null },{ "label": "Enable","name":"radio2","value":1 },{ "label": "Disable","name":"radio2","value":2 }],
              },
              {
                name: "overpayment",
                label: "Overpayment",
                type: "select",
                placeholder: "Select Status",
                required: false,
                options: overpaymentOptions,
                status : this.overpaymentLoading,
                trackBy:"id"
              },
              {
                name: "days",
                label: "Days",
                type: "select",
                placeholder: "Select Days",
                required: false,
                options: daysOptions,
                status: this.daysLoading,
                trackBy:"id"
              },
            ],
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
      console.log('submit',formData)
      data.company = data.company ? data.company.id : null
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
      data.investor_type = data.investor_type ? data.investor_type.id : null
      data.investor = data.investor ?  data.investor.id : null
      data.overpayment = data.overpayment ? data.overpayment.id : null
      data.days = data.days ? data.days.id : null
      this.$api({
      url: "/report/default-rate",
      field:'defaultRae',
      post:{...data,...this.request},
      force: false,
      catchErr: (d) => !d
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    },
    download(){
      download(this.data["download-url"])
    }
  },
  created() {
    if( this.$route.query.keyword ) this.request.filter = this.$route.query.keyword
    if( this.$route.query.range ) this.request.per_page = this.$route.query.range
    if( this.$route.query.page ) this.request.page = this.$route.query.page
    this.$api({
      url: "/report/default-rate",
      field:'defaultRae',
      post:this.request,
      force: false,
      catchErr: (d) => !d
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    //Table Headers
    if ( !this.dataColumn || !this.dataColumn.data ){
      this.$api({
        url: "/report/default-rate-columns",
        field:'defaultRateColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Filters
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
    //Investor Type  
    if(!this.investorType || !this.investorType.data){
      this.$api({
        url: "/filter/investor-type",
        field:'investorType',
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
    //Overpayment
    if( !this.overpayment || !this.overpayment.data){
      this.$api({
        url: "/filter/overpayment",
        field:'overpayment',
        method:'get', 
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Days
    if ( !this.days || !this.days.data){
      this.$api({
        url: "/filter/days",
        field:'days',
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