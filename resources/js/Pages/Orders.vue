<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Order</h2>
        </div>
        <div class="col-auto">
          <div class="col-auto">
            <button class="btn btn-info" @click="new_order_form_visible = true">
              New Order
            </button>
          </div>
        </div>
      </div>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <DataTable
                lazy
                responsiveLayout="scroll"
                paginator
                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                filterDisplay="menu"
                sortMode="multiple"
                :value="orders"
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                :loading="loading"
                :totalRecords="totalRecords"
                v-model:filters="filters"
                @page="params = $event"
                @sort="params = $event"
                @filter="params.filters = filters"
                @row-click="show_order($event.index)"
              >
                <Column field="created_at" header="Placed" sortable>
                  <template #body="slotProps">
                    {{ datetimeToLocal(new Date(slotProps.data.created_at)) }}
                  </template>
                </Column>
                <Column field="id" header="Number">
                  <template #body="slotProps">
                    {{ slotProps.data.id.toString().padStart(10, "0") }}
                  </template>
                </Column>
                <Column field="total" header="total">
                  <template #body="slotProps">
                    {{ renderCurrency(slotProps.data.total) }}
                  </template>
                </Column>
              </DataTable>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Dialog
      v-model:visible="show_selected_order"
      modal
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>Order #{{ selected_order.id.toString().padStart(10, "0") }}</h3>
      </template>
      <div class="row">
        <div class="col">
          <p>Total: {{ renderCurrency(selected_order.total) }}</p>
          <p>
            Placed: {{ datetimeToLocal(new Date(selected_order.created_at)) }}
          </p>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <h5>Products</h5>
        </div>
      </div>
      <div
        class="row"
        v-for="(product, index) in selected_order.products"
        :key="`order-product-${index}`"
      >
        <div class="col">
          <p>{{ product.name }} x {{ product.pivot.quantity }}</p>
        </div>
      </div>
    </Dialog>

    <Dialog
      v-model:visible="new_order_form_visible"
      :modal="true"
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>New Order</h3>
      </template>
      <form @submit.prevent="submit_order">
        <div class="row">
          <div class="col">
            <InputNumber
              v-model="new_order.total"
              mode="currency"
              currency="PHP"
              locale="en-US"
              placeholder="Total"
              :disabled="submitting"
              required
            />
          </div>
          <div class="col">
            <Calendar
              v-model="new_order.created_at"
              :showTime="true"
              class="w-100"
              placeholder="Time"
              :disabled="submitting"
              required
            />
          </div>
        </div>
        <div class="row mt-4 mb-2">
          <div class="col"><h5>Products</h5></div>
          <div class="col-auto">
            <Button @click="increment_products" class="me-2"
              >+ Add Product</Button
            >
          </div>
        </div>
        <div
          v-for="(product, index) in new_order.products"
          class="row mt-2"
          :key="index"
        >
          <div class="col-1">
            <Button @click="remove(index)" v-if="index !== 0">x</Button>
          </div>
          <div class="col-5">
            <Dropdown
              v-model="product.id"
              :options="products"
              option-label="name"
              option-value="id"
              placeholder="Select a Product"
              filter
              class="w-100"
            />
          </div>
          <div class="col-3">
            <InputNumber v-model="product.quantity" class="w-100" />
          </div>
          <div class="col-2"></div>
        </div>
        <div class="row mt-4">
          <div class="col-auto ms-auto">
            <button
              class="btn btn-primary"
              type="submit"
              :disabled="submitting"
            >
              Submit
            </button>
          </div>
        </div>
      </form>
    </Dialog>
  </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import MultiSelect from "primevue/multiselect";
import { Link } from "@inertiajs/inertia-vue3";
import { FilterService, FilterMatchMode } from "primevue/api";
import { ObjectUtils } from "primevue/utils";
import Dropdown from "primevue/dropdown";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Calendar from "primevue/calendar";
import FileUpload from "primevue/fileupload";
import Button from "primevue/button";

FilterService.register("InOrNull", (value, filter) => {
  if (filter === undefined || filter === null || filter.length === 0) {
    return true;
  }

  for (let i = 0; i < filter.length; i++) {
    if (ObjectUtils.equals(value, filter[i])) {
      return true;
    }
    if (filter[i]?.id === null && value === null) {
      return true;
    }
  }

  return false;
});
export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
    Link,
    MultiSelect,
    Dropdown,
    Dialog,
    InputText,
    InputNumber,
    Calendar,
    FileUpload,
    Button,
  },
  data() {
    return {
      orders: [],
      products: [],
      sites: [],
      columns: [
        {
          field: "name",
          header: "Name",
        },
        {
          field: "total",
          header: "Total",
        },
        {
          field: "created_at",
          header: "Placed",
        },
      ],
      filters: {},
      loading: false,

      params: {},
      totalRecords: 0,

      selected_order_index: null,
      show_selected_order: false,

      new_order_form_visible: false,
      new_order: {
        total: 0,
        created_at: null,
        products: [{}],
      },
    };
  },
  computed: {
    selected_order() {
      if (this.selected_order_index === null) return {};
      return this.orders[this.selected_order_index];
    },
  },
  methods: {
    async submit_order() {
      try {
        const response = await axios.post("/api/orders", this.new_order);
        this.search();
      } catch (e) {
        console.log(e);
      }
    },
    increment_products() {
      this.new_order.products.push({});
    },

    show_order(index) {
      this.selected_order_index = index;
      this.show_selected_order = true;
    },
    // DataTable

    on_page(event) {
      this.params = event;
    },
    async search() {
      this.loading = true;
      const response = await axios.get("/api/orders", {
        params: this.params,
      });
      this.orders = response.data.data;
      this.totalRecords = response.data.count;
      this.loading = false;
    },
    async get_products() {
      const response = await axios.get("/api/products");
      this.products = response.data.data;
    },
    async get_data() {
      this.search();
      this.get_products();
    },
  },
  beforeMount() {
    this.params = {
      first: 0,
      sortField: null,
      sortOrder: null,
      filters: this.filters,
    };
  },
  mounted() {
    this.get_data();
  },
  watch: {
    params: {
      handler() {
        this.search();
      },
      deep: true,
    },
  },
});
</script>