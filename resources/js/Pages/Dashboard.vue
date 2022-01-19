<template>
  <app-layout title="Dashboard">
    <template #header>
      <h2 class="h4 font-weight-bold">Dashboard</h2>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-6">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title text-danger">Products Low On Stock</h5>
              <ol>
                <li v-for="product in products" :key="`product-${product.id}`">
                  {{ product.name }} x {{ product.stock }}
                </li>
              </ol>
            </div>
          </div>
        </div>

        <div class="col-6">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title text-danger">Resources Low On Stock</h5>
              <ol>
                <li
                  v-for="resource in resources"
                  :key="`resource-${resource.id}`"
                >
                  {{ resource.name }} x {{ resource.stock }}
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
  },
  data() {
    return {
      products: [],
      resources: [],
    };
  },
  methods: {
    async get_data() {
      const [products, resources] = await Promise.all([
        axios.get("/api/products/low-on-stock"),
        axios.get("/api/resources/low-on-stock"),
      ]);
      this.products = products.data.data;
      this.resources = resources.data.data;
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
