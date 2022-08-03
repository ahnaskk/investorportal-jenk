<template>
  <div class="wrapper">
    <formBase 
      :data="descriptors" 
      @submit="applyFilters"
    >
      <button type="submit" class="btn green-bt">Export</button>
    </formBase>
    <!-- <tableSection
      v-if="data && !loading && !error"
      :data="data.data"
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
      :pagination="data.pagination"
    /> -->
    <!-- <preloader v-else></preloader> -->
  </div>
</template>

<script>
import preloader from "@ac/preloader"
import formBase from "@ac/form/formBase.vue";
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "revenue-recognition",
  components: {
    tableSection,
    preloader,
    formBase
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
            fields: [
              {
                name: "date_start",
                type: "date",
                placeholder: "",
              },
            ],
          },
        ],
      },
    };
  },
   computed: {
     ...mapState('api', {
      data: s => s.data.paymentLeft,
      loading: (s) => s.loading.paymentLeft
    }),
  },
  methods:{
    applyFilters(data){
      console.log('submit',data)
    }
  },
  created() {

  },
};
</script>

<style>
</style>