<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Orders</h1>
                <p class="page-sub">Track and manage kitchen orders</p>
            </div>
            <button @click="fetchOrders" class="btn btn-soft">
                <RefreshCw class="h-4 w-4" />
                Refresh
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <div class="relative">
                <select v-model="statusFilter" @change="fetchOrders" class="input w-44 appearance-none pr-8">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <!-- <option value="preparing">Preparing</option> -->
                    <option value="ready">Ready</option>
                    <!-- <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option> -->
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

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="orders.length === 0" class="empty">No orders found.</div>
        <div v-else class="stagger space-y-4">
            <div v-for="order in orders" :key="order.id"
                class="card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-900/5">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 font-extrabold text-white shadow-md">
                            {{ order.order_number.split('-').pop() }}
                        </div>
                        <div>
                            <h3 class="font-extrabold tracking-tight text-slate-900">{{ order.order_number }}</h3>
                            <p class="text-xs text-slate-500">
                                {{ order.order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                                <span v-if="order.table"> &middot; Table {{ order.table.table_number }}</span>
                                <span v-if="order.customer"> &middot; {{ order.customer.name }}</span>
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge" :class="statusClass(order.status)">{{ order.status }}</span>
                        <span v-if="order.payment" class="badge"
                            :class="order.payment.status === 'paid' ? 'badge-blue' : 'badge-slate'">
                            {{ order.payment.status }}
                        </span>
                    </div>
                </div>

                <!-- Items -->
                <div v-if="order.items?.length" class="px-5 pt-4">
                    <div class="divide-y divide-slate-50">
                        <div v-for="item in order.items" :key="item.id"
                            class="flex items-center justify-between py-2 text-sm">
                            <span class="text-slate-600">
                                {{ item.food?.name || 'Food #' + item.food_id }}
                                <span class="ml-1 rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-bold text-slate-600">x{{ item.quantity }}</span>
                            </span>
                            <span class="font-semibold text-slate-900">${{ Number(item.subtotal).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                    <span class="text-xs text-slate-500">
                        Items: {{ order.items?.length || 0 }}
                        <span v-if="Number(order.discount) > 0" class="ml-2 text-red-500">Discount: -${{ Number(order.discount).toFixed(2) }}</span>
                        <span class="ml-2 font-bold text-slate-900">Total: ${{ Number(order.total).toFixed(2) }}</span>
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <button v-if="order.status === 'pending'"
                            @click="updateStatus(order, 'preparing')" class="btn btn-dark px-3 py-2 text-xs">
                            <ChefHat class="h-3.5 w-3.5" />
                            Start Preparing
                        </button>
                        <button v-if="order.status === 'preparing'"
                            @click="updateStatus(order, 'ready')" class="btn btn-primary px-3 py-2 text-xs">
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            Mark Ready
                        </button>
                        <button v-if="order.status === 'ready' && order.payment?.status !== 'paid'"
                            @click="processPayment(order)" class="btn px-3 py-2 text-xs bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-500">
                            <CreditCard class="h-3.5 w-3.5" />
                            Pay
                        </button>
                        <button v-if="order.status === 'ready'"
                            @click="updateStatus(order, 'completed')" class="btn btn-primary px-3 py-2 text-xs">
                            <CircleCheck class="h-3.5 w-3.5" />
                            Complete
                        </button>
                        <button v-if="['pending', 'preparing'].includes(order.status)"
                            @click="updateStatus(order, 'cancelled')" class="btn btn-ghost-danger px-3 py-2 text-xs">
                            <XCircle class="h-3.5 w-3.5" />
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { ChevronDown, RefreshCw, ChefHat, CheckCircle2, CircleCheck, CreditCard, XCircle } from 'lucide-vue-next';
import { ordersService } from '../../../services/orders';
import api from '../../../api/axios';

interface OrderItem {
    id: number;
    food_id: number;
    quantity: number;
    price: string;
    subtotal: string;
    food?: { id: number; name: string; image: string; price: string };
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
    customer?: { id: number; name: string };
    payment?: { id: number; status: string; amount: string };
}

const orders = ref<Order[]>([]);
const loading = ref(false);
const statusFilter = ref('');
const typeFilter = ref('');

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'badge-yellow',
        preparing: 'badge-blue',
        ready: 'badge-emerald',
        completed: 'badge-green',
        cancelled: 'badge-red',
    };
    return classes[status] || 'badge-slate';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const fetchOrders = async () => {
    loading.value = true;
    try {
        const response = await ordersService.orders({
            status: statusFilter.value,
            order_type: typeFilter.value,
        });
        orders.value = response.orders;
    } catch (e) {
        console.error('Failed to fetch orders:', e);
    } finally {
        loading.value = false;
    }
};

const updateStatus = async (order: Order, status: string) => {
    try {
        await ordersService.updateStatus(order.id, { status });
        order.status = status;
    } catch (e) {
        console.error('Failed to update order:', e);
    }
};

const processPayment = async (order: Order) => {
    try {
        await api.post('/payment', {
            order_id: order.id,
            amount: order.total,
            payment_method: 'cash',
        });
        order.payment = { id: 0, status: 'paid', amount: order.total };
        order.status = 'completed';
    } catch (e) {
        console.error('Failed to process payment:', e);
    }
};

onMounted(() => {
    fetchOrders();
});
</script>