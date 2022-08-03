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
import { cloneDeep } from "lodash";
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "liquidity",
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
      data: s => s.data.liquidity,
      loading: (s) => s.loading.liquidity,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
      liquidity: s => s.data.liquidity,
      liquidityLoading: s => s.loading.liquidity,
      company: s => s.data.company,
      companyLoading: s => s.loading.company,
    }),
    descriptors(){
      let companyOptions =  []
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
                name: "liquidity",
                label: "Liquidity",
                type: "select",
                placeholder: "",
                required: false,
                width:1/3,
                options: [
                  { name: "All", id: null },
                  { name: "Excluded", id: 0 },
                  { name: "Included", id: 1 },
                ],
                selected:{ name: "All", value: null }
              },
              {
                label:"Disable/Enable Investors",
                name:"Disable/Enable Investors",
                width:1/3,
                type: "radio",
                placeholder: "",
                componentClass:"dark",
                radioList: [{ "label": "All","name":"radio2","value":"value1" },{ "label": "Enable","name":"radio2","value":"value2" },{ "label": "Disable","name":"radio2","value":"value3" }],
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
      data.company = data.company ? data.company.id : null
      data.liquidity = data.liquidity ? data.liquidity.id : null
      this.$api({
        url: "/report/liquidity",
        field:'liquidity',
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
      url: "/report/liquidity",
      field:'liquidity',
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
        url: "/report/liquidity-columns",
        field:'dataColumn',
        force: true,
        catchErr: (d) => !d
      })
      .then(d=>{
        console.log('resolved',d);
      })
      .catch((e) => console.log("Catched on component", e));
    }
    //Liquidity
    if( !this.liquidity || !this.liquidity.data){
      this.$api({
        url: "/filter/liquidity",
        field:'liquidity',
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