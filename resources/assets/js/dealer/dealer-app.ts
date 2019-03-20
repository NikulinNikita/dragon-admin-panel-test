import Vue from 'vue';
import VueRouter from 'vue-router';

import store from './store';
import {routes} from './routes';

import popperLib from 'popper.js';
import bootstrapLib from 'bootstrap';

/* libs section */
let bootstrap: any = bootstrapLib;
let popper: any = popperLib;

import {$} from 'jquery';
/* libs section end */

/* components section */
import BaccaratField from "./components/baccarat/BaccaratField.vue";
import axios from "axios";
import {mapState} from "vuex";
/* components section end */

Vue.use(VueRouter);

const router = new VueRouter({
    routes: routes
});

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if(store.state['User'].id === null) {
            next({name: 'DealerLogin'})
        }
        else {
            next();
        }
    }

    next();
});

Vue.component('modal', {
  template: '#modal-template'
});

let instance: Vue = new Vue({
    router,
    store,
    components: {
        BaccaratField
    },
    data: {
        command: null
    },
    computed: {
        ...mapState({
            logoutOn: state => state['logoutOn']
        })
    }
}).$mount('#vueApp');

document.onkeypress = async (e) => {
    let event = e || window.event;

    let code: number | null = null;

    if(event.key !== undefined) {
        code = parseInt(event.key);
    }

    if(code && (code >= 0 && code <= 9)) {
        instance['command'] = await axios.post(`/api/dealer/dealerCommand`, {key: code}).then((response) => {
            return 'success';
        })
        .catch((error) => {
            console.error(error);
            return 'failed';
        });

        setTimeout(() => {
            instance['command'] = null;
        }, 3500)
    }
};