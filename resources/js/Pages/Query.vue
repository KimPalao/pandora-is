<template>
  <app-layout title="Dashboard">
    <template #header>
      <h2 class="h4 font-weight-bold">Query</h2>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title">What do you want to make?</h5>
              <div class="row">
                <div class="col-6">
                  <Dropdown
                    v-model="selected_product"
                    :options="products"
                    option-label="name"
                    option-value="id"
                    placeholder="Select a Product"
                    filter
                    @filter="filter_products"
                    :loading="loading_products"
                    class="w-100"
                  />
                </div>
                <div class="col-3">
                  <InputNumber v-model="quantity" class="w-100" />
                </div>
                <div class="col-2">
                  <Button @click="query">Query</Button>
                </div>
              </div>

              <h5 class="card-title mt-5">
                You need:
                <ProgressSpinner
                  v-if="query_loading"
                  style="width: 25px; height: 25px"
                />
              </h5>

              <div class="row">
                <div
                  class="card col-auto"
                  style="width: 18rem"
                  v-for="resource in resources"
                  :key="resource.resource.id"
                >
                  <img
                    :src="`/api/resource/${resource.resource.id}/image`"
                    class="card-img-top"
                    style="width: 200px; height: 200px"
                  />
                  <div class="card-body">
                    <h5 class="card-title">
                      <strong>x{{ resource.quantity }}</strong>
                    </h5>
                    <h5 class="card-subtitle">{{ resource.resource.name }}</h5>
                    <p class="card-text">
                      You currently have
                      <strong>{{ resource.resource.stock }}</strong> of this
                    </p>
                  </div>
                </div>
              </div>
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
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import Button from "primevue/button";
import ProgressSpinner from "primevue/progressspinner";

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
    Dropdown,
    InputNumber,
    Button,
    ProgressSpinner,
  },
  data() {
    return {
      products: [],
      selected_product: 0,
      quantity: 0,
      loading_products: false,

      resources: [],
      query_loading: false,
    };
  },
  methods: {
    async get_data() {
      const [sales] = await Promise.all([axios.get("/api/sales/recent")]);
      this.sales = sales.data.data;
    },
    async filter_products(event) {
      this.products_loading = true;
      try {
        const response = await axios.get("/api/products", {
          params: {
            name: event.value,
          },
        });
        this.products = response.data.data;
      } catch (e) {
      } finally {
        this.products_loading = false;
      }
    },
    async query() {
      this.query_loading = true;
      try {
        const response = await axios.get("/api/resolve-products", {
          params: {
            products: this.selected_product,
            quantities: this.quantity,
          },
        });
        this.resources = response.data.resources;
      } catch (e) {
        console.log(e);
      } finally {
        this.query_loading = false;
      }
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
