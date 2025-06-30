import { ref, reactive, onMounted } from 'vue'

export default function useNewSalesOrder() {
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

    return {
        salesOrderNumber,
        customer,
        newCustomer,
        order,
        items,
        selectedCustomer,
        sameAsBilling,
        availableItems,
        availableContacts,
        availableTaxes,
        paymentTerms,
        deliveryMethods,
        showNewCustomerModal,
        errors,
        isSubmitting,
        successMessage,
        errorMessage,
        files,
        fetchLookups,
        addItem,
        removeItem,
        onCustomerChange,
        onItemChange,
        calculateItemAmount,
        recalculateTotals,
        formatCurrency,
        formatFileSize,
        onFileChange,
        removeFile,
        validateForm,
        submitForm,
        formatDateForZoho,
        submitNewCustomer,
        resetForm
    }
}
