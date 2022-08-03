import Vue from "vue";
import Router from "vue-router";
import notificationView from '../views/notificationView';
import transactionView from '../views/transactionView';
import dashboardView from '../views/dashboardView';
import TwoFactor from '../views/two-factor/TwoFactor';
import EnableTwoFactor from '../views/two-factor/EnableTwoFactor';
import TwoFactorLogin from '../views/two-factor/Login';
import LoginWithRcode from '../views/two-factor/LoginWithRCode';
import Dashboard from '@merchant/views/Dashboard';
import FAQ from '@merchant/views/FAQ';
import Reports from '@merchant/views/Reports';
import Requests from '@merchant/views/Requests';

Vue.use(Router);

const router = new Router({
    mode: "history",
    base: '/',
    routes: [
      {
        path:"/merchants/dashboard",
        name:"dashboard",
        component:Dashboard,
        meta:{
          title:'Merchant App | Home page',
          breadcrumb: 'dashboard',
          user:true
        }
      },
      {
        path:"/merchants/reports",
        name:"reports",
        component:Reports,
        meta:{
          title:'Merchant App | Report',
          breadcrumb: 'reports',
          user:true
        }
      },
      {
        path:"/merchants/requests",
        name:"requests",
        component:Requests,
        meta:{
          title:'Merchant App | Report',
          breadcrumb: 'requests',
          user:true
        }
      },
      {
        path:"/merchants/faq",
        name:"faq",
        component:FAQ,
        meta:{
          title:'Merchant App | FAQ',
          breadcrumb: 'FAQ',
          user:true
        }
      },
      {
        path: "/merchants",
        name: "home",
        component:dashboardView,
        meta:{
          title:'Merchant App | Home page',
          breadcrumb: 'dashboard'
        }
      },
      //Two factor verification
      {
        path:"/merchants/two-factor",
        name:"two-factor-authentication",
        component:TwoFactor,
        meta:{
          title:'Merchant App | Two-Factor Authentication',
          user:true
        }
      },
      {
        path:"/merchants/enable-two-factor-auth",
        name:"enable-two-factor-authentication",
        component:EnableTwoFactor,
        meta:{
          title:'Merchant App | Enable Two-Factor Authentication',
          user:true
        }
      },
      {
        path:"/merchants/two-factor-challenge",
        name:"two-factor-challenge",
        component:TwoFactorLogin,
        meta:{
          title:'Merchant App | Enable Two-Factor Authentication',
        }
      },
      {
        path:"/merchants/login-by-recovery-key",
        name:"login-by-recovery-key",
        component:LoginWithRcode,
        meta:{
          title:'Merchant App | Enable Two-Factor Authentication',
        }
      },
      {
        path:'/merchants/transactions',
        component: transactionView,
        meta:{
            title:'Merchant App | Transactions',
            breadcrumb:'transactions',
            user:true
        }
    },
    {
        path:'/merchants/notifications',
        component: notificationView,
        meta:{
            title:'Merchant App | Notifications',
            breadcrumb:'notifications'
        }
    }
    ]
});

export default router;
