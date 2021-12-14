require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import { DateTime } from "luxon";

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

const currencyMixin = {
  methods: {
    renderCurrency(price) {
      return (price).toLocaleString('en-US', {
        style: 'currency',
        currency: 'PHP',
      });
    }
  }
};

const datetimeMixin = {
  methods: {
    datetimeToLocal(datetime) {
      return DateTime.fromISO(datetime.toISOString()).toFormat(
        "yyyy-MM-dd HH:mm:ss"
      );
    }
  }
};


createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({ el, app, props, plugin }) {
    return createApp({ render: () => h(app, props) })
      .use(plugin)
      .use(PrimeVue)
      .mixin({ methods: { route } })
      .mixin(currencyMixin)
      .mixin(datetimeMixin)
      .mount(el);
  },
});

InertiaProgress.init({ color: '#4B5563' });
