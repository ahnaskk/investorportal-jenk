<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
      @custom="custom($event)"
    >
      <button type="submit" class="btn green-bt">Apply Filter</button>
      <button type="button" class="btn blue-bt" @click="download()">Download</button>
      <button type="button" class="btn blue-bt" @click="download()">Download Syndicates</button>
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
import formBase from "@ac/form/formBase.vue";
import preloader from "@ac/preloader"
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "payments",
  components: {
    tableSection,
    formBase,
    preloader
  },
data() {
    return {
      error: false,
      errorMsg: null,
      timeFieldHidden:true,
    };
  },
   computed: {
    ...mapState('api', {
      data: s => s.data.payment,
      loading: (s) => s.loading.payment,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      label: s => s.data.label,
      labelLoading: s => s.loading.label,
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
      advanceType: s => s.data.advanceType,
      advanceTypeLoading: s => s.loading.advanceType,
      rcode: s => s.data.rcode,
      rcodeLoading: s => s.loading.rcode
    }),
    descriptors(){
      let [
        labelOptions,
        investorTypeOptions,
        investorOptions,
        merchantOptions,
        lenderOptions,
        statusOptions,
        advanceTypeOptions,
        rcodeOptions
      ] = new Array(8).fill([])
      if (!this.labelLoading) labelOptions = this.label.data
      if (!this.investorTypeLoading) investorTypeOptions = this.investorType.data
      if (!this.investorLoading) investorOptions = this.investor.data
      if (!this.merchantLoading) merchantOptions = this.merchant.data
      if (!this.lenderLoading) lenderOptions = this.lender.data
      if (!this.statusLoading) statusOptions = this.status.data
      if (!this.advanceTypeLoading) advanceTypeOptions = this.advanceType.data
      if (!this.rcodeLoading) rcodeOptions = this.rcode.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "date_type",
                label:"Filter Based On Payment Added Date (Payment Date by Default)",
                type: "checkbox",
                value:true,
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              }
            ],
          },
          {
            className:"",
            hidden:!this.timeFieldHidden,
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
            hidden:this.timeFieldHidden,
            fields: [
              {
                name: "date_start",
                label: "From Date",
                type: "date",
                placeholder: ""
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
                name:"merchants",
                type:"multiselect",
                label:"Merchants",
                placeholder:"Select Merchant(s)",
                options: merchantOptions,
                status: this.merchantLoading
              },
              {
                name: "lenders",
                label: "Lenders",
                type: "multiselect",
                placeholder: "Select Lender(s)",
                required: false,
                options: lenderOptions,
                status:this.lenderLoading
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
                name: "payment_type",
                label: "Payment Type",
                type: "select",
                placeholder: "Select Payment Type",
                required: false,
                options: [
                  { name: "All", id: null },
                  { name: "Credit", id: 1 },
                  { name: "Debit", id: 0 },
                ],
                selected:{ name: "All", id: null },
              },
            ],
          },
          {
            className: "",
            fields: [
              {
                name: "sub_status",
                label: "Status",
                type: "multiselect",
                placeholder: "Select Sub status(es)",
                required: false,
                options: statusOptions,
                status:this.statusLoading
              },
              {
                name: "advance_type",
                label: "Advance Type",
                type: "multiselect",
                placeholder: "Select Advance Type(s)",
                required: false,
                options: advanceTypeOptions,
                status:this.advanceTypeLoading
              }
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
                name: "export_checkbox",
                label:"Download Without Details",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              }
            ]
          },
          {
            className:"",
            fields:[
              {
                name: "investor_type",
                label: "Investor Type",
                type: "multiselect",
                placeholder: "Select Investor Type(s)",
                required: false,
                options: investorTypeOptions,
                status:this.investorTypeLoading
              },
              {
                name: "label",
                label: "Label",
                type: "select",
                required: false,
                options: labelOptions,
                status:this.labelLoading
              },
            ]
          },
          {
            className:"",
            fields:[
              {
                name: "overpayment",
                label:"Filter Based On Overpayment",
                type: "checkbox",
                value:true,
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
              {
                name: "rcode",
                selectAll:true,
                label: "Rcode",
                type: "multiselect",
                placeholder: "Select Rcode(s)",
                required: false,
                options: rcodeOptions,
                status:this.rcodeLoading
              },
            ]
          },
          {
            className: "",
            fields: [
              {
                name: "report_totals",
                label:"Include Report Totals",
                type: "checkbox",
                value:true,
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
              {
                name: "payment_method",
                label: "Payment Method",
                type: "select",
                placeholder: "Select Payment Method",
                required: false,
                options: [
                  { name: "ACH", id:0 },
                  { name: "Manual", id:1 },
                  { name: "Credit Card", id:2 },
                ],
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
      if (data.merchants != null ){
        data.merchants  =  data.merchants.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.merchants = []
      if (data.lenders != null ){
        data.lenders  =  data.lenders.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.lenders = []
      if (data.investors != null ){
        data.investors  =  data.investors.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.investors = []
      if (data.sub_status != null ){
        data.sub_status  =  data.sub_status.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.sub_status = []
      if (data.investor_type != null ){
        data.investor_type  =  data.investor_type.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.investor_type = []
      if (data.advance_type != null ){
        data.advance_type  =  data.advance_type.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.advance_type = []
      if (data.rcode != null ){
        data.rcode  =  data.rcode.reduce((idArray , field) => {
          idArray.push(field.id)
          return idArray
        },[])
      }
      else data.rcode = []
      data.payment_type = data.payment_type ? data.payment_type.id : null
      data.owner = data.owner ? data.owner.id : null
      data.payment_method = data.payment_method ? data.payment_method.id : null
      data.label = data.label ? data.label.id : null
      console.log('submit',data)
      this.$api({
        url: "/report/payment",
        field:'payment',
        force: true,
        post: {...this.request,...data},
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
    download(){
      // download(this.data["download-url"],{})
    }
  },
  created() {
    if( this.$route.query.keyword ) this.request.filter = this.$route.query.keyword
    if( this.$route.query.range ) this.request.per_page = this.$route.query.range
    if( this.$route.query.page ) this.request.page = this.$route.query.page
    this.$api({
      url: "/report/payment",
      field:'payment',
      force: true,
      post: this.request,
      catchErr: (d) => !d
    })
    .then(d=>{
      console.log('resolved',d);
    })
    .catch((e) => console.log("Catched on component", e));
    //Table Headers
    if ( !this.dataColumn || !this.dataColumn.data ){
      this.$api({
        url: "/report/payment-columns",
        field:'dataColumn',
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
    // Rcode
    if ( !this.rcode || !this.rcode.data ){
      this.$api({
        url: "/filter/rcode",
        field:'rcode',
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