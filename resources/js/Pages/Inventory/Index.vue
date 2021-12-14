<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Inventory</h2>
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
                :value="bags"
                responsiveLayout="scroll"
                :paginator="true"
                :rows="10"
                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                :rowsPerPageOptions="[10, 20, 50]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                v-model:filters="filters"
                filterDisplay="menu"
              >
                <Column field="name" header="Name">
                  <template #body="slotProps">
                    <Link
                      :href="route('bag', slotProps.data.id)"
                      :active="route().current('bag')"
                      >{{ slotProps.data.name }}</Link
                    >
                  </template></Column
                >
                <Column field="price" header="Price">
                  <template #body="slotProps">
                    {{ renderCurrency(slotProps.data.price) }}
                  </template>
                </Column>
                <Column
                  field="is_sold"
                  header="Sold?"
                  :showFilterMatchModes="false"
                >
                  <template #body="slotProps">
                    {{ slotProps.data.is_sold ? "Yes" : "No" }}
                  </template>
                  <template #filter="{ filterModel }">
                    <Dropdown
                      v-model="filterModel.value"
                      :options="[
                        { value: 1, label: 'Yes' },
                        { value: 0, label: 'No' },
                      ]"
                      optionValue="value"
                      optionLabel="label"
                      :showClear="true"
                      placeholder="Select an option"
                    />
                  </template>
                </Column>
                <Column
                  field="latest_movement"
                  header="Location"
                  filterField="latest_movement.to_site"
                  :showFilterMatchModes="false"
                  :filterMenuStyle="{ width: '14rem' }"
                >
                  <template #body="slotProps">
                    {{
                      slotProps.data.latest_movement?.to_site?.name ?? "Sold"
                    }}
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

    <Dialog
      v-model:visible="new_bag_form_visible"
      :modal="true"
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>New Bag</h3>
      </template>
      <form @submit.prevent="new_bag_submit">
        <div class="row">
          <div class="col">
            <InputText
              type="text"
              v-model="new_bag_form.name"
              placeholder="Name"
              :disabled="submitting"
              required
            />
          </div>
          <div class="col">
            <InputNumber
              v-model="new_bag_form.price"
              mode="currency"
              currency="PHP"
              locale="en-US"
              placeholder="Sale Price"
              :disabled="submitting"
              required
            />
          </div>
          <div class="col">
            <Calendar
              v-model="new_bag_form.date_obtained"
              :showTime="true"
              class="w-100"
              placeholder="Time"
              :disabled="submitting"
              required
            />
          </div>
          <div class="col">
            <Dropdown
              v-model="new_bag_form.initial_site"
              :options="sites"
              optionValue="id"
              optionLabel="name"
              placeholder="Initial Location"
              class="w-100"
              :disabled="submitting"
              required
            />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col">
            <input
              class="form-control"
              type="file"
              multiple
              ref="images"
              @change="new_bag_images = $event.target.files"
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
      bags: [],
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
        "latest_movement.to_site": {
          value: null,
          matchMode: "InOrNull",
        },
        is_sold: { value: null, matchMode: FilterMatchMode.EQUALS },
      },

      // new bag form
      new_bag_form_visible: false,
      new_bag_form: {
        name: "",
        price: null,
        date_obtained: null,
        initial_site: null,
      },
      new_bag_images: [],
      submitting: false,
    };
  },
  methods: {
    async new_bag_submit() {
      try {
        this.submitting = true;
        const form_data = new FormData();
        form_data.append("name", this.new_bag_form.name);
        form_data.append("price", this.new_bag_form.price);
        form_data.append(
          "date_obtained",
          this.datetimeToLocal(this.new_bag_form.date_obtained)
        );
        form_data.append("initial_site", this.new_bag_form.initial_site);
        for (let image of this.new_bag_images) {
          form_data.append("images[]", image);
        }
        const response = await axios.post(`/api/bag`, form_data, {
          headers: {
            "content-type": "multipart/form-data",
          },
        });
        this.get_data();
        this.movement_form_visible = false;
        this.movement_form.to = null;
        this.movement_form.datetime = null;
        const id = response.data.id;
        this.get_data();
      } catch (e) {
        console.log(e);
      } finally {
        this.submitting = false;
      }
    },
    async get_data() {
      const [bags, sites] = await Promise.all([
        axios.get("/api/inventory"),
        axios.get("/api/sites"),
      ]);
      this.bags = bags.data.data;
      this.sites = sites.data.data;
      this.sites.push({ id: null, name: "Sold" });
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>