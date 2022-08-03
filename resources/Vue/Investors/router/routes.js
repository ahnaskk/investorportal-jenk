// Pages
import Dashboard from '@v/Dashboard';
import InvestmentGraph from '@v/InvestmentGraph';
import Merchants from '@v/merchants/Merchants';
import MerchantDashboard from '@v/merchants/Dashboard';
import Reports from '@v/reports-component/Index';
import PaymentReports from '@v/reports-component/PaymentReports';
import InvestmentReports from "@v/reports-component/InvestmentReports";
import TransactionReports from "@v/reports-component/TransactionReports";
import DefaultRateMerchantReports from '@v/reports-component/DefaultRateMerchantReports';
import Banking from '@v/Banking';
import Statements from '@v/Statements';
import Marketplace from '@v/marketplace/Marketplace';
import Docs from '@v/marketplace/Docs';
import Messages from '@v/Messages';
import Notifications from '@v/Notifications';
import CollectionNotes from '@v/CollectionNotes';
import FAQ from '@v/FAQ';
import EditProfile from '@v/EditProfile';
import Graph from '@v/Graph';
import TwoFactor from '@v/two-factor/TwoFactor';
import EnableTwoFactor from '@v/two-factor/EnableTwoFactor';
// 404
import NotFound from '@v/404';
import Login from '@v/Login';
import TwoFactorLogin from '@v/two-factor/Login';
import LoginWithRcode from '@v/two-factor/LoginWithRCode';

// STORE < init - used in clearCompare function >
import store from '@/store';
const routes = [
    // home
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta:{
            layout: 'home',
            user: true
        }
    },
    //Investment Data
    {
        path: '/advance-plus-investments-report',
        name: 'advance-plus-investments-report',
        component: InvestmentGraph,
        meta:{            
            user: true
        }
    },
    // merchant dashboard
    {
        path: '/merchants',
        name: 'merchants',
        component: Merchants,
        meta:{
            user: true
        }
    },
    // merchant dashboard
    {
        path: '/merchants/:id',
        name: 'merchant-dashboard',
        component: MerchantDashboard,
        meta:{
            user: true,
            stickView: true
        }
    },
    // banking
    {
        path: '/banking',
        name: 'banking',
        component: Banking,
        meta:{
            user: true
        }
    },
    // reports
    {
        path: '/reports',
        name: 'reports',
        component: Reports,
        meta:{
            user: true
        },
        redirect:'/reports/payment-reports',
        children:[
            {
                path: 'payment-reports',
                name: 'payment-reports',
                component: PaymentReports,
            },
            {
                path: "investment-reports",
                name: "investment-reports",
                component: InvestmentReports
            },
            {
                path: "transaction-reports",
                name: "transaction-reports",
                component: TransactionReports
            },
            {
                path: "default-rate-merchant-reports",
                name: "default-rate-merchant-reports",
                component: DefaultRateMerchantReports
            }
        ]
    },
    // statements
    {
        path: '/statements',
        name: 'statements',
        component: Statements,
        meta:{
            user: true
        }
    },
    // marketplace
    {
        path: '/marketplace',
        name: 'marketplace',
        component: Marketplace,
        meta:{
            participant: true,
            user: true,
            title: 'Marketplace | Investor Portal'
        }
    },
    {
        path: '/marketplace/docs/:id',
        name: 'marketplace-documents',
        component: Docs,
        meta:{
            participant: true,
            user: true
        }
    },
    {
        path: '/',
        redirect: '/dashboard',

    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta:{
            layout: 'plain',
            visitor: true,
            title: 'Login | Investor Portal'
        }
    },
    // 404
    {
        path: '/404',
        name: '404',
        component: NotFound,
        meta:{
            layout: 'plain'
        }
    },
    // 404 redirect
    {
        path: '*',
        redirect: '/404'
    },
    // messages detail page
    {
        path:'/messages',
        name: 'messages',
        component: Messages,
        meta:{
            user: true
        }
    },
    // notifications detail page
    {
        path:'/notifications',
        name: 'notifications',
        component: Notifications,
        meta:{
            user: true
        }
    },
    {
        path:'/collection-notes',
        name:'collection notes',
        component:CollectionNotes,
        meta:{
            user: true
        }
    },  
    {
        path:'/faq',
        name:'FAQ',
        component:FAQ,
        meta:{
            user: true
        }
    },
    {
        path: '/edit-profile',
        name: 'edit profile',
        component: EditProfile,
        meta:{
            user: true
        }
    },
    {
        path: '/analytics',
        name: 'analytics',
        component: Graph,
        meta:{
            user: true
        }
    },
    {
        path:'/two-factor',
        name: 'two-factor-authentication',
        component:TwoFactor,
        meta:{
            user:true
        }
    },
    {
        path:'/enable-two-factor-auth',
        name: 'enable-two-factor-authentication',
        component:EnableTwoFactor,
        meta:{
            user:true
        }
    },
    {
        path:'/two-factor-challenge',
        name: 'twofactorchallenge',
        component: TwoFactorLogin,
        meta:{
            layout: 'plain',
            visitor: true,
            title: 'Login | Investor Portal'
        }
    },
    {
        path:'/login-by-recovery-key',
        name: 'login-with-recovery-key',
        component: LoginWithRcode,
        meta:{
            layout: 'plain',
            visitor: true,
            title: 'Login | Investor Portal'
        }
    }
]
export default routes;
