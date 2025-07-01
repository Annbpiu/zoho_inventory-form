<template>
    <div class="container mx-auto p-6 max-w-7xl bg-white text-gray-900 rounded shadow text-sm">
        <h1 class="text-2xl font-semibold mb-6">New Sales Order</h1>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">New Order</h1>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        v-model="orderType"
                        value="sales"
                        class="form-radio h-5 w-5 text-blue-600"
                    >
                    <span class="ml-2">Sales Order</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        type="radio"
                        v-model="orderType"
                        value="purchase"
                        class="form-radio h-5 w-5 text-blue-600"
                    >
                    <span class="ml-2">Purchase Order</span>
                </label>
            </div>
        </div>
        <!-- Customer & Order Info -->
        <CustomerVendorDetails
            :availableContacts="availableContacts"
            :customer="customer"
            :orderType="orderType"
            :errors="errors"
            :show-new-customer-modal="showNewCustomerModal"
            :order="order"
            :paymentTerms="paymentTerms"
            :deliveryMethods="deliveryMethods"
            :sales-order-number="salesOrderNumber"
            :sameAsBilling="sameAsBilling"
            :submitNewCustomer="submitNewCustomer"
            @update-customer="onCustomerChange"
            @toggle-new-customer-modal="showNewCustomerModal = $event"
        />

        <div class="mb-8">
            <h2 class="text-lg font-semibold mb-3 border-b pb-2">Item Table</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Item Details</th>
                        <th class="border p-2 text-right">Quantity</th>
                        <th class="border p-2 text-right">Rate</th>
                        <th class="border p-2 text-right">Tax</th>
                        <th class="border p-2 text-right">Amount</th>
                        <th class="border p-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, idx) in items" :key="item.id">
                        <td class="border p-2">
                            <select v-model="item.itemId" class="input w-full" @change="onItemChange(idx)">
                                <option value="">Select Item</option>
                                <option v-for="prod in availableItems" :key="prod.item_id" :value="prod.item_id">
                                    {{ prod.name }}
                                </option>
                            </select>
                            <input v-model="item.description" class="input mt-1 w-full" placeholder="Description" />
                        </td>
                        <td class="border p-2 text-right">
                            <input v-model.number="item.quantity" type="number" class="input w-full text-right" min="1" @input="calculateItemAmount(idx)" />
                        </td>
                        <td class="border p-2 text-right">
                            <input v-model.number="item.rate" type="number" class="input w-full text-right" min="0" @input="calculateItemAmount(idx)" />
                        </td>
                        <td class="border p-2 text-right">
                            <select v-model="item.taxId" class="input w-full" @change="calculateItemAmount(idx)">
                                <option value="">Select a Tax</option>
                                <option v-for="tax in availableTaxes" :key="tax.tax_id" :value="tax.tax_id">
                                    {{ tax.tax_name }} ({{ tax.percentage }}%)
                                </option>
                            </select>
                        </td>
                        <td class="border p-2 text-right font-medium">
                            {{ formatCurrency(item.amount) }}
                        </td>
                        <td class="border p-2 text-center">
                            <button @click="removeItem(idx)" class="text-red-600 font-bold">✕</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex space-x-4 mt-3">
                <button @click="addItem" class="btn-submit">+ Add New Row</button>
<!--                <button class="btn-cancel">Add Items in Bulk</button>-->
            </div>
        </div>

        <!-- Summary + Notes/Terms -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-4">
                <div>
                    <label class="block font-medium mb-1">Customer Notes</label>
                    <textarea v-model="order.customerNotes" rows="4" class="input w-full" placeholder="Any notes to be displayed in your transaction"></textarea>
                </div>
                <div>
                    <label class="block font-medium mb-1">Terms & Conditions</label>
                    <textarea v-model="order.termsConditions" rows="4" class="input w-full" placeholder="Terms and conditions of your business"></textarea>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded border space-y-3 max-w-md ml-auto">
                <div class="flex justify-between">
                    <span>Sub Total</span>
                    <span>{{ formatCurrency(order.subtotal) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Discount</span>
                    <div class="flex items-center space-x-2">
                        <input v-model.number="order.discountPercent" type="number" min="0" max="100" class="input w-16" @input="recalculateTotals" />%
                        <span>{{ formatCurrency(order.discountAmount) }}</span>
                    </div>
                </div>
                <div class="flex justify-between">
                    <span>Shipping Charges</span>
                    <input v-model.number="order.shippingCharges" type="number" class="input w-28 text-right" @input="recalculateTotals" />
                </div>
                <div class="flex justify-between">
                    <span>Adjustment</span>
                    <input v-model.number="order.adjustment" type="number" class="input w-28 text-right" @input="recalculateTotals" />
                </div>
                <div class="border-t pt-2 font-bold text-lg flex justify-between">
                    <span>Total (UAH)</span>
                    <span>{{ formatCurrency(order.total) }}</span>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-3">
            <button @click="resetForm" type="button" class="btn-cancel">Cancel</button>
<!--            <button @click="submitForm('draft')" type="button" class="btn-cancel">Save as Draft</button>-->
            <button type="button" @click="submitForm('confirmed')" :disabled="isSubmitting" class="btn-submit">
                {{ isSubmitting ? 'Processing...' : 'Save and Send' }}
            </button>
        </div>

        <!-- Messages -->
        <div v-if="successMessage" class="mt-4 p-4 bg-green-100 text-green-700 rounded">{{ successMessage }}</div>
        <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 text-red-700 rounded">{{ errorMessage }}</div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'

import CustomerVendorDetails from "@/components/CustomerVendorDetails.vue";


const salesOrderNumber = ref('SO-00014')
const orderType = ref('sales')

const customer = reactive({
    id: '',
    name: '',
})

const newCustomer = reactive({
    salutation: '',
    first_name: '',
    last_name: '',
    display_name: '',
    email: '',
    phone: '',
    company_name: '',
    billing_address: '',
})


const order = reactive({
    referenceNumber: '',
    orderDate: new Date().toISOString().split('T')[0],
    contact_id: customer.id,
    expectedShipmentDate: '',
    paymentTerm: '',
    deliveryMethod: '',
    customerNotes: '',
    subtotal: 0,
    discountPercent: 0,
    discountAmount: 0,
    shippingCharges: 0,
    adjustment: 0,
    total: 0,
    termsConditions: '',
    salesSpecificField: '',
    purchaseSpecificField: '',
})

const items = reactive([])

const selectedCustomer = ref(null)
const sameAsBilling = ref(false)

const availableItems = ref([])
const availableContacts = ref([])
const availableTaxes = ref([])
const paymentTerms = ref([])
const deliveryMethods = ref([])

const showNewCustomerModal = ref(false)

const errors = reactive({})
const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const files = ref([])

async function fetchContactOrVendors() {
    try {
        const isPurchase = orderType.value === 'purchase';
        const url = isPurchase ? '/api/inventory/vendors' : '/api/inventory/contacts';
        const response = await fetch(url);

        if (!response.ok) {
            console.error('Failed to load contacts/vendors:', response.statusText);
            availableContacts.value = [];
            return;
        }

        const data = await response.json();

        availableContacts.value = isPurchase ? data.vendors : data;

    } catch (error) {
        errorMessage.value = error.message || 'Unknown error while fetching contacts/vendors';
        console.error(error);
        availableContacts.value = [];
    }
}


async function fetchLookups() {
    try {
        const [itemsRes, taxesRes, paymentTermsRes, deliveryMethodsRes] = await Promise.all([
            fetch('/api/inventory/items'),
            fetch('/api/inventory/taxes'),
        ])

        if (!itemsRes.ok) {
            console.error('Failed to load items:', itemsRes.statusText)
            availableItems.value = []
        } else {
            availableItems.value = await itemsRes.json()
        }

        if (!taxesRes.ok) {
            console.error('Failed to load taxes:', taxesRes.statusText)
            availableTaxes.value = []
        } else {
            availableTaxes.value = await taxesRes.json()
        }
    } catch (error) {
        errorMessage.value = error.message || 'Unknown error while fetching lookups'
        console.error(error)
    }
}

function addItem() {
    items.push({
        id: Date.now(),
        itemId: '',
        description: '',
        purchase_account_id: null,
        quantity: 1,
        rate: 0,
        taxId: '',
        amount: 0,
    })
}

function removeItem(index) {
    items.splice(index, 1)
    recalculateTotals()
}

function onCustomerChange() {
    const selectedContact = availableContacts.value.find(c => c.contact_id === customer.id);
    if (selectedContact) {
        customer.name = selectedContact.contact_name;
        order.contact_id = customer.id;

        if (!order.referenceNumber) {
            order.referenceNumber = `${selectedContact.contact_id}`;
        }
    }
}

function onItemChange(index) {
    const item = items[index]
    const selected = availableItems.value.find(p => p.item_id === item.itemId)
    if (selected) {
        item.rate = selected.rate || 0
        item.description = selected.description || ''
    } else {
        item.rate = 0
        item.description = ''
    }
    calculateItemAmount(index)
}

function calculateItemAmount(index) {
    const item = items[index]
    const qty = item.quantity || 0
    const rate = item.rate || 0
    const tax = availableTaxes.value.find(t => t.tax_id === item.taxId)
    const taxPct = tax ? tax.percentage : 0
    const amountBeforeTax = qty * rate
    const taxAmount = (amountBeforeTax * taxPct) / 100
    item.amount = amountBeforeTax + taxAmount
    recalculateTotals()
}

function recalculateTotals() {
    order.subtotal = items.reduce((sum, i) => sum + (i.quantity * i.rate || 0), 0)

    order.discountAmount = (order.subtotal * (order.discountPercent || 0)) / 100

    order.total = order.subtotal - order.discountAmount + (order.shippingCharges || 0) + (order.adjustment || 0)
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'UAH' }).format(value || 0)
}

function removeFile(index) {
    files.value.splice(index, 1)
}

function validateForm() {
    let valid = true
    errors.value = {}

    console.log(customer)

    if (!customer.id) {
        errors.customerName = 'Customer is required'
        valid = false
    }

    items.forEach((item, index) => {
        const itemErrors = {}

        if (!item.itemId) {
            itemErrors.itemId = 'Item selection is required'
            valid = false
        }

        if (!item.quantity || item.quantity <= 0) {
            itemErrors.quantity = 'Valid quantity is required'
            valid = false
        }

        if (Object.keys(itemErrors).length > 0) {
            errors.value[`items.${index}`] = itemErrors
        }
    })

    return valid
}

async function ensureItemHasPurchaseInfo(itemId, purchase_account_id) {
    try {
        await fetch(`/api/inventory/items/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                item_type: 'sales_and_purchases',
                purchase_account_id: purchase_account_id,
            }),
        });
    } catch (error) {
        console.error(`Помилка при оновленні item ${itemId}:`, error);
        throw new Error(`Не вдалося оновити item ${itemId} для закупівлі`);
    }
}


async function submitForm(status = 'confirmed') {
    if (!validateForm()) return;

    const selectedContact = availableContacts.value.find(c => c.contact_id === customer.id);
    customer.name = selectedContact?.contact_name || '';

    isSubmitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        let payload = {};

        if (orderType.value === 'sales') {
            payload = {
                type: 'sales',
                sales_specific_field: order.salesSpecificField,
                customer_id: customer.id,
                date: formatDateForZoho(order.orderDate),
                shipment_date: formatDateForZoho(order.expectedShipmentDate),
                reference_number: order.referenceNumber,
                line_items: items.map(item => ({
                    item_id: item.itemId,
                    name: availableItems.value.find(i => i.item_id === item.itemId)?.name || '',
                    description: item.description || '',
                    rate: item.rate,
                    quantity: item.quantity,
                    tax_id: item.taxId || '',
                    item_total: (item.quantity * item.rate).toFixed(2),
                    unit: "qty"
                })),
                notes: order.customerNotes || '',
                terms: order.termsConditions || '',
                discount: order.discountPercent > 0 ? `${order.discountPercent}%` : "0%",
                shipping_charge: order.shippingCharges,
                adjustment: order.adjustment,
            };
        } else if (orderType.value === 'purchase') {
            for (const item of items) {
                console.log(item);
                await ensureItemHasPurchaseInfo(item.itemId, item.purchase_account_id);
            }


            payload = {
                type: 'purchase',
                purchase_specific_field: order.purchaseSpecificField,
                vendor_id: customer.id,
                date: formatDateForZoho(order.orderDate),
                shipment_date: formatDateForZoho(order.expectedShipmentDate),
                reference_number: order.referenceNumber,
                line_items: items.map(item => {
                    const matchedItem = availableItems.value.find(i => i.item_id === item.itemId);

                    return {
                        item_id: item.itemId,
                        name: matchedItem?.name || '',
                        description: item.description || '',
                        rate: item.rate,
                        quantity: item.quantity,
                        tax_id: item.taxId || '',
                        item_total: (item.quantity * item.rate).toFixed(2),
                        unit: "qty",
                    };
                }),
                notes: order.customerNotes || '',
                terms: order.termsConditions || '',
                discount: order.discountPercent > 0 ? `${order.discountPercent}%` : "0%",
                shipping_charge: order.shippingCharges,
                adjustment: order.adjustment,
            };
        }

        const response = await fetch(orderType.value === 'sales' ? '/api/inventory/create-sales-order' : '/api/inventory/create-purchase-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const responseData = await response.json();

        if (!response.ok) {
            const apiError = responseData?.message || JSON.stringify(responseData);
            throw new Error(apiError);
        }

        successMessage.value = `${orderType.value === 'sales' ? 'Sales order' : 'Purchase order'} created successfully!`;

        if (responseData.salesorder?.salesorder_number || responseData.purchaseorder?.purchaseorder_number) {
            salesOrderNumber.value = responseData.salesorder?.salesorder_number || responseData.purchaseorder?.purchaseorder_number;
        }

        resetForm();
    } catch (error) {
        console.error('Error creating order:', error);
        errorMessage.value = error.message.includes('Error')
            ? error.message
            : `Error creating ${orderType.value} order. Please check console for details.`;
    } finally {
        isSubmitting.value = false;
    }
}

function formatDateForZoho(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

async function submitNewCustomer(newCustomer) {
    console.log('Received customer:', newCustomer);

    if (!newCustomer.last_name?.trim() || !newCustomer.display_name?.trim()) {
        alert('Last name and display name are required');
        return;
    }

    try {
        const res = await fetch('/api/inventory/create-contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                contact_name: newCustomer.display_name,
                company_name: newCustomer.company_name,
                email: newCustomer.email,
                phone: newCustomer.phone,
                contact_type: 'customer',
                billing_address: {
                    address: newCustomer.billing_address,
                    city: newCustomer.billing_city || '',
                    country: 'UA',
                    zip: newCustomer.billing_zip || '',
                },
                contact_persons: [
                    {
                        salutation: newCustomer.salutation,
                        first_name: newCustomer.first_name,
                        last_name: newCustomer.last_name,
                        email: newCustomer.email,
                        phone: newCustomer.phone,
                        is_primary_contact: true
                    }
                ]
            }),
        });

        const responseData = await res.json();

        if (!res.ok) {
            console.error('Zoho API Error:', responseData);
            throw new Error(responseData.message || 'Error creating contact');
        }

        availableContacts.value.push(responseData);
        customer.id = responseData.contact_id;
        customer.name = responseData.contact_name;
        order.contact_id = responseData.contact_id;
        showNewCustomerModal.value = false;

        Object.keys(newCustomer).forEach(k => newCustomer[k] = '');
    } catch (err) {
        console.error('Error creating contact:', err);
        alert('Failed to create customer: ' + err.message);
    }
}

function resetForm() {
    customer.name = ''
    order.referenceNumber = ''
    order.orderDate = new Date().toISOString().split('T')[0]
    order.expectedShipmentDate = ''
    order.paymentTerm = ''
    order.deliveryMethod = ''
    order.customerNotes = ''
    order.subtotal = 0
    order.discountPercent = 0
    order.discountAmount = 0
    order.shippingCharges = 0
    order.adjustment = 0
    order.total = 0
    order.termsConditions = ''
    items.splice(0, items.length)
    files.value.splice(0, files.value.length)
    addItem()
    errors.customerName = ''
    Object.keys(errors).forEach(k => { errors[k] = '' })
}

watch(orderType, () => {
    fetchContactOrVendors()
})

fetchContactOrVendors()

onMounted(() => {
    fetchLookups()
    addItem()
})
</script>
