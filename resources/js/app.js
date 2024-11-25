    // resources/js/app.js
    // import './bootstrap';
    // import '../css/app.css';
    // import Router from '@/router';
    // import store from '@/store';
    // import { createApp } from 'vue';
    // import App from './App.vue'; // Import your root component

    // // Initialize the store
    // store.dispatch('auth/initialize').then(() => {
    // const app = createApp(App); // Pass App to createApp
    // app.use(Router);
    // app.use(store);
    // app.mount('#app');
    // });

// resources/js/app.js
import './bootstrap';
import '../css/app.css';
import Router from '@/router';
import store from '@/store';
import { createApp } from 'vue';
import App from './App.vue'; // Import your root component

// Import vue-gtag for Google Analytics
import VueGtag from 'vue-gtag';
// Google Tag Manager
import { createGtm } from '@gtm-support/vue-gtm';

// Initialize the store
store.dispatch('auth/initialize').then(() => {
    const app = createApp(App); // Pass App to createApp

    // Use vue-gtag for Google Analytics
    app.use(VueGtag, {
        config: { id: 'G-V14H8KVHQZ' } // Replace with your Google Analytics Measurement ID
    }, Router);
    app.use(
        createGtm({
          id: "G-V14H8KVHQZ",
          vueRouter: Router
        })
    )
    app.use(Router);
    app.use(store);
    app.mount('#app');
});
