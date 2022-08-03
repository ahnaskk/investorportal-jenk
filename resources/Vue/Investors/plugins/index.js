import Icons from './fontAwesome'
import loader from '@directive/loader'
import Vuelidate from 'vuelidate'
import InputMask from 'vue-input-mask'
import preloader from '@c/preloader'
import emptyBox from '@c/emptyBox'
import moneyFilter from '@/filters/money'
import limitFilter from '@/filters/limit'
import capitalize from '@/filters/capitalize'
import mixinCollection from '@/mixins'
import Paginate from 'vuejs-paginate'
import vClickOutside from 'v-click-outside'
import VueSignaturePad from 'vue-signature-pad'
import VueCurrencyInput from 'vue-currency-input'
import vSelect from 'vue-select'
import ths from '@c/ths'
import VueYoutube from 'vue-youtube'
import VueMask from 'v-mask'
const pluginOptions = {
  /* see config reference */
  globalOptions: { currency: 'USD' }
}
 
export default {
    install(Vue){
        // PLUGINS
        Vue.use(Icons)
        Vue.use(Vuelidate)
        Vue.use(VueSignaturePad)
        Vue.use(VueCurrencyInput, pluginOptions)
        Vue.use(vClickOutside);
        Vue.use(VueYoutube)
        Vue.use(VueMask);
        // DIRECTIVES
        Vue.directive('loader',loader)
        // COMPONENTS
        Vue.component('input-mask', InputMask)
        Vue.component('preloader',preloader)
        Vue.component('emptyBox',emptyBox)
        Vue.component('paginate', Paginate)
        Vue.component('v-select', vSelect)
        Vue.component('ths',ths)
        // FILTERS
        Vue.filter('formatMoney',moneyFilter)
        Vue.filter('capitalize',capitalize)
        Vue.filter('limit',limitFilter)
        Vue.filter('acHash',v=>'xxx'+v.toString().slice(-4))
        // MIXINS
        Vue.mixin(mixinCollection)
    }
}
