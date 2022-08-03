import middleware from "@/api/middleware";
import Vue from "vue";
export default {
  namespaced: true,
  state: {
    investorDashboard: null,
    marketplace: null,
    merchants: null,
    marketplaceFilters: null,
    merchantData: null,
    reports: null,
    notificatios: null,
    dashboardGraph: null,
    paymentInvestmentGraph:null,
    investmentGraph:{},
    statements: null,
    merchantPaymentData: null,
    banks:null,
    enabletwofactor:null,
    twofactor:null,
    faq:null,
    graphFilterLoad:null,
    loadGraph:null,
    collectionNotes:null,
    loading: {
      investorDashboard: false,
      marketplace: false,
      twofactor:false,
      enabletwofactor:false,
      merchants: false,
      merchantData: false,
      marketplaceFilters: false,
      reports: false,
      notifications: false,
      dashboardGraph: false,
      paymentInvestmentGraph: false,
      investmentGraph:false,
      statements: false,
      merchantPaymentData: false,
      banks: false,
      faq: false,
      defaultRateReports:false,
      transactionReports:false,
      loadprofile:false,
      investmentReports:false,
      graphFilterLoad:false,
      loadGraph:false,
      collectionNotes:false,
    },
  },
  mutations: {
    saveData(state, { field, value }) {
      Vue.set(state, field, value);
    },
    load({ loading }, { field, val }) {
      loading[field] = val;
    },
    clearData(s) {
      Object.keys(s).forEach((k) => (s[k] = k == "loading" ? s[k] : null));
    }
  },
  actions: {
    getData({ getters, commit }, p) {
      const { force, url, field, post, transformer, vals, handler , method='post' , buffer=false } = p;
      if (getters.loading[field]) return;
      return new Promise((res, rej) => {
        if (getters[field] && !force) res(getters[field]);
        else {
          commit("load", { field, val: true });
          commit("saveData", { field, value: null });
          if(method == 'get'){
            middleware
            .get(url,post)
            .then((r) => {
              if (r && r.status == 200) {
                return r.data;
              }
              else rej(r);
            })
            .then((data) => {             
              if(handler) data = handler(data)
              if ((data && data.status) || data.success) {
                if (transformer) transformer(data.data);
                let value = {};
                if (vals && vals.length)
                  vals.forEach((val) => (value[val] = data[val]));
                else value = data.data;
                commit("saveData", {
                  field,
                  value,
                });
                res(getters[field]);
              } else rej(data);
            })
            .catch((e) => {
              commit("load", {
                loading: false,
                showSlot: false,
                error: true,
                field,
              });
              rej(e);
            })
            .finally(() => commit("load", { field, val: false }));
          }
          else{
            let file = {}
            if(buffer) file = { responseType: 'arraybuffer' }
            middleware
            .post(url, post , file)
            .then((r) => {
              if (r && r.status == 200) return r.data;
              else rej(r);
            })
            .then((data) => {
              if(handler) data = handler(data)
              if ((data && data.status) || data.success) {
                if (transformer) transformer(data.data);
                let value = {};
                if (vals && vals.length)
                  vals.forEach((val) => (value[val] = data[val]));
                else value = data.data;
                commit("saveData", {
                  field,
                  value,
                });
                res(getters[field]);
              } else rej(data);
            })
            .catch((e) => {
              commit("load", {
                loading: false,
                showSlot: false,
                error: true,
                field,
              });
              rej(e);
            })
            .finally(() => commit("load", { field, val: false }));
          }
        }
      });
    },
    call(c, { url, post }) {
      return new Promise((res, rej) => {
        middleware
          .post(url, post)
          .then((r) => (r.status == 200 ? res(r.data) : rej(r)))
          .catch((e) => rej(e));
      });
    },
    clearData({ commit }) {
      commit("clearData");
    },
  },
  getters: {
    investorDashboard: (s) => s.investorDashboard,
    marketplace: (s) => s.marketplace,
    loading: (s) => s.loading,
    merchants: (s) => s.merchants,
    marketplaceFilters: (s) =>
      s.marketplaceFilters ? s.marketplaceFilters.filters : null,
    merchantData: (s) => s.merchantData,
    reports: (s) => s.reports,
    notifications: (s) => s.notifications,
    notificationsMiniList: (s) => {
      const N = s.notifications;
      return N ? N.slice(0, N.length > 10 ? 10 : N.length) : N;
    },
    notsToRead: (s) => s.notifications.some((n) => !n.read_status),
    dashboardGraph: (s) => s.dashboardGraph,
    paymentInvestmentGraph: (s) => s.paymentInvestmentGraph,
    investmentGraph: (s) => s.investmentGraph,
    statements: (s) => s.statements,
    merchantPaymentData: (s) => s.merchantPaymentData,
    graphFilterLoad : (s) => s.graphFilterLoad,
    loadGraph: s => s.loadGraph,
    collectionNotes: s => s.collectionNotes,
    hasFAQ: s => s.faq && s.faq.length > 0
  },
};
