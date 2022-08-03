import Home from '@av/Home'
import Investors from '@av/Investors'
// 404
import NotFound from '@av/404'
import Login from '@av/Login'
//Reports
import Reports from '@av/reports/Reports'
import DefaultRate from '@av/reports/DefaultRate'
import DefaultRateMerchants from '@av/reports/DefaultRateMerchants'
import Delinquent from '@av/reports/Delinquent'
import EquityInvestor from '@av/reports/EquityInvestor'
import Investment from '@av/reports/Investment'
import InvestorAssignment from '@av/reports/InvestorAssignment'
import InvestorReassignment from '@av/reports/InvestorReassignment'
import LenderDelinquent from '@av/reports/LenderDelinquent'
import Liquidity from '@av/reports/Liquidity'
import OverPaymentReport from '@av/reports/OverPaymentReport'
import PaymentLeft from '@av/reports/PaymentLeft'
import Profitability2 from '@av/reports/Profitability2'
import Profitability3 from '@av/reports/Profitability3'
import Profitability4 from '@av/reports/Profitability4'
import RevenueRecognition from '@av/reports/RevenueRecognition'
import TotalPortfolioEarnings from '@av/reports/TotalPortfolioEarnings'
import Transactions from '@av/reports/Transactions'
import VelocityProfitability from '@av/reports/VelocityProfitability'
import Payments from '@av/reports/Payments'
import AccruedPreReturn from '@av/reports/AccruedPreReturn'
import DebtInvestor from '@av/reports/DebtInvestor'
import AnticipatedPayment from '@av/reports/AnticipatedPayment'
import MerchantsPerDiff from '@av/reports/MerchantsPerDiff'



// STORE < init - used in clearCompare function >
import store from '@a/store';
const routes = [
    {
        path: '',
        redirect: '/home'
    },
    {
        path: '/home',
        name: 'home',
        component: Home,
        meta: {
            user: true
        }
    },
    //investors
    {
        path: '/investors',
        name: 'investors',
        component: Investors,
        meta: {
            user: true
        }
    },
    // login
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta:{
            layout: 'plain',
            visitor: true
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
    //Reports
    {
        path: '/reports',
        name: 'reports',
        meta:{
            user: true
        },
        component: Reports,
        children:[
            {
                path: '',
                redirect: 'default-rate'
            }, 
            {
                path: 'default-rate',
                name: 'default rate',
                component: DefaultRate,
                meta: {
                    user: true
                }
            },    
            {
                path: 'default-rate-merchants',
                name: 'default rate merchant',
                component: DefaultRateMerchants,
                meta: {
                    user: true
                }
            },    
            {
                path: 'delinquent',
                name: 'delinquent',
                component: Delinquent,
                meta: {
                    user: true
                }
            },    
            {
                path: 'equity-investor',
                name: 'equity-investor',
                component: EquityInvestor,
                meta: {
                    user: true
                }
            },    
            {
                path: 'investment',
                name: 'investment',
                component: Investment,
                meta: {
                    user: true
                }
            },    
            {
                path: 'investor-assignment',
                name: 'investor-assignment',
                component: InvestorAssignment,
                meta: {
                    user: true
                }
            },    
            {
                path: 'investor-reassignment',
                name: 'investor-reassignment',
                component: InvestorReassignment,
                meta: {
                    user: true
                }
            },    
            {
                path: 'lender-delinquent',
                name: 'lender-delinquent',
                component: LenderDelinquent,
                meta: {
                    user: true
                }
            },    
            {
                path: 'liquidity',
                name: 'liquidity',
                component: Liquidity,
                meta: {
                    user: true
                }
            },    
            {
                path: 'overpayment-report',
                name: 'overpayment-report',
                component: OverPaymentReport,
                meta: {
                    user: true
                }
            },    
            {
                path: 'payment-left',
                name: 'payment-left',
                component: PaymentLeft,
                meta: {
                    user: true
                }
            },    
            {
                path: 'payments',
                name: 'payments',
                component: Payments,
                meta: {
                    user: true
                }
            },    
            {
                path: 'profitability2',
                name: 'profitability2',
                component: Profitability2,
                meta: {
                    user: true
                }
            },    
            {
                path: 'profitability3',
                name: 'profitability3',
                component: Profitability3,
                meta: {
                    user: true
                }
            },    
            {
                path: 'profitability4',
                name: 'profitability4',
                component: Profitability4,
                meta: {
                    user: true
                }
            },    
            {
                path: 'revenue-recognition',
                name: 'revenue-recognition',
                component: RevenueRecognition,
                meta: {
                    user: true
                }
            },    
            {
                path: 'total-portfolio-earnings',
                name: 'total-portfolio-earnings',
                component: TotalPortfolioEarnings,
                meta: {
                    user: true
                }
            },    
            {
                path: 'transactions',
                name: 'transactions',
                component: Transactions,
                meta: {
                    user: true
                }
            },    
            {
                path: 'velocity-profitability',
                name: 'velocity-profitability',
                component: VelocityProfitability,
                meta: {
                    user: true
                }
            },    
            {
                path: 'accrued-pre-return',
                name: 'accrued-pre-return',
                component: AccruedPreReturn,
                meta: {
                    user: true
                }
            },    
            {
                path: 'debt-investor',
                name: 'debt-investor',
                component: DebtInvestor,
                meta: {
                    user: true
                }
            },    
            {
                path: 'anticipated-payment',
                name: 'anticipated-payment',
                component: AnticipatedPayment,
                meta: {
                    user: true
                }
            },
            {      
                path: 'merchants-per-diff',
                name: 'merchants-per-diff',
                component: MerchantsPerDiff,
                meta: {
                    user: true
                }
            }    
        ]
    },
]
export default routes;
