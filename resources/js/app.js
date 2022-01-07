require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import { DateTime } from "luxon";

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

const currencyMixin = {
  data() {
    return {
      price_formatter: Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })
    };
  },
  methods: {
    renderCurrency(price) {
      return this.price_formatter.format(price);
    }
  }
};

const datetimeMixin = {
  methods: {
    datetimeToLocal(datetime, format = "yyyy-MM-dd HH:mm:ss") {
      return DateTime.fromISO(datetime.toISOString()).toFormat(format);
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
