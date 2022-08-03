import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
// import { dom } from '@fortawesome/fontawesome-svg-core';
// dom.watch();

import {
} from '@fortawesome/free-regular-svg-icons';
import {
    faEnvelope, faBell, faUser, faCheck, faAngleRight, faAngleDown, faAngleUp,faBars,faInfoCircle, faPlus, faMinus, faSortUp, faSortDown,faAngleLeft
} from '@fortawesome/free-solid-svg-icons';
 
library.add(
    faEnvelope,
    faBell,
    faUser,
    faCheck,
    faAngleRight,
    faAngleDown,
    faAngleUp,
    faBars,
    faInfoCircle,
    faPlus,
    faMinus,
    faSortUp,
    faSortDown,
    faAngleLeft
);

export default {
    install(Vue){
        Vue.component('icon', FontAwesomeIcon)
    }
}