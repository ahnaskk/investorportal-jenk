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
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "investor-reaassignment",
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
      data: s => s.data.investorReAssignment,
      loading: (s) => s.loading.investorReAssignment,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      investor: s => s.data.investor,
      investorLoading: s => s.loading.investor, 
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
    }),
    descriptors(){
      let [investorOptions,merchantOptions] = new Array(2).fill([])
      if (!this.merchantLoading) merchantOptions = this.merchant.data
      if (!this.investorLoading) investorOptions = this.investor.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className: "",
            fields: [
              {
                name: "from_date",
                label: "From Date (Assigned Date)",
                type: "date",
                placeholder: "",
              },
              {
                name: "to_date",
                label: "To Date",
                type: "date",
                placeholder: "",
              }
            ],
          },
          {
            className: "between",
            fields: [
              {
                name: "merchants",
                label: "Merchants",
                type: "multiselect",
                placeholder: "Select Merchant(s)",
                required: false,
                options: merchantOptions,
                status: this.merchantLoading
              },
              {
                name: "investors",
                label: "Investors",
                type: "multiselect",
                placeholder: "Select Investor(s)",
                required: false,
                options: investorOptions,
                status: this.investorLoading
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
      if(data.investors !=null){
        data.investors = data.investors.reduce( (idArray , investor) => {
         idArray.push(investor.id)
         return idArray 
        },[]) 
      } else data.investors = []
      if(data.merchants !=null){
        data.merchants = data.merchants.reduce( (idArray , merchant) => {
         idArray.push(merchant.id)
         return idArray 
        },[]) 
      } else data.merchants = []
      //submit
      this.$api({
        url: "/report/investor-assignment",
        field:'investorAssignment',
        force: true,
        post:{...this.request,...data},
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
      url: "/report/investor-re-assignment",
      field:'investorReAssignment',
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
        url: "/report/investor-re-assignment-columns",
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
  },
};
</script>

<style>
</style>