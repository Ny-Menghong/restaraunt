<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Customers</h1>
                <p class="page-sub">Keep track of your guests</p>
            </div>
            <button @click="showModal = true; resetForm()" class="btn btn-primary">
                <Plus class="h-4 w-4" />
                Add Customer
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="customers.length === 0" class="empty">No customers found.</div>
        <div v-else class="table-shell overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="th">Name</th>
                            <th class="th">Phone</th>
                            <th class="th">Email</th>
                            <th class="th">Address</th>
                            <th class="th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in customers" :key="customer.id"
                            class="border-b border-slate-50 transition last:border-0 hover:bg-slate-50/60">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-xs font-bold text-white">
                                        {{ customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ customer.name }}</span>
                                </div>
                            </td>
                            <td class="td">{{ customer.phone || '-' }}</td>
                            <td class="td">{{ customer.email || '-' }}</td>
                            <td class="td max-w-[200px] truncate">{{ customer.address || '-' }}</td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <button @click="editCustomer(customer)" class="btn-icon text-blue-600 hover:bg-blue-50" title="Edit">
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button @click="deleteCustomer(customer.id)" class="btn-icon text-red-500 hover:bg-red-50" title="Delete">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-backdrop">
            <div class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="modal-card relative z-50 animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">{{ editingId ? 'Edit Customer' : 'Add Customer' }}</h2>
                        <p class="text-xs text-slate-400">{{ editingId ? 'Update the customer details' : 'Register a new customer' }}</p>
                    </div>
                    <button @click="showModal = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="label">Name</label>
                        <input v-model="form.name" type="text" required class="input" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Phone</label>
                            <input v-model="form.phone" type="text" class="input" />
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input v-model="form.email" type="email" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Address</label>
                        <input v-model="form.address" type="text" class="input" />
                    </div>
                    <div>
                        <label class="label">Note</label>
                        <textarea v-model="form.note" rows="2" class="input"></textarea>
                    </div>
                    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="submitting" class="btn btn-primary flex-1">
                            <span v-if="submitting">Saving...</span>
                            <span v-else>{{ editingId ? 'Update Customer' : 'Create Customer' }}</span>
                        </button>
                        <button type="button" @click="showModal = false" class="btn btn-soft">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Plus, Pencil, Trash2, X } from 'lucide-vue-next';
import type { Customer } from '../../../models/customer';
import { customerService } from '../../../services/customers';

const customers = ref<Customer[]>([]);
const loading = ref(false);
const showModal = ref(false);
const submitting = ref(false);
const editingId = ref<number | null>(null);
const error = ref('');

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    note: '',
});

const resetForm = () => {
    form.value = { name: '', phone: '', email: '', address: '', note: '' };
    editingId.value = null;
    error.value = '';
};

const fetchCustomers = async () => {
    loading.value = true;
    try {
        const response = await customerService.fetchCustomers();
        customers.value = response.customers;
    } catch (e) {
        console.error('Failed to fetch customers:', e);
    } finally {
        loading.value = false;
    }
};

const editCustomer = (customer: Customer) => {
    editingId.value = customer.id;
    form.value = {
        name: customer.name,
        phone: customer.phone || '',
        email: customer.email || '',
        address: customer.address || '',
        note: customer.note || '',
    };
    showModal.value = true;
};

const submitForm = async () => {
    submitting.value = true;
    error.value = '';
    try {
        if (editingId.value) {
            await customerService.updateCustomer(editingId.value, form.value);
        } else {
            await customerService.createCustomer(form.value);
        }
        showModal.value = false;
        await fetchCustomers();
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Failed to save customer';
    } finally {
        submitting.value = false;
    }
};

const deleteCustomer = async (id: number) => {
    if (!confirm('Are you sure you want to delete this customer?')) return;
    try {
        await customerService.deleteCustomer(id);
        await fetchCustomers();
    } catch (e) {
        console.error('Failed to delete customer:', e);
    }
};

onMounted(() => {
    fetchCustomers();
});
</script>