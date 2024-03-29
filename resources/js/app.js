window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = BASE_URL;
window.axios.defaults.headers.common['Authorization'] = 'bearer ' + window.localStorage.getItem('token');
window.moment = require('moment');
window.Vue = require('vue');

import ElementUI from 'element-ui';
import locale from 'element-ui/lib/locale/lang/en'
import router from './router'
import store from './store'
import VueMask from 'v-mask'

Vue.use(ElementUI, { locale });
Vue.use(VueMask);

Vue.filter('readableDateTime', function(v) {
    return v ? moment(v).format('DD-MMM-YYYY HH:mm') : ''
})

Vue.filter('readableDate', function(v) {
    return v ? moment(v).format('DD-MMM-YYYY') : ''
})

Vue.filter('formatNumber', function (v) {
    try {
        v += '';
        var x = v.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    } catch (error) {
        return 0
    }

});

import DefaultLayout from "./layouts/default.vue";
import LoginLayout from "./layouts/login.vue";

Vue.component('default-layout', DefaultLayout);
Vue.component('login-layout', LoginLayout);
Vue.component('App', require('./App').default);

const app = new Vue({
    el: '#app',
    store, router,
    render: function(createElement) {
        return createElement('App')
    }
});
