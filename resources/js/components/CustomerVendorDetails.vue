<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Customer -->
        <div class="mb-4">
            <label class="block font-medium text-gray-700 mb-1">
                {{ labelText }}
                <span class="text-red-500">*</span>
            </label>

            <div class="relative">
                <select
                    v-model="customer.id"
                    class="input w-full pr-8 appearance-none"
                    :class="{ 'border-red-500': errors.customerName }"
                    @change="onCustomerChange"
                >
                    <option disabled value="">Select a customer</option>
                    <option
                        v-for="contact in availableContacts"
                        :key="contact.contact_id"
                        :value="contact.contact_id"
                        class="py-2"
                    >
                        {{ contact.contact_name }}
                        <template v-if="contact.company_name">({{ contact.company_name }})</template>
                    </option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                    </svg>
                </div>
            </div>

            <button
                @click="showNewCustomerModal = true"
                class="w-full mt-1 text-left text-sm text-blue-600 hover:text-blue-800 flex items-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create New Customer
            </button>

            <p v-if="errors.customerName" class="text-red-600 text-xs mt-1">
                {{ errors.customerName }}
            </p>

            <div v-if="customer.id" class="mt-2 text-sm text-gray-600">
                <p v-if="selectedCustomer?.email">Email: {{ selectedCustomer.email }}</p>
                <p v-if="selectedCustomer?.phone">Phone: {{ selectedCustomer.phone }}</p>
                <p v-if="selectedCustomer?.billing_address?.address">
                    Address: {{ selectedCustomer.billing_address.address }}
                </p>
            </div>
        </div>

        <!-- New Customer -->
        <div v-if="showNewCustomerModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-3xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">New Customer</h2>
                    <button @click="showNewCustomerModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="col-span-1">
                        <h3 class="font-medium text-gray-700 mb-3 border-b pb-2">Basic Information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-3">
                                <select v-model="newCustomer.salutation" class="input">
                                    <option value="">Title</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Dr">Dr</option>
                                </select>
                                <input v-model="newCustomer.first_name" placeholder="First Name" class="input col-span-2" />
                            </div>
                            <input v-model="newCustomer.last_name" placeholder="Last Name *" class="input" required />
                            <input v-model="newCustomer.display_name" placeholder="Display Name *" class="input" required />
                            <input v-model="newCustomer.company_name" placeholder="Company Name" class="input" />
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="col-span-1">
                        <h3 class="font-medium text-gray-700 mb-3 border-b pb-2">Contact Information</h3>
                        <div class="space-y-4">
                            <input v-model="newCustomer.email" placeholder="Email" type="email" class="input" />
                            <input v-model="newCustomer.phone" placeholder="Phone" class="input" />
                            <input v-model="newCustomer.mobile" placeholder="Mobile" class="input" />
                            <input v-model="newCustomer.website" placeholder="Website" class="input" />
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-span-2">
                        <h3 class="font-medium text-gray-700 mb-3 border-b pb-2">Address</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Billing Address</h4>
                                <textarea v-model="newCustomer.billing_address" placeholder="Address" class="input h-20"></textarea>
                                <div class="grid grid-cols-3 gap-3 mt-2">
                                    <input v-model="newCustomer.billing_city" placeholder="City" class="input" />
                                    <input v-model="newCustomer.billing_state" placeholder="State/Region" class="input" />
                                    <input v-model="newCustomer.billing_zip" placeholder="Postal Code" class="input" />
                                </div>
                                <select v-model="newCustomer.billing_country" class="input mt-2">
                                    <option value="UA">Ukraine</option>
                                    <option value="PL">Poland</option>
                                    <option value="DE">Germany</option>
                                </select>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 mb-2">Shipping Address</h4>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" v-model="sameAsBilling" class="form-checkbox">
                                    <span class="ml-2">Same as billing address</span>
                                </label>
                                <textarea v-model="newCustomer.shipping_address" placeholder="Address" class="input h-20 mt-2" :disabled="sameAsBilling"></textarea>
                                <div class="grid grid-cols-3 gap-3 mt-2">
                                    <input v-model="newCustomer.shipping_city" placeholder="City" class="input" :disabled="sameAsBilling" />
                                    <input v-model="newCustomer.shipping_state" placeholder="State/Region" class="input" :disabled="sameAsBilling" />
                                    <input v-model="newCustomer.shipping_zip" placeholder="Postal Code" class="input" :disabled="sameAsBilling" />
                                </div>
                                <select v-model="newCustomer.shipping_country" class="input mt-2" :disabled="sameAsBilling">
                                    <option value="UA">Ukraine</option>
                                    <option value="PL">Poland</option>
                                    <option value="DE">Germany</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="alertMessage"
                    :class="{
                        'bg-green-100 border-green-400 text-green-700': alertType === 'success',
                        'bg-red-100 border-red-400 text-red-700': alertType === 'error',
                    }"
                    class="border px-4 py-3 rounded relative mb-4"
                    role="alert"
                >
                    <span class="block sm:inline">{{ alertMessage }}</span>
                </div>


                <div class="flex justify-end mt-6 space-x-3">
                    <button class="btn-cancel" @click="showNewCustomerModal = false">Cancel</button>
                    <button class="btn-submit" @click="handleSubmit">Save</button>
                </div>
            </div>
        </div>

        <!-- Sales Order# -->
        <div>
            <label class="block font-medium mb-1">Sales Order#</label>
            <input type="text" class="input bg-gray-100" :value="salesOrderNumber" readonly />
        </div>

        <!-- Reference# -->
        <div>
            <label class="block font-medium mb-1">Reference#</label>
            <input type="text" class="input" :value="customer.id" disabled />
        </div>


        <!-- Sales Order Date -->
        <div>
            <label class="block font-medium mb-1">Sales Order Date</label>
            <input v-model="order.orderDate" type="date" class="input" />
        </div>

        <!-- Expected Shipment -->
        <div>
            <label class="block font-medium mb-1">Expected Shipment Date</label>
            <input v-model="order.expectedShipmentDate" type="date" class="input" />
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed } from 'vue';

export default {
    props: {
        customer: {
            type: Object,
            required: true,
        },
        orderType: {
            type: String,
            required: true,
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
        availableContacts: {
            type: Array,
            required: true,
        },
        salesOrderNumber: {
            type: String,
            default: '',
        },
        paymentTerms: {
            type: Array,
            required: true,
        },
        deliveryMethods: {
            type: Array,
            required: true,
        },
        order: {
            type: Object,
            required: true,
        },
        sameAsBilling: {
            type: Boolean,
            required: true,
        },
        submitNewCustomer: {
            type: Function,
            required: true,
        },
    },
    methods: {
        async handleSubmit() {
            await this.submitNewCustomer({ ...this.newCustomer });

            this.showAlert('Customer successfully created!', 'success');
        },
    },
    emits: ['update-customer', 'toggle-new-customer-modal'],
    setup(props, { emit }) {
        const showNewCustomerModal = ref(false);

        const labelText = computed(() => {
            return props.orderType === 'purchase' ? 'Vendor Name' : 'Customer Name';
        });

        const newCustomer = reactive({
            salutation: '',
            first_name: '',
            last_name: '',
            display_name: '',
            email: '',
            phone: '',
            company_name: '',
            billing_address: '',
        });

        const selectedCustomer = computed(() =>
            props.availableContacts.find(contact => contact.contact_id === props.customer.id)
        );

        const onCustomerChange = (value) => {
            emit('update-customer', value);
        };

        return {
            labelText,
            sameAsBilling: false,
            showNewCustomerModal,
            newCustomer,
            selectedCustomer,
            onCustomerChange,
        };
    },
};
</script>
