/**
 * @param _fields All fields to be initialized when using the getData
 * action in the store (api.js).
 * @method run Sets every field data to null and loading to false.
 * @method initGetters sets getters in store for every field
 */
class Fields{
    constructor(_fields){
        this.fields = _fields
        this.initalized = {
            loading:{}
        },
        this.getters = {}
    }
    get size(){
        return this.fields.length
    }
    run(){
        for( let i=0;i < this.size; i++){
            this.initalized[this.fields[i]] = null
            this.initalized.loading[this.fields[i]] = false
        }
        return this.initalized
    }
    initGetters(){
        for( let i=0;i < this.size; i++){
            this.getters[this.fields[i]] = (s) => s[this.fields[i]]
        }
        return this.getters
    }
}

let initFields = new Fields([
    'investorDashboard',
    'transaction',
    'marketplace',
    'merchants',
    'marketplaceFilters',
    'merchantData',
    'reports',
    'notificatios',
    'dashboardGraph',
    'statements',
    'merchantPaymentData',
    'banks',
    'enabletwofactor',
    'twofactor',
    'faq',
    'enableTwoFactor',
    'twoFactorStatus',
    'header',
    'requestMoneyField',
    'requestPayOffField',
    'crmapi',
    'latesPayments',
    'merchantGraph',
    'merchantRequest'
])


let apiState = initFields.run();
let apiGetters = initFields.initGetters();
export { apiState , apiGetters}