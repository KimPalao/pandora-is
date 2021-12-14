<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Audit</h2>
        </div>
      </div>
    </template>

    <div class="container my-5">
      <div class="row justify-content-center my-5">
        <div class="col-md-12">
          <div class="card shadow bg-light">
            <div
              class="
                card-body
                bg-white
                px-5
                py-3
                border-bottom
                rounded-top
                py-5
              "
            >
              <div class="row">
                <div class="col">
                  <Dropdown
                    v-model="selected_site"
                    :options="sites"
                    optionValue="id"
                    optionLabel="name"
                    placeholder="Select site"
                    class="w-100"
                    @change="get_bags"
                  />
                </div>
              </div>

              <div class="row mt-5">
                <div class="col">
                  <Textarea
                    v-model="site_selected_bags_text"
                    disabled
                    rows="5"
                    class="w-100"
                  />
                </div>
                <div class="col">
                  <Textarea v-model="on_site_barcodes" rows="5" class="w-100" />
                </div>
              </div>

              <div class="row mt-5" v-show="show_results">
                <div class="col">
                  <strong>Expected, but not found:</strong>
                  <ul>
                    <li
                      v-for="bag in expected_bags"
                      :key="`expected-bag-${bag.barcode}`"
                    >
                      {{ bag.barcode }} ({{ bag.name }})
                    </li>
                  </ul>
                </div>
                <div class="col">
                  <strong>Not expected, but found:</strong>
                  <ul>
                    <li
                      v-for="barcode in unexpected_bags"
                      :key="`unexpected-bag-${barcode}`"
                    >
                      {{ barcode }}
                    </li>
                  </ul>
                </div>
              </div>

              <div class="row mt-5">
                <div class="col d-grid">
                  <button
                    class="btn btn-primary text-white"
                    @click="show_results = true"
                  >
                    Audit
                  </button>
                </div>
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
import Carousel from "primevue/carousel";
import Dropdown from "primevue/dropdown";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Calendar from "primevue/calendar";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";

export default defineComponent({
  components: {
    AppLayout,
    Carousel,
    Dropdown,
    Dialog,
    Button,
    Calendar,
    InputNumber,
    Textarea,
  },
  props: {
    bag_id: Number,
  },
  data() {
    return {
      sites: [],
      selected_site: 0,

      site_selected_bags: [],
      site_selected_bags_map: {},
      show_results: false,
      on_site_barcodes: "",
    };
  },
  computed: {
    site_selected_bags_text() {
      return this.site_selected_bags.map((bag) => bag.barcode).join("\n");
    },
    on_site_barcodes_map() {
      const map = {};
      const bags = this.on_site_barcodes.split("\n");
      for (let bag of bags) {
        if (bag.trim() === "") continue;
        map[bag] = "";
      }
      return map;
    },
    expected_bags() {
      const bags = [];
      for (let barcode in this.site_selected_bags_map) {
        console.log("expected_bags", barcode);
        if (!(barcode in this.on_site_barcodes_map)) {
          bags.push(this.site_selected_bags_map[barcode]);
        }
      }
      return bags;
    },
    unexpected_bags() {
      const bags = [];
      for (let barcode in this.on_site_barcodes_map) {
        console.log("unexpected_bags", barcode);
        if (!(barcode in this.site_selected_bags_map)) {
          bags.push(barcode);
        }
      }
      return bags;
    },
  },
  methods: {
    async get_data() {
      let [sites] = await Promise.all([axios.get(`/api/sites`)]);
      this.sites = sites.data.data;
    },
    async get_bags() {
      const response = await axios.get(`/api/site/${this.selected_site}/bags`);
      this.site_selected_bags = response.data.data;
      for (let bag of this.site_selected_bags) {
        this.site_selected_bags_map[bag.barcode] = bag;
      }
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
