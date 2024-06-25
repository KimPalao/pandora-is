<template>
    <app-layout title="Inventory">
        <template #header>
            <div class="row">
                <div class="col">
                    <h2 class="h4 font-weight-bold">Products</h2>
                </div>
                <div class="col-auto">
                    <div class="col-auto">
                        <button class="btn btn-info" @click="entity_form_visible = true">
                            New Product
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
                            <DataTable lazy responsiveLayout="scroll" paginator
                                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                                currentPageReportTemplate="Showing {first} to {last} of {totalRecords}"
                                filterDisplay="menu"
                                sortMode="multiple" :value="products" :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                                :loading="loading" :totalRecords="totalRecords" v-model:filters="filters"
                                @page="params = $event" @sort="params = $event" @filter="params.filters = filters">
                                <template #header>
                                    <div class="p-d-flex p-jc-between p-ai-center">
                                        <h5 class="p-m-0">Products</h5>
                                        <span class="p-input-icon-left">
                                            <i class="pi pi-search" />
                                            <InputText
                                                v-model="filters['global'].value"
                                                class="mx-2"
                                                placeholder="Keyword Search" />
                                        </span>
                                    </div>
                                </template>
                                <Column field="name" header="Name" sortable />
                                <Column field="price" header="Price" sortable>
                                    <template #body="slotProps">
                                        {{ renderCurrency(slotProps.data.price) }}
                                    </template>
                                </Column>
                                <Column field="stock" header="Stock" sortable />
                                <Column field="" header="">
                                    <template #body="slotProps">
                                        <Button @click="show_update_stock(slotProps.index)">Update Stock</Button>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:visible="entity_form_visible" :modal="true"
            :breakpoints="{ '2000px': '75vw', '640px': '100vw' }">
            <template #header>
                <h3>New Product</h3>
            </template>
            <form @submit.prevent="submit">
                <div class="row">
                    <div class="col">
                        <InputText type="text" v-model="entity_form.name" placeholder="Name" :disabled="submitting"
                            required />
                    </div>
                    <div class="col">
                        <InputNumber v-model="entity_form.price" mode="currency" currency="PHP" locale="en-US"
                            placeholder="Sale Price" :disabled="submitting" required />
                    </div>
                    <div class="col">
                        <InputNumber v-model="entity_form.stock" placeholder="Stock Amount" :disabled="submitting"
                            required />
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <Textarea v-model="entity_form.description" class="w-100" />
                    </div>
                </div>
                <div class="row mt-4 mb-2">
                    <div class="col">
                        <h5>Resources</h5>
                    </div>
                    <div class="col-auto">
                        <Button @click="increment_resources" class="me-2">+ Add Resource</Button>
                    </div>
                </div>
                <div v-for="(resource, index) in entity_form.resources" class="row mt-2" :key="index">
                    <div class="col-1">
                        <Button @click="entity_form.resources.splice(index, 1)" v-if="index !== 0">x</Button>
                    </div>
                    <div class="col-5">
                        <Dropdown v-model="resource.id" :options="resources" option-label="name" option-value="id"
                            placeholder="Select a Resource" filter class="w-100" />
                    </div>
                    <div class="col-3">
                        <InputNumber v-model="resource.quantity" class="w-100" placeholder="Quantity" />
                    </div>
                    <div class="col-2"></div>
                </div>
                <div class="row mt-4">
                    <div class="col">
                        <input class="form-control" type="file" multiple ref="images"
                            @change="new_images = $event.target.files" />
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-auto ms-auto">
                        <button class="btn btn-primary" type="submit" :disabled="submitting">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        </Dialog>
        <Dialog v-model:visible="update_stock_visible" :modal="true"
            :breakpoints="{ '2000px': '75vw', '640px': '100vw' }">
            <template #header>
                <h3>Update Stock of {{ to_update_stock.name }}</h3>
            </template>
            <form @submit.prevent="update_stock">
                <div class="grid">
                    <div class="justify-self-end">
                        <Button @click="add_stock(-100)">-100</Button>
                    </div>
                    <div class="justify-self-end">
                        <Button @click="add_stock(-10)">-10</Button>
                    </div>
                    <div class="justify-self-end">
                        <Button @click="add_stock(-1)">-1</Button>
                    </div>

                    <div class="justify-self-center">
                        <InputNumber v-model="products[update_stock_index].new_stock" @input="compute_resources_to_use"
                            placeholder="Stock" :disabled="submitting" required class="w-100" />
                    </div>

                    <div class="">
                        <Button @click="add_stock(1)">+1</Button>
                    </div>
                    <div class="">
                        <Button @click="add_stock(10)">+10</Button>
                    </div>
                    <div class="">
                        <Button @click="add_stock(100)">+100</Button>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col">
                        <h5>Resources that will be used:</h5>
                    </div>
                </div>

                <div class="row" v-for="(resource, index) in to_update_stock.resources"
                    :key="`to-update-resource-${index}`">
                    <div class="col-auto">
                        <img :src="`/api/resources/${resource.id}/image`" alt="" style="width: 25px; height: 25px" />
                    </div>
                    <div class="col-4">
                        {{ resource.name }} x {{ resource.pivot.quantity }}
                    </div>
                    <div class="col-auto">x {{ new_stock }} =</div>
                    <div class="col-auto">
                        <InputNumber v-model="products[update_stock_index].resources[index].to_use" />
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-auto ms-auto">
                        <button class="btn btn-primary" type="submit" :disabled="submitting">
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
            products: [],
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
                price: null,
                description: "",
                stock: null,
                resources: [{}],
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
            return this.products[this.update_stock_index];
        },
        new_stock() {
            return Math.max(
                this.to_update_stock.new_stock - this.to_update_stock.stock,
                0
            );
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
                for (let i = 0; i < this.entity_form.resources.length; i++) {
                    const resource = this.entity_form.resources[i];
                    console.log(resource);
                    form_data.append(`resources[${i}][id]`, resource.id);
                    form_data.append(`resources[${i}][quantity]`, resource.quantity);
                }
                for (let image of this.new_images) {
                    form_data.append("images[]", image);
                }
                const response = await axios.post(`/api/products`, form_data, {
                    headers: {
                        "content-type": "multipart/form-data",
                    },
                });
                const id = response.data.id;
                this.entity_form_visible = false;
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
            this.products[index].new_stock = this.products[index].stock;
            for (let resources of this.products[index].resources) {
                this.products[index].resources.stock_to_use = 0;
            }
            this.update_stock_visible = true;
        },

        add_stock(stock) {
            this.products[this.update_stock_index].new_stock += stock;
            this.compute_resources_to_use({ value: this.products[this.update_stock_index].new_stock });
        },

        compute_resources_to_use(event) {
            const new_stock = event.value - this.to_update_stock.stock;
            for (
                let i = 0;
                i < this.products[this.update_stock_index].resources.length;
                i++
            ) {
                if (new_stock < 0) {
                    this.products[this.update_stock_index].resources[i].to_use = 0;
                } else {
                    this.products[this.update_stock_index].resources[i].to_use =
                        new_stock *
                        this.products[this.update_stock_index].resources[i].pivot.quantity;
                }
            }
        },

        async update_stock() {
            try {
                const data = { resources: [] };
                for (let resource of this.to_update_stock.resources) {
                    data.resources.push({
                        resource_id: resource.id,
                        quantity: resource.to_use,
                    });
                }
                const response = await axios.post(
                    `/api/products/${this.to_update_stock.id}/update-stock/${this.to_update_stock.new_stock}`,
                    data
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
            const response = await axios.get("/api/products", {
                params: { ...this.params, with_resources: "true" },
            });
            this.products = response.data.data;
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
<style>
span.w-100 input {
    max-width: 100%;
}

span.w-50 input {
    max-width: 100%;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 2fr 1fr 1fr 1fr;
    column-gap: 1em;
}

.justify-self-end {
    justify-self: end;
}

.justify-self-center {
    justify-self: center;
}
</style>
