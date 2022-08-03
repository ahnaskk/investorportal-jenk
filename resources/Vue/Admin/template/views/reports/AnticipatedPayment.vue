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
import tableSection from "@ac/tableSection.vue";
import formBase from "@ac/form/formBase.vue";
import preloader from "@ac/preloader"
import { mapState } from "vuex";
export default {
  name: "anticipated-payment",
  components: {
    tableSection,
    preloader,
    formBase
  },
  data() {
    return {
      error: false,
      errorMsg: null,
    };
  },
  computed: {
    ...mapState('api', {
      data: s => s.reports,
      loading: (s) => s.loading.reports,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading : s => s.loading.dataColumn,
      merchant: s => s.data.merchant,
      merchantLoading: (s) => s.loading.merchant,
    }),
    descriptors(){
      let merchantOptions = []
      if(!this.merchantLoading) merchantOptions = this.merchant.data
      return {
        form: "defaultRates",
        wrappers: [
          {
            className:"",
            fields:[
              {
                name: "From Date",
                label: "From Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "To Date",
                label: "To Date",
                type: "date",
                placeholder: "",
              },
              {
                name: "Merchants",
                label: "Merchants",
                type: "multiselect",
                placeholder: "Select Merchant(s)",
                required: false,
                options: merchantOptions,
                status:this.merchantLoading
              }
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
    applyFilters(data){
      console.log('submit',data)
    }
  },
  created() {
    this.$api({
      url: "/report/anticipated-payment",
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
        url: "/report/anticipated-payment-columns",
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
  },
};
</script>

<style>
</style>