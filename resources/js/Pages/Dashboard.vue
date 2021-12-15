<template>
  <app-layout title="Dashboard">
    <template #header>
      <h2 class="h4 font-weight-bold">Dashboard</h2>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title">Latest sales</h5>

              <DataTable :value="sales" responsiveLayout="scroll">
                <Column field="datetime" header="Date" />
                <Column field="bag.name" header="Name" />
                <Column field="price" header="Sale Price">
                  <template #body="slotProps">
                    {{ renderCurrency(slotProps.data.price) }}
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

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
  },
  data() {
    return {
      sales: [],
    };
  },
  methods: {
    async get_data() {
      const [sales] = await Promise.all([axios.get("/api/sales/recent")]);
      this.sales = sales.data.data;
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
