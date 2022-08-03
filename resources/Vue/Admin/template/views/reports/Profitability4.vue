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
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "profitability4",
  components: {
    tableSection,
    formBase,
    preloader
  },
 data() {
    return {
      error: false,
      errorMsg: null,
      request: {
        limit: 10,
        keyword: "",
        page: 1,
        sDate: "",
        eDate: "",
        merchant_name: "",
      },
      descriptors: {
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
                name: "filter_date",
                label:"Filter with Funding Date",
                type: "checkbox",
                checkboxLabel:"Check this",
                placeholder: "",
                componentClass:"dark",
              },
            ],
          }
        ],
      },
    };
  },
   computed: {
     ...mapState('api', {
      data: s => s.data.profitability4,
      loading: (s) => s.loading.profitability4,
      dataColumn: s => s.data.dataColumn,
      dataColumnLoading: s => s.loading.dataColumn,
    }),
  },
  methods:{
    applyFilters(data){
      console.log('submit',data)
      this.$api({
        url: "/report/profitability4",
        field:'profitability4',
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
      url: "/report/profitability4",
      field:'profitability4',
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
        url: "/report/profitability2-columns",
        field:'dataColumn',
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