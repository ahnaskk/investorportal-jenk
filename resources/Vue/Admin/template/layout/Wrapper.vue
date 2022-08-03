<template>
    <section
        class="section-wrapper"
        :class="[{'layout-app': layout != 'plain'}]"
    >
        <!-- PLAIN -->
        <div
            class="layout-wrapper plain"
            v-if="layout == 'plain'"
        >
            <slot></slot>
        </div>
        <!-- HOME -->
        <div
            class="layout-wrapper app"
            v-else-if="layout == 'home'"
        >
            <Head />
            <section class="main-view-slot">
                <slot></slot>
            </section>
            <Footer />
        </div>
        <!-- DEFAULT -->
        <div
            class="layout-wrapper app"
            v-else
        >
            <Head />
            <section class="main-view-slot">
                <div class="main-wrapper">
                    <Sidebar :list="sidebar" />
                    <div class="content-wrapper">
                        <Breadcrumb />
                        <preloader v-if="loading" />
                        <slot></slot>
                    </div>
                </div>
            </section>
            <Footer />
        </div>
    </section>
</template>

<script>
    import Head from './Header';
    import Footer from './Footer';
    import Breadcrumb from './Breadcrumb';
    import Sidebar from '@ac/sidebar/Sidebar';
    export default {
        name: 'layout-wrapper',
        components:{
            Head,
            Footer,
            Breadcrumb,
            Sidebar,
            errorMsg: 'Error Message to show at the layout wrapper'
        },
        props:{
            layout: String
        },
        data(){
            return {
                loading: false,
                error: false,
                sidebar:[
                    {
                        open: false,
                        icon:'dashboard',
                        link:'/home',
                        title:'Dashboard'
                    },
                    {
                        open: false,
                        icon:'investors',
                        link:'/investors',
                        title:'Investors',
                    },
                    {
                        open: false,
                        icon:'collection-user',
                        link:'/b',
                        title:'Collection Users'
                    },
                    {
                        open: false,
                        icon:'companies',
                        link:'/c',
                        title:'Companies'
                    },
                    {
                        open: false,
                        icon:'admin',
                        link:'/d',
                        title:'Admin'
                    },
                    {
                        open: false,
                        icon:'editor',
                        link:'/e',
                        title:'Editor'
                    },
                    {
                        open: false,
                        icon:'lender',
                        link:'/f',
                        title:'Lender'
                    },
                    {
                        open: false,
                        icon:'viewer',
                        link:'/g',
                        title:'Viewer'
                    },
                    {
                        open: false,
                        icon:'roles-and-permissions',
                        link:'/h',
                        title:'Roles and Permissions'
                    },
                    {
                        open: false,
                        icon:'merchants',
                        link:'/i',
                        title:'Merchants'
                    },
                    {
                        open: false,
                        icon:'marketing-offers',
                        link:'/j',
                        title:'Marketing Offers'
                    },
                    {
                        open: false,
                        icon:'transactions',
                        link:'/k',
                        title:'Transactions'
                    },
                    {
                        open: false,
                        icon:'reconciliation-request',
                        link:'/l',
                        title:'Reconciliation Request'
                    },
                    {
                        open: false,
                        icon:'velocity-distributions',
                        link:'/m',
                        title:'Velocity Distributions'
                    },
                    {
                        open: false,
                        icon:'merchant-batches',
                        link:'/n',
                        title:'Merchant Batches'
                    },
                    {
                        open: false,
                        icon:'payments',
                        link:'/o',
                        title:'Payments'
                    },
                    {
                        open: false,
                        icon:'marketplace',
                        link:'/p',
                        title:'Marketplace'
                    },
                    {
                        open: false,
                        icon:'reports',
                        link:'/reports',
                        title:'Reports',
                        submenu:[
                            {
                                link: '/reports/default-rate',
                                title: 'Default Rate'
                            },
                            {
                                link: '/reports/default-rate-merchants',
                                title: 'Default Rate(Merchants)'
                            },
                            {
                                link: '/reports/delinquent',
                                title: 'Delinquent'
                            },
                            {
                                link: '/reports/payment-left',
                                title: 'Payment Left'
                            },
                            {
                                link: '/reports/lender-delinquent',
                                title: 'Lender Delinquent'
                            },
                            {
                                link: '/reports/profitability2',
                                title: 'Profitability(65/20/15)'
                            },
                            {
                                link: '/reports/profitability3',
                                title: 'Profitability(50/30/20)'
                            },
                            {
                                link: '/reports/profitability4',
                                title: 'Profitability(50/50)'
                            },
                            {
                                link: '/reports/investment',
                                title: 'Investment'
                            },
                            {
                                link: '/reports/investor-assignment',
                                title: 'Investor Assignment '
                            },
                            {
                                link: '/reports/investor-reassignment',
                                title: 'Investor Reassignment'
                            },
                            {
                                link: '/reports/liquidity',
                                title: 'Liquidity'
                            },
                            {
                                link: '/reports/payments',
                                title: 'Payments'
                            },
                            {
                                link: '/reports/revenue-recognition',
                                title: 'Revenue Recognition'
                            },
                            {
                                link: '/reports/transactions',
                                title: 'Transactions'
                            },
                            {
                                link: '/reports/accrued-pre-return',
                                title: 'Accrued Pre Return'
                            },
                            {
                                link: '/reports/debt-investor',
                                title: 'Debt Investor'
                            },
                            {
                                link: '/reports/equity-investor',
                                title: 'Equity Investor'
                            },
                            {
                                link: '/reports/total-portfolio-earnings',
                                title: 'Total Portfolio Earnings'
                            },
                            {
                                link: '/reports/overpayment-report',
                                title: 'OverPayment Report'
                            },
                            {
                                link: '/reports/merchants-per-diff',
                                title: 'Merchants Per Diff'
                            },
                            {
                                link: '/reports/velocity-profitability',
                                title: 'Velocity Profitability'
                            },
                            {
                                link: '/reports/anticipated-payment',
                                title: 'Anticipated Payment'
                            }
                        ]
                    },
                    {
                        open: false,
                        icon:'logs',
                        link:'/r',
                        title:'Logs'
                    },
                    {
                        open: false,
                        icon:'bank-details',
                        link:'/s',
                        title:'Bank Details'
                    },
                    {
                        open: false,
                        icon:'reconcile',
                        link:'/t',
                        title:'Reconcile'
                    },
                    {
                        open: false,
                        icon:'wires-arch',
                        link:'/u',
                        title:'Wires/ach'
                    },
                    {
                        open: false,
                        icon:'template-management',
                        link:'/v',
                        title:'Template Management'
                    },    
                    {
                        open: false,
                        icon:'settings',
                        link:'/w',
                        title:'Settings'
                    },
                    {
                        open: false,
                        icon:'logout',
                        link:'/x',
                        title:'Logout'
                    }
                ]
            }
        }
    }
</script>

<style
    src="~al/wrapper.scss"
    lang="scss"
    scoped
></style>