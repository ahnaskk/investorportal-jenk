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
import { cloneDeep } from "lodash";
import {download} from "@ac/form/download"
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "transactions",
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
      data: s => s.data.transaction,
      loading: (s) => s.loading.transaction,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      transactionType: s => s.data.transactionType,
      transactionTypeLoading: s => s.loading.transactionType,
      transactionCat: s => s.data.transactionCat,
      transactionCatLoading: s => s.loading.transactionCat,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor, 
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
    }),
    descriptors(){
      let [
        transactionTypeOptions,
        transactionCatOptions,
        companyOptions,
        investorOptions
      ] =  new Array(4).fill([])
      if (!this.transactionTypeLoading) transactionTypeOptions = this.transactionType.data
      if (!this.transactionCatLoading) transactionCatOptions = this.transactionCat.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.companyLoading) companyOptions = this.company.data
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
              {
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                required: false,
                options: companyOptions,
                status:this.companyLoading
              },
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "investors",
                label: "Investors",
                type: "multiselect",
                placeholder: "Select Investor(s)",
                required: false,
                options: investorOptions,
                status:this.investorLoading
              },
              {
                name: "transaction_type",
                label: "Transaction Type",
                type: "select",
                placeholder: "Select Transaction Type",
                required: false,
                options: transactionTypeOptions,
                status: this.transactionTypeLoading,
              },
              {
                name: "transaction_categories",
                label: "Transaction Categories",
                type: "multiselect",
                placeholder: "Select Transaction Category(s)",
                required: false,
                options: transactionCatOptions,
                status: this.transactionCatLoading
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
      console.log('submit',formData)
      let data = cloneDeep(formData)
      if (data.investors != null ){
        data.investors  =  data.investors.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.investors = []
      //
      if (data.company != null ){
        data.company  =  data.investors.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.company = []
      //
      if (data.transaction_categories != null ){
        data.transaction_categories  =  data.transaction_categories.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.transaction_categories = []
      data.transaction_type = data.transaction_type ? data.transaction_type.id : null
      this.$api({
        url: "/report/transaction",
        field:'transaction',
        post:{...this.request,...data},
        force: true,
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
      url: "/report/transaction",
      field:'transaction',
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
        url: "/report/transaction-columns",
        field:'dataColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //TransactionType
    if(!this.merchant || !this.merchant.data){
      this.$api({
        url: "/filter/transaction-type",
        field:'transactionType',
        method:'get',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //TransactionCategories
    if(!this.merchant || !this.merchant.data){
      this.$api({
        url: "/filter/transaction-category",
        field:'transactionCat',
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
  },
};
</script>

<style>
</style>