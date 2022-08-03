<template>
  <section class="merchant-dashboard">
    <preloader v-if="loading" />
    <section v-else>
      <div class="vertical_scroll_class">
        <table class="data-table">
          <thead style="height: 85px">
            <tr>
              <th>Merchant</th>
              <th>Net Investment</th>
              <th>Date</th>
              <th>%</th>
              <th
                class="vertical_text"
                v-for="(date, dateIndex) in ivData.data.dates"
                :key="dateIndex"
              >
                {{ date }}
              </th>
            </tr>
          </thead>
          <tbody>
            <emptyBox msg="Nothing to display" v-if="!ivData" />
            <tr
              v-else
              v-for="(single, index) in ivData.data.data"
              :key="index"
              class="sticky-title"
              style="--bg: #f6f8fb"
            >
              <td class="merchantName">{{ single.Merchant }}</td>
              <td>{{ single.amount }}</td>
              <td>{{ single.date }}</td>
              <td>{{ single.percentage }}</td>
              <td
                v-for="(singleDate, singleIndex) in ivData.data.dates"
                :key="singleIndex"
              >
                <span v-if="single.list && single.list[singleDate]">{{
                  single.list[singleDate]
                }}</span>
                <span v-else>-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</template>

<script>
import infoSection from "@c/merchantDashboard/infoSection";
import tableSection from "@c/tableSection";
import { mapState } from "vuex";
const strip = (v) => 0 + +v.toString().replace(/[^0-9\.]/g, "");
export default {
  name: "investment-graph",
  data() {
    return {
      error: false,
      errorMsg: null,
      request: {
        limit: 10,
        keyword: "",
        page: 1,
        sort_by: "",
        sort_order: "",
        request_from: "web",
      },
      empty: false,
      searching: false,
    };
  },
  computed: {
    ...mapState("api", {
      ivData: (s) => s.investmentGraph,
      loading: (s) => s.loading.investmentGraph,
    }),
  },
  components: {
    infoSection,
    tableSection,
  },
  methods: {
    log(e) {
      console.log(e);
    },
    initPage() {
      const [q, r] = [this.$route.query, this.request];
      if (q.range) r.limit = strip(q.range);
      if (q.keyword) r.keyword = q.keyword.replace(/%/g, " ");
      if (q.page) r.page = q.page;
      if (q.sortBy) r.sort_by = q.sortBy;
      if (q.sortOrder) r.sort_order = q.sortOrder;
    },
    getData() {
      const { keyword, limit, page, sort_by, sort_order, request_from } =
        this.request;
      let offset = limit * (page - 1);
      offset = offset < 0 ? 0 : offset;
      const post = {
        keyword,
        offset,
        limit,
        sort_order,
        sort_by,
        request_from,
      };
      this.$store
        .dispatch("api/getData", {
          force: true,
          url: "/investment-waterflow",
          field: "investmentGraph",
          vals: ["data"],
          post,
        })
        .catch((e) => {
          if (e.msg) this.errorMsg = e.msg;
          this.error = true;
        });
    },
    on_create() {
      this.initPage();
      this.getData();
    },
  },
};
</script>

<style lang="scss" scoped src="~v/merchants.scss"></style>