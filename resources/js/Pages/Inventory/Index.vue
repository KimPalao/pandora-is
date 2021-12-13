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
              <DataTable :value="bags" responsiveLayout="scroll">
                <Column field="name" header="Name">
                  <template #body="slotProps">
                    <Link
                      :href="route('bag', slotProps.data.id)"
                      :active="route().current('bag')"
                      >{{ slotProps.data.name }}</Link
                    >
                  </template></Column
                >
                <Column field="price" header="Price"></Column>
                <Column field="is_sold" header="Sold?">
                  <template #body="slotProps">
                    {{ slotProps.data.is_sold ? "Yes" : "No" }}
                  </template>
                </Column>
                <Column field="latest_movement" header="Location">
                  <template #body="slotProps">
                    {{
                      slotProps.data.latest_movement?.to_site?.name ?? "Sold"
                    }}
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
import { Link } from "@inertiajs/inertia-vue3";

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
    Link,
  },
  data() {
    return {
      bags: [],
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
    };
  },
  async mounted() {
    const response = await axios.get("/api/inventory");
    this.bags = response.data.data;
  },
});
</script>
