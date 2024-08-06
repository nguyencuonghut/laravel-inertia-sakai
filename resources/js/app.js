import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

import Aura from '@primevue/themes/aura';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';

import './PrimeVue/assets/styles.scss';
import './PrimeVue/assets/tailwind.css';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue')
    return pages[`./Pages/${name}.vue`]()
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                darkModeSelector: '.app-dark'
            }
        }
      })
      .use(ToastService)
      .use(ConfirmationService)
      .mount(el)
  },
})
