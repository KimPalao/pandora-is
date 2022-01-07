<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Resources</h2>
        </div>
        <div class="col-auto">
          <div class="col-auto">
            <button class="btn btn-info" @click="entity_form_visible = true">
              New Resource
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
                :value="resources"
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                :loading="loading"
                :totalRecords="totalRecords"
                v-model:filters="filters"
                @page="params = $event"
                @sort="params = $event"
                @filter="params.filters = filters"
              >
                <template #header>
                  <div class="p-d-flex p-jc-between p-ai-center">
                    <h5 class="p-m-0">Resources</h5>
                    <span class="p-input-icon-left">
                      <i class="pi pi-search" />
                      <InputText
                        v-model="filters['global'].value"
                        placeholder="Keyword Search"
                      />
                    </span>
                  </div>
                </template>
                <Column field="name" header="Name" sortable />
                <Column field="stock" header="Stock" sortable>
                  <template #body="slotProps">
                    {{ slotProps.data.stock }}
                    {{ slotProps.data.unit || "Units" }}
                  </template>
                </Column>
                <Column field="" header="">
                  <template #body="slotProps">
                    <Button @click="show_update_stock(slotProps.index)"
                      >Update Stock</Button
                    >
                  </template>
                </Column>
              </DataTable>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Dialog
      v-model:visible="entity_form_visible"
      :modal="true"
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>New Resource</h3>
      </template>
      <form @submit.prevent="submit">
        <div class="row">
          <div class="col">
            <InputText
              type="text"
              v-model="entity_form.name"
              placeholder="Name"
              :disabled="submitting"
              required
            />
          </div>
          <div class="col">
            <InputText
              v-model="entity_form.unit"
              placeholder="Unit (cm, kg, etc.)"
              :disabled="submitting"
            />
          </div>
          <div class="col">
            <InputNumber
              v-model="entity_form.stock"
              placeholder="Stock"
              :disabled="submitting"
              required
            />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col">
            <Textarea v-model="entity_form.description" class="w-100" />
          </div>
        </div>

        <div class="row mt-4">
          <div class="col">
            <input
              class="form-control"
              type="file"
              multiple
              ref="images"
              @change="new_images = $event.target.files"
            />
          </div>
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
    <Dialog
      v-model:visible="update_stock_visible"
      :modal="true"
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>Update Stock</h3>
      </template>
      <form @submit.prevent="update_stock">
        <div class="row justify-content-center">
          <div class="col-1">
            <Button @click="add_stock(-100)">-100</Button>
          </div>
          <div class="col-1">
            <Button @click="add_stock(-10)">-10</Button>
          </div>
          <div class="col-1">
            <Button @click="add_stock(-1)">-1</Button>
          </div>

          <div class="col-2">
            <InputNumber
              v-model="to_update_stock.new_stock"
              placeholder="Stock"
              :disabled="submitting"
              required
              class="w-100"
            />
          </div>

          <div class="col-1">
            <Button @click="add_stock(1)">+1</Button>
          </div>
          <div class="col-1">
            <Button @click="add_stock(10)">+10</Button>
          </div>
          <div class="col-1">
            <Button @click="add_stock(100)">+100</Button>
          </div>
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
import Textarea from "primevue/textarea";
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
    Textarea,
    Button,
  },
  data() {
    return {
      resources: [],
      resources: [],
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
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
      },
      loading: false,

      params: {},
      totalRecords: 0,

      entity_form_visible: false,
      entity_form: {
        name: "",
        unit: null,
        description: "",
        stock: null,
      },
      new_images: [],
      submitting: false,

      update_stock_index: null,
      update_stock_visible: false,
    };
  },
  computed: {
    to_update_stock() {
      if (this.update_stock_index === null) return {};
      return this.resources[this.update_stock_index];
    },
  },
  methods: {
    async submit() {
      try {
        this.submitting = true;
        const form_data = new FormData();
        form_data.append("name", this.entity_form.name);
        form_data.append("price", this.entity_form.price);
        form_data.append("description", this.entity_form.description);
        form_data.append("stock", this.entity_form.stock);
        for (let image of this.new_images) {
          form_data.append("images[]", image);
        }
        const response = await axios.post(`/api/resources`, form_data, {
          headers: {
            "content-type": "multipart/form-data",
          },
        });
        const id = response.data.id;
        this.search();
      } catch (e) {
        console.error(e);
      } finally {
        this.submitting = false;
      }
    },
    // DataTable

    on_page(event) {
      this.params = event;
    },

    show_update_stock(index) {
      this.update_stock_index = index;
      this.resources[index].new_stock = this.resources[index].stock;
      this.update_stock_visible = true;
    },

    add_stock(stock) {
      this.resources[this.update_stock_index].new_stock += stock;
    },

    async update_stock() {
      try {
        const response = await axios.post(
          `/api/resources/${this.to_update_stock.id}/update-stock/${this.to_update_stock.new_stock}`
        );
        this.update_stock_index = null;
        this.update_stock_visible = false;
        this.search();
      } catch (e) {
        console.log(e);
      }
    },

    async search() {
      this.loading = true;
      const response = await axios.get("/api/resources", {
        params: this.params,
      });
      this.resources = response.data.data;
      this.totalRecords = response.data.count;
      this.loading = false;
    },
    async get_resources() {
      const response = await axios.get("/api/resources");
      this.resources = response.data.data;
    },

    increment_resources() {
      this.entity_form.resources.push({});
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
    this.search();
    this.get_resources();
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