<template>
  <app-layout title="Reports">
    <template #header>
      <h2 class="h4 font-weight-bold">Reports</h2>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title">Reports</h5>

              <div class="row">
                <div class="col">
                  <Calendar
                    v-model="start_date"
                    class="w-100"
                    placeholder="Start Date"
                  />
                </div>
                <div class="col">
                  <Calendar
                    v-model="end_date"
                    class="w-100"
                    placeholder="End Date"
                  />
                </div>
              </div>

              <div class="row">
                <div class="col">
                  <LineChart :chartData="data" :options="options" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div class="card-body bg-white px-5 py-3 border-bottom rounded-top">
              <h5 class="card-title mb-2">Products most sold</h5>
              <div
                class="row"
                v-for="(product, index) in products"
                :key="`product-${index}`"
              >
                <template v-if="index === 0">
                  <div class="col-6">
                    <h3>1. {{ product.name }} - x{{ product.sold }}</h3>
                  </div>
                </template>
                <template v-if="index === 1">
                  <div class="col-6">
                    <h4>2. {{ product.name }} - x{{ product.sold }}</h4>
                  </div>
                </template>
                <template v-if="index === 2">
                  <div class="col-6">
                    <h5>3. {{ product.name }} - x{{ product.sold }}</h5>
                  </div>
                </template>
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
import Calendar from "primevue/calendar";
import { LineChart } from "vue-chart-3";
import Chart from "chart.js/auto";
import Color from "color";

export default defineComponent({
  components: {
    AppLayout,
    DataTable,
    Column,
    Calendar,
    LineChart,
  },
  data() {
    return {
      start_date: null,
      end_date: null,

      products: [],

      data: {
        labels: [],
        datasets: [
          {
            label: "Sales",
            data: [
              10000, 20000, 30000, 40000, 50000, 60000, 70000, 80000, 90000,
              100000, 110000, 120000,
            ],
            borderColor: Color({ h: 0, s: 75, l: 75 }).hex(),
            backgroundColor: Color({ h: 0, s: 75, l: 75 }).alpha(0.5).hex(),
          },
        ],
      },
      options: {},
    };
  },
  methods: {
    async get_data() {
      const start_date = this.datetimeToLocal(this.start_date, "yyyy-MM-dd");
      const end_date = this.datetimeToLocal(this.end_date, "yyyy-MM-dd");
      console.log(start_date, end_date);
      const response = await axios.get("/api/sales/report", {
        params: { start_date, end_date },
      });
      this.data.labels = response.data.labels;
      this.data.datasets[0].data = response.data.data;
    },
    async get_most_sold() {
      const response = await axios.get("/api/products/most-sold");
      this.products = response.data.data;
    },
  },
  watch: {
    start_date() {
      this.get_data();
    },
    end_date() {
      this.get_data();
    },
  },
  mounted() {
    this.end_date = new Date();
    const start_date = new Date();
    start_date.setMonth(start_date.getMonth() - 11);
    this.start_date = start_date;
    this.get_data();
    this.get_most_sold();
  },
});
</script>
