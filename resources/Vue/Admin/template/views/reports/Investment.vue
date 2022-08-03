<template>
  <div class="wrapper">    
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
      @custom="custom($event)"
      :componentKey="componentKey"
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
import {cloneDeep} from 'lodash'
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import preloader from "@ac/preloader"
import { mapState } from "vuex";
export default {
  name: "investment",
  components: {
    tableSection,
    formBase,
    preloader
  },
 data() {
    return {
      error: false,
      errorMsg: null,
      componentKey:0,
      timeFieldHidden:true,
    };
  },
   computed: {
     ...mapState('api', {
      data: s => s.data.investment,
      loading: (s) => s.loading.investment,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      label: s => s.data.label,
      labelLoading: s => s.loading.label,
      subStatusFlag: s => s.data.subStatusFlag,
      subStatusFlagLoading: s => s.loading.subStatusFlag,
      investorType: s => s.data.investorType,
      investorTypeLoading: s => s.loading.investorType,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor, 
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
      lender: s => s.data.lender,
      lenderLoading: s => s.loading.lender,
      status: s => s.data.status,
      statusLoading: s => s.loading.status,
      industry: s => s.data.industry,
      industryLoading: s => s.loading.industry,
      advanceType: s => s.data.advanceType,
      advanceTypeLoading: s => s.loading.advanceType,
    }),
    descriptors(){
      let [
        lenderOptions, 
        industryOptions,
        labelOptions,
        subStatusFlagOptions,
        merchantOptions,
        investorTypeOptions,
        investorOptions,
        statusOptions,
        advanceTypeOptions 
      ] = new Array(9).fill([])
      if (!this.lenderLoading) lenderOptions = this.lender.data
      if (!this.industryLoading) industryOptions = this.industry.data
      if (!this.merchantLoading) merchantOptions = this.merchant.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.labelLoading) labelOptions = this.label.data
      if (!this.subStatusFlagLoading) subStatusFlagOptions = this.subStatusFlag.data
      if (!this.investorTypeLoading) investorTypeOptions = this.investorType.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.statusLoading) statusOptions = this.status.data
      if (!this.advanceTypeLoading) advanceTypeOptions = this.advanceType.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "date_type",
                label:"Filter Based On Merchant Added Date (Funded Date by Default)",
                type: "checkbox",
                value:true,
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
              {
                name:"merchants",
                type:"multiselect",
                label:"Merchants",
                placeholder:"Select Merchant(s)",
                options: merchantOptions,
                status: this.merchantLoading
              }
            ],
          },
          {
            className:"",
            hidden:!this.timeFieldHidden,
            fields:[
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
            ]
          },
          {
            className: "",
            hidden:this.timeFieldHidden,
            fields: [
              {
                name: "date_start",
                label: "From Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "time_start",
                label: "From Time",
                type: "time",
                placeholder: "Pick From Time",
              },
            ],
          },
          {
            className: "",
            hidden:this.timeFieldHidden,
            fields: [
              {
                name: "date_end",
                label: "To Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "time_end",
                label: "To Time",
                type: "time",
                placeholder: "Pick To Time",
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
                status: this.investorLoading
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
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "industries",
                label: "Industries",
                type: "multiselect",
                placeholder: "Select Industry(s)",
                required: false,
                options: industryOptions,
                status: this.industryLoading
              },
              {
                name: "sub_status",
                label: "Status",
                type: "multiselect",
                placeholder: "Select Sub status(es)",
                required: false,
                options: statusOptions,
                status: this.statusLoading
              }
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "advance_type",
                label: "Advance Type",
                type: "multiselect",
                placeholder: "Select Advance Type(s)",
                required: false,
                options: advanceTypeOptions,
                status: this.advanceTypeLoading
              },
              {
                name: "Download Without Details",
                label:"Download Without Details",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
            ],
          },
          {
            className:"",
            fields:[
              {
                name: "owner",
                label: "Owner",
                type: "select",
                placeholder: "Select Company",
                required: false,
                options: [
                  { option: "Investor 1", value: "value 1" },
                  { option: "Investor 2", value: "value 2" },
                  { option: "Investor 3", value: "value 3" },
                ],
              },
              {
                name: "investor_type",
                label: "Investor Type",
                type: "multiselect",
                placeholder: "Select Investor Type(s)",
                required: false,
                options: investorTypeOptions,
                status: this.investorTypeLoading
              },
            ]
          },
          {
            className: "",
            fields: [
              {
                name: "sub_status_flag",
                label: "Sub status Flag",
                type: "multiselect",
                placeholder: "Select Sub status Flag(s)",
                required: false,
                options: subStatusFlagOptions,
                status: this.subStatusFlagLoading
              },
              {
                name: "label",
                label: "Label",
                type: "select",
                placeholder: "",
                required: false,
                options: labelOptions,
                status: this.labelLoading
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
      if (data.merchants != null){
        data.merchants = data.merchants.reduce ((idArray,merchant) =>{
          idArray.push(merchant.id)
          return idArray
        },[])
      } else data.merchants = []  
      if (data.investors != null){
        data.investors = data.investors.reduce ((idArray,investor) =>{
          idArray.push(investor.id)
          return idArray
        },[])
      } else data.investors = []  
      if (data.lenders != null){
        data.lenders = data.lenders.reduce ((idArray,lender) =>{
          idArray.push(lender.id)
          return idArray
        },[])
      } else data.lenders = [] 
      if (data.industries != null){
        data.industries = data.industries.reduce ((idArray,industry) =>{
          idArray.push(industry.id)
          return idArray
        },[])
      } else data.industries = [] 
      if (data.investor_type != null){
        data.investor_type = data.investor_type.reduce ((idArray,investor) =>{
          idArray.push(investor.id)
          return idArray
        },[])
      } else data.investor_type = [] 
      if (data.sub_status != null){
        data.sub_status = data.sub_status.reduce ((idArray,status) =>{
          idArray.push(status.id)
          return idArray
        },[])
      } else data.sub_status = [] 
      if (data.advance_type != null){
        data.advance_type = data.advance_type.reduce ((idArray,type) =>{
          idArray.push(type.id)
          return idArray
        },[])
      } else data.advance_type = [] 
      if (data.sub_status_flag != null){
        data.sub_status_flag = data.sub_status_flag.reduce ((idArray,flag) =>{
          idArray.push(flag.id)
          return idArray
        },[])
      } else data.sub_status_flag = [] 
      data.label = data.label ? data.label.id : null
      //submit
      this.$api({
        url: "/report/investment",
        field:'investment',
        force: true,
        post:{...this.request,...data},
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    },
    custom(data){
      if(data['date_type'] == true){
        this.timeFieldHidden = false
      }
      else if(data['date_type'] == false){
        this.timeFieldHidden = true
      }
    },
  },
  created() {
    if( this.$route.query.keyword ) this.request.filter = this.$route.query.keyword
    if( this.$route.query.range ) this.request.per_page = this.$route.query.range
    if( this.$route.query.page ) this.request.page = this.$route.query.page
    this.$api({
      url: "/report/investment",
      field:'investment',
      force: true,
      post:this.request,
      catchErr: (d) => !d
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    //Table Headers
    if ( !this.dataColumn || !this.dataColumn.data ){
      this.$api({
        url: "/report/investment-columns",
        field:'dataColumn',
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
    //Sub status flag
    if ( !this.subStatusFlag || !this.subStatusFlag.data ){
      this.$api({
        url: "/filter/sub-status-flag",
        field:'subStatusFlag',
        force: true,
        method:'get',
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Investor type
    if ( !this.investorType || !this.investorType.data ){
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
    //Merchant
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
    //Advance Type
    if (!this.advanceType || !this.advanceType.data){
      this.$api({
        url: "/filter/advance-type",
        field:'advanceType',
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