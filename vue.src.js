const
    investorsSrc = "resources/Vue/Investors",
    adminSrc = "resources/Vue/Admin",
    merchantsSrc = "resources/Vue/Merchants"
module.exports = {
    admin: {
        js: {
            from: `${adminSrc}/app.js`,
            to: 'public/vue/app.admin.js'
        }
    },
    investor: {
        js: {
            from: `${investorsSrc}/app.js`,
            to: 'public/vue/app.investors.js'
        }
    },
    merchant: {
        js: {
            from: `${merchantsSrc}/app.js`,
            to: 'public/vue/app.merchants.js'
        },
        sass: {
            from: `${merchantsSrc}/styles/app.scss`,
            to: 'public/css/app.merchants.css'
        }
    }
}