<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Sales</h2>
        </div>
        <div class="col-auto">
          <div class="col-auto">
            <button class="btn btn-info" @click="new_bag_form_visible = true">
              New Bag
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
                :value="sales"
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                :loading="loading"
                :totalRecords="totalRecords"
                v-model:filters="filters"
                @page="params = $event"
                @sort="params = $event"
                @filter="params.filters = filters"
              >
                <Column field="datetime" header="Date & Time" sortable>
                  <template #body="slotProps">
                    <Link :href="route('bag', slotProps.data.id)">{{
                      slotProps.data.datetime
                    }}</Link>
                  </template></Column
                >
                <Column field="bag.name" header="Name">
                  <template #body="slotProps">
                    <Link :href="route('bag', slotProps.data.id)">{{
                      slotProps.data.bag.name
                    }}</Link>
                  </template></Column
                >
                <Column field="bag.price" header="SRP">
                  <template #body="slotProps">
                    {{ renderCurrency(slotProps.data.bag.price) }}
                  </template>
                </Column>
                <Column field="price" header="Sale" sortable>
                  <template #body="slotProps">
                    {{ renderCurrency(slotProps.data.price) }}
                  </template>
                </Column>
                <Column
                  field="site.name"
                  header="Location"
                  filterField="site"
                  :showFilterMatchModes="false"
                  :filterMenuStyle="{ width: '14rem' }"
                >
                  <template #body="slotProps">
                    {{ slotProps.data.site.name ?? "Sold" }}
                  </template>
                  <template #filter="{ filterModel }">
                    <div class="p-mb-3 p-text-bold">Site</div>
                    <MultiSelect
                      v-model="filterModel.value"
                      :options="sites"
                      optionLabel="name"
                      placeholder="Any"
                      class="p-column-filter"
                    >
                      <template #option="slotProps">
                        <div class="p-multiselect-representative-option">
                          <span class="image-text">{{
                            slotProps.option.name
                          }}</span>
                        </div>
                      </template>
                    </MultiSelect>
                  </template>
                </Column>
              </DataTable>
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
      sales: [],
      sites: [],
      columns: [
        {
          field: "name",
          header: "Name",
        },
        {
          field: "price",
          header: "Price",
        },
      ],
      filters: {
        site: {
          value: null,
          matchMode: "InOrNull",
        },
      },
      loading: false,

      params: {},
      totalRecords: 0,
    };
  },
  methods: {
    // DataTable

    on_page(event) {
      this.params = event;
    },
    async search() {
      this.loading = true;
      const sales = await axios.get("/api/sales", {
        params: this.params,
      });
      this.sales = sales.data.data;
      this.totalRecords = sales.data.count;
      this.loading = false;
    },
    async get_data() {
      const [_, sites] = await Promise.all([
        this.search(),
        axios.get("/api/sites"),
      ]);
      this.sites = sites.data.data;
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