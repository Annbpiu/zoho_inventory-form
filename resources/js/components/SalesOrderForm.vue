<template>
    <div class="container mx-auto p-6 max-w-7xl bg-white text-gray-900 rounded shadow text-sm">
        <h1 class="text-2xl font-semibold mb-6">New Sales Order</h1>

        <!-- Customer & Order Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Customer -->
            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-1">
                    Customer Name <span class="text-red-500">*</span>
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

                    <div class="flex justify-end mt-6 space-x-3">
                        <button class="btn-cancel" @click="showNewCustomerModal = false">Cancel</button>
                        <button class="btn-submit" @click="submitNewCustomer">Save</button>
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

            <!-- Payment Terms -->
            <div>
                <label class="block font-medium mb-1">Payment Terms</label>
                <select v-model="order.paymentTerm" class="input">
                    <option value="">Select Payment Term</option>
                    <option v-for="term in paymentTerms" :key="term.id" :value="term.id">{{ term.name }}</option>
                </select>
            </div>

            <!-- Delivery Method -->
            <div>
                <label class="block font-medium mb-1">Delivery Method</label>
                <select v-model="order.deliveryMethod" class="input">
                    <option value="">Select Delivery Method</option>
                    <option v-for="method in deliveryMethods" :key="method.id" :value="method.id">{{ method.name }}</option>
                </select>
            </div>
        </div>

        <!-- Item Table -->
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

            <!-- Add Item Buttons -->
            <div class="flex space-x-4 mt-3">
                <button @click="addItem" class="btn-submit">+ Add New Row</button>
<!--                <button class="btn-cancel">Add Items in Bulk</button>-->
            </div>
        </div>

        <!-- Summary + Notes/Terms -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Notes & Terms -->
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

            <!-- Totals -->
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
import { ref, reactive, onMounted } from 'vue'

const salesOrderNumber = ref('SO-00014')

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
})

const items = reactive([])

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

async function fetchLookups() {
    try {
        const [itemsRes, contactsRes, taxesRes, paymentTermsRes, deliveryMethodsRes] = await Promise.all([
            fetch('/api/inventory/items'),
            fetch('/api/inventory/contacts'),
            fetch('/api/inventory/taxes'),
            fetch('/api/inventory/payment-terms'),
            fetch('/api/inventory/delivery-methods'),
        ])

        if (!itemsRes.ok) {
            console.error('Failed to load items:', itemsRes.statusText)
            availableItems.value = []
        } else {
            availableItems.value = await itemsRes.json()
        }

        if (!contactsRes.ok) {
            console.error('Failed to load contacts:', contactsRes.statusText)
            availableContacts.value = []
        } else {
            availableContacts.value = await contactsRes.json()
        }

        if (!taxesRes.ok) {
            console.error('Failed to load taxes:', taxesRes.statusText)
            availableTaxes.value = []
        } else {
            availableTaxes.value = await taxesRes.json()
        }

        paymentTerms.value = paymentTermsRes.ok ? await paymentTermsRes.json() : []
        deliveryMethods.value = deliveryMethodsRes.ok ? await deliveryMethodsRes.json() : []

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

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B'
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB'
}

function onFileChange(e) {
    const selectedFiles = Array.from(e.target.files)
    for (const file of selectedFiles) {
        if (files.value.length >= 10) break
        if (file.size > 5 * 1024 * 1024) continue
        files.value.push(file)
    }
    e.target.value = null
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

async function submitForm(status = 'confirmed') {
    if (!validateForm()) return;

    const selectedContact = availableContacts.value.find(c => c.contact_id === customer.id);
    customer.name = selectedContact?.contact_name || '';

    isSubmitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const payload = {
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
            is_discount_before_tax: true,
            discount_type: "entity_level",
            custom_fields: []
        };

        const response = await fetch('/api/inventory/create-sales-order', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const responseData = await response.json();

        if (!response.ok) {
            const zohoError = responseData?.message || JSON.stringify(responseData);
            throw new Error(`Zoho API Error: ${zohoError}`);
        }

        successMessage.value = `Sales order created successfully!`;

        if (responseData.salesorder?.salesorder_number) {
            salesOrderNumber.value = responseData.salesorder.salesorder_number;
        }

        resetForm();
    } catch (error) {
        console.error('Error creating sales order:', error);
        errorMessage.value = error.message.includes('Zoho API Error')
            ? error.message
            : 'Error creating sales order. Please check console for details.';
    } finally {
        isSubmitting.value = false;
    }
}

function formatDateForZoho(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

async function submitNewCustomer() {
    if (!newCustomer.last_name || !newCustomer.display_name) {
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

onMounted(() => {
    fetchLookups()
    addItem()
})
</script>
