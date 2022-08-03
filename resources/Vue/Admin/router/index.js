import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter);
import globalGuard from './globalGuard';

import routes from './routes'

const router = new VueRouter({
  mode: 'history',
  base: '/v/admin',
  routes
});

router.beforeEach(globalGuard);

export default router
