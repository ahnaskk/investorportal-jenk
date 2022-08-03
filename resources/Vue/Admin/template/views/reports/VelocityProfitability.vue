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
import {cloneDeep} from 'lodash'
import preloader from "@ac/preloader"
import tableSection from "@ac/tableSection.vue";
import formBase from "@ac/form/formBase.vue";
import { mapState } from "vuex";
export default {
  name: "velocity-profitability",
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
      data: s => s.data.profitability,
      loading: (s) => s.loading.profitability,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading : s => s.loading.dataColumn,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor, 
      label: s => s.data.label,
      labelLoading: s => s.loading.label,
    }),
    descriptors(){
      let [
        companyOptions,
        investorOptions,
        labelOptions
      ] = new Array(3).fill([])
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.labelLoading) labelOptions = this.label.data
      if (!this.companyLoading) companyOptions = this.company.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className:"",
            fields:[
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
            ]
          },
          {
            className: "",
            fields: [
              {
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                required: false,
                options: companyOptions,
                status:this.companyLoading
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
            className: "",
            fields: [
              {
                name: "label",
                label: "Label",
                type: "select",
                placeholder: "Select Label",
                required: false,
                options: labelOptions,
                status:this.labelLoading
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
      let data = cloneDeep(formData)
      if (data.investors != null){
        data.investors = data.investors.reduce ((idArray,investor) =>{
          idArray.push(investor.id)
          return idArray
        },[])
      } else data.investors = []  
      data.label = data.label ? data.label.id : null
      data.company = data.company ? data.company.id : null
      this.$api({
        url: "/report/velocity-profitability",
        field:'profitability',
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
    this.$api({
      url: "/report/velocity-profitability",
      field:'profitability',
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
        url: "/report/velocity-profitability-columns",
        field:'dataColumn',
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
    // Label
    if ( !this.label || !this.label.data ){
      this.$api({
        url: "/filter/label",
        field:'label',
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