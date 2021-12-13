<template>
  <app-layout title="Inventory">
    <template #header>
      <h2 class="h4 font-weight-bold">Bag {{ bag.name || bag_id }}</h2>
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
  </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import Carousel from "primevue/carousel";

export default defineComponent({
  components: {
    AppLayout,
    Carousel,
  },
  props: {
    bag_id: Number,
  },
  data() {
    return {
      bag: {},
      movement: [],
    };
  },
  methods: {
    async get_data() {
      let [bag, movement] = await Promise.all([
        axios.get(`/api/bag/${this.bag_id}`),
        axios.get(`/api/bag/movement/${this.bag_id}`),
      ]);
      this.bag = bag.data.data;
      this.movement = movement.data.data;
    },
  },
  mounted() {
    this.get_data();
  },
});
</script>
