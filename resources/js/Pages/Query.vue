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
              <div class="row">
                <div class="col">
                  <h5 class="card-title">What do you want to make?</h5>
                </div>
                <div class="col-auto">
                  <Button @click="increment_products" class="me-2"
                    >+ Add Product</Button
                  >
                  <Button @click="query">Query</Button>
                </div>
              </div>
              <div
                class="row mb-4"
                v-for="(selected, index) in selection"
                :key="`product-${index}`"
              >
                <div class="col-1">
                  <Button @click="remove(index)" v-if="index !== 0">x</Button>
                </div>
                <div class="col-5">
                  <Dropdown
                    v-model="selected.product"
                    :options="selected.products"
                    option-label="name"
                    option-value="id"
                    placeholder="Select a Product"
                    filter
                    @filter="filter_products($event, index)"
                    :loading="selected.loading"
                    class="w-100"
                  />
                </div>
                <div class="col-3">
                  <InputNumber v-model="selected.quantity" class="w-100" />
                </div>
                <div class="col-2"></div>
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
      selection: [
        {
          product: 0,
          products: [],
          loading: false,
          quantity: 0,
        },
      ],

      resources: [],
      query_loading: false,
    };
  },
  methods: {
    increment_products() {
      this.selection.push({});
    },
    async filter_products(event, index) {
      this.selection[index].loading = true;
      try {
        const response = await axios.get("/api/products", {
          params: {
            name: event.value,
          },
        });
        this.selection[index].products = response.data.data;
      } catch (e) {
      } finally {
        this.selection[index].loading = false;
      }
    },
    remove(index) {
      this.selection.splice(index, 1);
    },
    async query() {
      this.query_loading = true;
      try {
        const products = [];
        const quantities = [];
        for (let selection of this.selection) {
          products.push(selection.product);
          quantities.push(selection.quantity);
        }
        const response = await axios.get("/api/resolve-products", {
          params: {
            products: products.join(","),
            quantities: quantities.join(","),
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
});
</script>
