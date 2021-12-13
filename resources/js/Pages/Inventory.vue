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
                <Column
                  v-for="col of columns"
                  :field="col.field"
                  :header="col.header"
                  :key="col.field"
                ></Column>
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

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
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
