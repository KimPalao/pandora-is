<template>
  <app-layout title="Inventory">
    <template #header>
      <div class="row">
        <div class="col">
          <h2 class="h4 font-weight-bold">Bag {{ bag.name || bag_id }}</h2>
        </div>
        <div class="col-auto">
          <button
            :disabled="bag.is_sold"
            class="btn btn-info"
            @click="movement_form_visible = true"
            :title="
              bag.is_sold ? `Bag has already been sold.` : `Update bag activity`
            "
          >
            Update
          </button>
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
              <h5 v-if="bag.is_sold">
                This bag was sold
                <span v-if="bag.sale"
                  >for {{ renderCurrency(bag.sale.price) }}</span
                >
              </h5>
              <h5 v-else>
                This bag is at {{ bag.latest_movement?.to_site.name }} since
                {{ bag.latest_movement?.datetime }}
              </h5>
              <h4>Images</h4>
              <div class="row mb-5">
                <div class="col-4" v-for="image in bag.images" :key="image.id">
                  <img
                    :src="`/${image.file_name}`"
                    class="img-thumbnail"
                    alt="..."
                  />
                </div>
              </div>
              <h4>Log</h4>
              <p v-for="m in movement" :key="`movement-${m.id}`">
                <span>{{ m.datetime }}</span> |
                <span v-if="m.from_site">{{ m.from_site.name }}</span>
                <span v-else>Obtained</span>
                -
                <span v-if="m.to_site">{{ m.to_site.name }}</span>
                <span v-else>Sold</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Dialog
      v-model:visible="movement_form_visible"
      :modal="true"
      :breakpoints="{ '2000px': '75vw', '640px': '100vw' }"
    >
      <template #header>
        <h3>Update Activity</h3>
      </template>
      <form @submit.prevent="update_bag_movement">
        <div class="row">
          <div class="col-4">
            <Dropdown
              v-model="movement_form.to"
              :options="site_options"
              optionValue="id"
              optionLabel="name"
              :optionDisabled="
                (option) => {
                  return option.id === last_site;
                }
              "
              placeholder="New Location"
              class="w-100"
              :disabled="submitting"
            />
          </div>
          <div class="col-4">
            <Calendar
              v-model="movement_form.datetime"
              :showTime="true"
              class="w-100"
              placeholder="Time"
              :minDate="minimum_movement_time"
              :maxDate="new Date()"
              :disabled="submitting"
            />
          </div>
          <div class="col-4" v-show="movement_form.to === 0">
            <InputNumber
              v-model="movement_form.price"
              mode="currency"
              currency="PHP"
              locale="en-US"
              placeholder="Sale Price"
              :disabled="submitting"
            />
          </div>
        </div>
        <div class="row">
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
import Carousel from "primevue/carousel";
import Dropdown from "primevue/dropdown";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Calendar from "primevue/calendar";
import InputNumber from "primevue/inputnumber";
import { DateTime } from "luxon";

export default defineComponent({
  components: {
    AppLayout,
    Carousel,
    Dropdown,
    Dialog,
    Button,
    Calendar,
    InputNumber,
  },
  props: {
    bag_id: Number,
  },
  data() {
    return {
      bag: {},
      movement: [],
      sites: [],

      submitting: false,
      movement_form_visible: false,
      movement_form: {
        to: null,
        datetime: null,
        price: 0,
      },
    };
  },
  computed: {
    site_options() {
      return [{ id: 0, name: "Sold" }].concat(this.sites);
    },
    movement_form_data() {
      const data = { ...this.movement_form };
      if (data.datetime)
        data.datetime = DateTime.fromISO(data.datetime.toISOString()).toFormat(
          "yyyy-MM-dd HH:mm:ss"
        );
      return data;
    },
    minimum_movement_time() {
      // No movement yet
      if (!this.movement.length) return new Date(0, 0, 0, 0, 0, 0);
      return new Date(this.movement[this.movement.length - 1].datetime);
    },
    last_site() {
      if (!this.movement.length) return 0;
      return this.movement[this.movement.length - 1].to_site?.id;
    },
  },
  methods: {
    async get_data() {
      let [bag, movement, sites] = await Promise.all([
        axios.get(`/api/bag/${this.bag_id}`),
        axios.get(`/api/bag/movement/${this.bag_id}`),
        axios.get(`/api/sites`),
      ]);
      this.bag = bag.data.data;
      this.movement_form.price = this.bag.price;
      this.movement = movement.data.data;
      this.sites = sites.data.data;
    },
    async update_bag_movement() {
      try {
        this.submitting = true;
        const response = await axios.put(
          `/api/bag/${this.bag_id}/movement`,
          this.movement_form_data
        );
        this.get_data();
        this.movement_form_visible = false;
        this.movement_form.to = null;
        this.movement_form.datetime = null;
      } catch (e) {
      } finally {
        this.submitting = false;
      }
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
