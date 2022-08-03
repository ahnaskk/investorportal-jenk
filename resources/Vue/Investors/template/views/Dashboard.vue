<template>
  <div class="view-wrapper">
    <preloader v-if="loading" />
    <emptyBox v-if="error" :msg="errorMsg" />
    <section class="dashboard-row" v-if="data && !loading && !error">
      <div class="col overview">
        <div class="content-box">
          <!-- page title -->
          <header class="page-title-sec">
            <h1 class="title">
              Dashboard
              <span class="info">
                {{ data.investor_name }}
              </span>
            </h1>
          </header>
          <!-- overview row -->
          <section class="info-row">
            <div class="col left">
              <div class="content-box bg">
                <investmentInfo :data="data" />
              </div>
            </div>
            <div class="col right">
              <div class="content-box bg">
                <ratesBox :data="data" />
              </div>
            </div>
          </section>
          <!-- graph row -->
          <section class="graph-row">
            <div class="col">
              <div class="content-box bg">
                <graph :data="data" />
              </div>
            </div>
            <div class="col">
              <div class="content-box bg radial">
                <!-- <radialGraph v-if="data" :data="data.category_status_arr" /> -->
                <paymentInvestmentGraph />
              </div>
            </div>
          </section>
        </div>
      </div>
      <!-- payment info col -->
      <div class="col payments">
        <div class="content-box overflow">
          <paymentInfo>
            <template v-slot:body>
              <transactions
                v-if="data && data.latest_payments"
                :data="data.latest_payments"
                title="Latest Payments"
              >
                <template v-slot:action>
                  <router-link to="/reports" class="view-all-bt"
                    >View All</router-link
                  >
                </template>
              </transactions>
            </template>
            <template v-slot:footer>
              <statements
                v-if="data.last_generated_statements"
                :on="data.last_generated_statements.generated_on"
                :period="data.last_generated_statements.period"
              />
            </template>
          </paymentInfo>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import graph from "@c/dashboard/graph";
import paymentInvestmentGraph from "@c/dashboard/paymentInvestmentGraph";
import investmentInfo from "@c/dashboard/investmentInfo";
import ratesBox from "@c/dashboard/ratesBox";
import paymentInfo from "@c/dashboard/paymentInfo";
import transactions from "@c/transactionList/list";
import statements from "@c/dashboard/sub/statements";
import { mapState } from "vuex";
export default {
  name: "dashboard-view",
  components: {
    graph,
    investmentInfo,
    paymentInfo,
    ratesBox,
    transactions,
    statements,
    paymentInvestmentGraph,
  },
  data() {
    return {
      error: false,
      errorMsg: false,
    };
  },
  computed: {
    ...mapState("api", {
      data: (state) => state.investorDashboard,
      loading: ({ loading }) => loading.investorDashboard,
    }),
  },
  methods: {
    on_create() {
      this.$store
        .dispatch("api/getData", {
          force: true,
          url: "/dashboard",
          field: "investorDashboard",
        })
        .catch((e) => {
          this.error = true;
          this.errorMsg =
            e.msg ||
            e.message ||
            "Something went wrong! Please try again later";
        });
    },
  },
  created: function () {
    console.log("created");
  },
};
</script>

<style
  lang="scss"
  scoped
  src="~v/dashboard.scss"
></style>