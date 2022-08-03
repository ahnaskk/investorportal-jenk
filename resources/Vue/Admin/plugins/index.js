import Icons from './fontAwesome';
import loader from '@a/directives/loader';
import Vuelidate from 'vuelidate';
import InputMask from 'vue-input-mask';
import preloader from '@ac/preloader';
import emptyBox from '@ac/emptyBox';
import moneyFilter from '@a/filters/money';
import limitFilter from '@a/filters/limit';
import capitalize from '@a/filters/capitalize';
import mixinCollection from '@a/mixins';
import Paginate from 'vuejs-paginate';
import vClickOutside from 'v-click-outside';
import VueSignaturePad from 'vue-signature-pad';

export default {
    install(Vue){
        // PLUGINS
        Vue.use(Icons);
        Vue.use(Vuelidate);
        Vue.use(vClickOutside);
        Vue.use(VueSignaturePad);
        // DIRECTIVES
        Vue.directive('loader',loader);
        // COMPONENTS
        Vue.component('input-mask', InputMask);
        Vue.component('preloader',preloader);
        Vue.component('emptyBox',emptyBox);
        Vue.component('paginate', Paginate);
        // FILTERS
        Vue.filter('formatMoney',moneyFilter);
        Vue.filter('capitalize',capitalize);
        Vue.filter('limit',limitFilter);
        // MIXINS
        Vue.mixin(mixinCollection);
    }
}
