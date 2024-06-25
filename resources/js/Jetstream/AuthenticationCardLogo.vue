<template>
  <Link :href="'/'" class="d-flex justify-content-center mb-4">
    <img src="/ico/favicon-96x96.png" width="64" alt="Pandora" />
  </Link>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from '@inertiajs/inertia-vue3';

export default defineComponent({
  components: {
    Link,
  },
})
</script>
