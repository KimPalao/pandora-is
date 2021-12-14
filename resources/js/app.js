require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';

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

const bootstrapMixin = {
  data() {
    return {
      modals: {},
    };
  },
  methods: {
    open_modal(name) {
      if (!(name in this.modals))
        this.modals[name] = new bootstrap.Modal(this.$refs[name]);
      this.modals[name].show();
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
      .mixin(bootstrapMixin)
      .mount(el);
  },
});

InertiaProgress.init({ color: '#4B5563' });
