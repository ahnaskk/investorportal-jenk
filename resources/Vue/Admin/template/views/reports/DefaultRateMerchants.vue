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
import tableSection from "@ac/tableSection.vue";
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import { mapState } from "vuex";
export default {
  name: "default-rate-merchant",
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
      data: s => s.data.defaultRateMerchant,
      loading: (s) => s.loading.defaultRateMerchant,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      status: s => s.data.status,
      statusLoading: s => s.loading.status,
      investorType: s => s.data.investorType,
      investorTypeLoading: s => s.loading.investorType,
      investors: s => s.data.investors,
      investorsLoading: s => s.loading.investors,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
      days: s => s.data.days,
      daysLoading: s => s.loading.days
    }),
    descriptors(){
      let [ 
        statusOptions, 
        companyOptions, 
        investorTypeOptions, 
        investorOptions,
        daysOptions 
      ] = new Array(5).fill([])
      if (!this.statusLoading) statusOptions = this.status.data
      if (!this.investorTypeLoading) investorTypeOptions = this.investorType.data
      if (!this.investorsLoading) investorOptions = this.investors.data
      if (!this.companyLoading) companyOptions = this.company.data
      if (!this.daysLoading) daysOptions = this.days.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "investor_type",
                label: "Investor Type",
                type: "select",
                placeholder: "Select Investor Type",
                width:1/2,
                options: investorTypeOptions,
                status: this.investorTypeLoading
              },
              {
                name: "investor",
                label: "Investors",
                type: "select",
                placeholder: "Select Investors",
                width:1/2,
                options: investorOptions,
                status: this.investorsLoading
              }
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "company",
                label: "Company",
                type: "select",
                placeholder: "Select Company",
                width:1/2,
                options: companyOptions,
                status: this.companyLoading
              },
              {
                name: "sub_status",
                label: "Status",
                type: "multiselect",
                placeholder: "Select Sub status(es)",
                options: statusOptions,
                status:this.statusLoading
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
            ],
          },
          {
            fields:[
              {
                name: "Name",
                label:"Filter with Funding Date",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
                width:1/2,
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
            ]
          }
        ],
      }
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
        footer["Default Invested Amount"] = this.data.total_default_amount
        footer["Default RTR Amount"] = this.data.total_investor_rtr
        data.push(footer)
      }
      return data
    },
  },
  methods:{
    applyFilters(formData){
      let data = cloneDeep(formData)
      data.investor_type = data.investor_type ? data.investor_type.id : null
      data.investor = data.investor ?  data.investor.id : null
      data.company = data.company ? data.company.id : null
      data.days = data.days ? data.days.id : null
      if (data.sub_status != null ){
        data.sub_status  =  data.sub_status.reduce((idArray , status) => {
          idArray.push(status.id)
          return idArray
        },[])
      }
      console.log('submit',data)
      this.$api({
        url: "/report/default-rate-merchant",
        field:'defaultRateMerchant',
        post:{...this.request,...data},
        force: true,
        catchErr: (d) => !d,
        transformer:e => {
          console.log('transformer',e)
          return e;
        }
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
      url: "/report/default-rate-merchant",
      field:'defaultRateMerchant',
      post:this.request,
      force: true,
      catchErr: (d) => !d,
      transformer:e => {
        console.log('transformer',e)
        return e;
      }
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    //Table headers
    if ( !this.dataColumn || !this.dataColumn.data ){
      this.$api({
        url: "/report/default-rate-merchant-columns",
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
    if( !this.status || !this.status.data){
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
    if( !this.investors || !this.investors.data){
      this.$api({
        url: "/filter/investor",
        field:'investors',
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
    //Company
    if (!this.company || !this.company.data){
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