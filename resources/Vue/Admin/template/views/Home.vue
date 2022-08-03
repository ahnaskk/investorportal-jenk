<template>
  <div class="wrapper">
    <tableSection
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
    />
  </div>
</template>

<script>
import tableSection from "@ac/tableSection.vue";
import { mapState } from "vuex";
export default {
  name: "home-view",
  components: {
    tableSection,
  },
  data() {
    return {
      error: false,
      errorMsg: null,
      loading: false,
      request: {
        limit: 10,
        keyword: "",
        page: 1,
        sDate: "",
        eDate: "",
        merchant_name: "",
      },
      data:null,
      loading:true
    };
  },
  // computed: {
  //   ...mapState('api', {
  //     data: s => s.reports,
  //     loading: (s) => s.loading.reports
  //   }),
  // }
  created() {
    let self = this
    this.$api({
      url: "/report/delinquent",
      method: "post",
      post: {
        test: "Hi Hi",
      },
      interceptor(d, rej) {
        console.log("passed interceptor");
        return d.status == 200 ? d.data : rej(d);
      },
      catchErr: (d) => !d.success,
      onSuccess(d) {
        console.log("success", d);
      },
      onError(e) {
        self.data = e
        self.loading = false
        console.log("catched at passed onError", e);
      },
      onEnd() {
        console.log("Passed OnEnd method");
      },
    }).catch((e) => console.log("Catched on component", e));
  },
};
</script>

<style>
</style>