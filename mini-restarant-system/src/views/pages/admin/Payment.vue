<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Payments</h1>
                <p class="page-sub">View orders and their payment status</p>
            </div>
            <button @click="fetchOrders" class="btn btn-soft">
                <RefreshCw class="h-4 w-4" />
                Refresh
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <div class="relative">
                <select v-model="paymentFilter" class="input w-44 appearance-none pr-8">
                    <option value="">All Payments</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                </select>
                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
            <div class="relative">
                <select v-model="typeFilter" @change="fetchOrders" class="input w-44 appearance-none pr-8">
                    <option value="">All Types</option>
                    <option value="dine_in">Dine In</option>
                    <option value="takeaway">Takeaway</option>
                </select>
                <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Orders</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ filteredOrders.length }}</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Paid</p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-600">{{ paidCount }}</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Unpaid</p>
                <p class="mt-1 text-2xl font-extrabold text-red-500">{{ unpaidCount }}</p>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="filteredOrders.length === 0" class="empty">No orders found.</div>
        <div v-else class="table-shell overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="th">Order #</th>
                            <th class="th">Type</th>
                            <th class="th">Table</th>
                            <th class="th">Items</th>
                            <th class="th">Total</th>
                            <th class="th">Payment Method</th>
                            <th class="th">Payment Status</th>
                            <th class="th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in filteredOrders" :key="order.id"
                            class="border-b border-slate-50 transition last:border-0 hover:bg-slate-50/60">
                            <td class="td">
                                <span class="font-bold text-slate-900">{{ order.order_number }}</span>
                                <p class="text-[11px] text-slate-400">{{ formatDate(order.created_at) }}</p>
                            </td>
                            <td class="td">
                                <span class="badge badge-slate">{{ order.order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}</span>
                            </td>
                            <td class="td">
                                <span v-if="order.table">Table {{ order.table.table_number }}</span>
                                <span v-else class="text-slate-300">-</span>
                            </td>
                            <td class="td">
                                <span class="font-semibold">{{ order.items?.length || 0 }}</span>
                            </td>
                            <td class="td">
                                <span class="font-bold text-slate-900">${{ Number(order.total).toFixed(2) }}</span>
                                <span v-if="Number(order.discount) > 0" class="ml-1 text-[11px] text-red-500">-${{ Number(order.discount).toFixed(2) }}</span>
                            </td>
                            <td class="td capitalize">
                                {{ order.payment?.payment_method || 'cash' }}
                            </td>
                            <td class="td">
                                <span v-if="order.payment?.status === 'paid'"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Paid
                                </span>
                                <span v-else
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Pending
                                </span>
                            </td>
                            <td class="td text-right">
                                <router-link v-if="order.payment?.status !== 'paid'"
                                    to="/pos"
                                    class="btn btn-primary px-3 py-1.5 text-xs">
                                    <CreditCard class="h-3.5 w-3.5" />
                                    Pay
                                </router-link>
                                
                            </td>
                            <!-- @click="processPayment(order)" -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { ChevronDown, RefreshCw, CreditCard } from 'lucide-vue-next';
import { ordersService } from '../../../services/orders';
import api from '../../../api/axios';

interface OrderItem {
    id: number;
    food_id: number;
    quantity: number;
    price: string;
    subtotal: string;
    food?: { id: number; name: string };
}

interface Order {
    id: number;
    order_number: string;
    order_type: string;
    status: string;
    subtotal: string;
    discount: string;
    total: string;
    created_at: string;
    items: OrderItem[];
    table?: { id: number; table_number: string };
    payment?: { id: number; status: string; amount: string; payment_method: string };
}

const orders = ref<Order[]>([]);
const loading = ref(false);
const paymentFilter = ref('');
const typeFilter = ref('');

const filteredOrders = computed(() => {
    return orders.value.filter(o => {
        if (paymentFilter.value && o.payment?.status !== paymentFilter.value) return false;
        return true;
    });
});

const paidCount = computed(() => filteredOrders.value.filter(o => o.payment?.status === 'paid').length);
const unpaidCount = computed(() => filteredOrders.value.filter(o => o.payment?.status !== 'paid').length);

const formatDate = (date: string) => {
    return new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const fetchOrders = async () => {
    loading.value = true;
    try {
        const params: any = {};
        if (typeFilter.value) params.order_type = typeFilter.value;
        const response = await ordersService.orders(params);
        orders.value = response.orders;
    } catch (e) {
        console.error('Failed to fetch orders:', e);
    } finally {
        loading.value = false;
    }
};

const processPayment = async (order: Order) => {
    try {
        await api.post('/payment', {
            order_id: order.id,
            amount: order.total,
            payment_method: order.payment?.payment_method || 'cash',
        });
        order.payment = {
            id: order.payment?.id || 0,
            status: 'paid',
            amount: order.total,
            payment_method: order.payment?.payment_method || 'cash',
        };
        order.status = 'completed';
    } catch (e) {
        console.error('Failed to process payment:', e);
    }
};

onMounted(() => {
    fetchOrders();
});
</script>
