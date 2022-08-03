import middleware from "@merchant/api/middleware";
import Vue from "vue";
import { apiState,apiGetters } from './fields';
export default {
  namespaced: true,
  state: apiState,
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
    getData({ getters, commit,dispatch }, p) {
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
				if(e.response && e.response.status === 401){
					dispatch('auth/sessionExpired',{},{
						root:true
					})
				}
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
    statements: (s) => s.statements,
    merchantPaymentData: (s) => s.merchantPaymentData,
    refresh: (s) => s.refresh,
    hasTransactions: s=> s.header && s.header.no_of_payments && s.header.no_of_payments > 0,
    ...apiGetters,
    investorDashboard: s=> s.header
  },
};
