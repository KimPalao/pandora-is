<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Order</h2>
        </div>
        <div class="col-auto">
          <div class="col-auto">
            <button class="btn btn-info" @click="new_bag_form_visible = true">
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
  },
  data() {
    return {
      orders: [],
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
    };
  },
  computed: {
    selected_order() {
      if (this.selected_order_index === null) return {};
      return this.orders[this.selected_order_index];
    },
  },
  methods: {
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
    async get_data() {
      this.search();
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