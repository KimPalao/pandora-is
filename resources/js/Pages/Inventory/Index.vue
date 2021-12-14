<template>
  <app-layout title="Inventory">
    <template #header>
      <h2 class="h4 font-weight-bold">Inventory</h2>
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
    };
  },
  async mounted() {
    const [bags, sites] = await Promise.all([
      axios.get("/api/inventory"),
      axios.get("/api/sites"),
    ]);
    this.bags = bags.data.data;
    this.sites = sites.data.data;
    this.sites.push({ id: null, name: "Sold" });
  },
});
</script>
