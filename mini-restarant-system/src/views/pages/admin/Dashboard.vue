<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../../../api/axios';
import { Grid3X3, Utensils, NotebookTabs, Users, ArrowUpRight } from 'lucide-vue-next';
import { orderLabel } from '../../../utils/order';

const stats = ref({
    tables: 0,
    foods: 0,
    orders: 0,
    customers: 0,
    pendingOrders: 0,
    confirmedOrders: 0,
});

const recentOrders = ref<any[]>([]);
const newRequests = ref<any[]>([]);

onMounted(async () => {
    try {
        const [tablesRes, foodsRes, ordersRes, customersRes, usersRes] = await Promise.all([
            api.get('/tables'),
            api.get('/foods'),
            api.get('/orders'),
            api.get('/customers'),
            api.get('/users?status=inActive'),
        ]);
        stats.value.tables = tablesRes.data.tables?.length || 0;
        stats.value.foods = foodsRes.data.foods?.length || 0;
        stats.value.orders = ordersRes.data.orders?.length || 0;
        stats.value.customers = customersRes.data.customers?.length || 0;
        const orders = ordersRes.data.orders || [];
        stats.value.pendingOrders = orders.filter((o: any) => o.status === 'pending').length;
        stats.value.confirmedOrders = orders.filter((o: any) => o.status === 'confirmed').length;
        recentOrders.value = orders.slice(0, 5);
        newRequests.value = usersRes.data.users || [];
    } catch (e) {
        console.error('Failed to load dashboard:', e);
    }
});

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'badge-yellow',
        confirmed: 'badge-green',
    };
    return classes[status] || 'badge-slate';
};

const statCards = [
    {
        label: 'Total Tables',
        value: () => stats.value.tables,
        icon: Grid3X3,
        gradient: 'from-emerald-500 to-teal-600',
        shadow: 'shadow-emerald-500/30',
    },
    {
        label: 'Total Foods',
        value: () => stats.value.foods,
        icon: Utensils,
        gradient: 'from-orange-500 to-amber-600',
        shadow: 'shadow-orange-500/30',
    },
    {
        label: 'Total Orders',
        value: () => stats.value.orders,
        icon: NotebookTabs,
        gradient: 'from-blue-500 to-indigo-600',
        shadow: 'shadow-blue-500/30',
    },
    {
        label: 'Total Customers',
        value: () => stats.value.customers,
        icon: Users,
        gradient: 'from-purple-500 to-fuchsia-600',
        shadow: 'shadow-purple-500/30',
    },
];
</script>
<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-sub">A quick overview of your restaurant today</p>
            </div>
            <div class="flex gap-2">
                <span class="badge badge-yellow">Pending: {{ stats.pendingOrders }}</span>
                <span class="badge badge-green">Confirmed: {{ stats.confirmedOrders }}</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="card in statCards" :key="card.label"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                :class="[card.gradient, card.shadow]">
                <div
                    class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-150">
                </div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/70">{{ card.label }}</p>
                        <p class="mt-2 text-4xl font-extrabold tracking-tight">{{ card.value() }}</p>
                    </div>
                    <div class="rounded-xl bg-white/15 p-2.5 backdrop-blur">
                        <component :is="card.icon" class="h-6 w-6" />
                    </div>
                </div>
                <div class="relative mt-4 flex items-center gap-1.5 text-xs font-medium text-white/80">
                    <ArrowUpRight class="h-3.5 w-3.5" />
                    Live count
                </div>
            </div>
        </div>

        <!-- Recent & Requests -->
        <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Recent Orders -->
            <div class="card animate-fade-up overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Recent Orders</h2>
                    <p class="text-xs text-slate-400">Latest 5 orders placed</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-slate">{{ recentOrders.length }} shown</span>
                    <router-link to="/orders"
                        class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        View all
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </router-link>
                </div>
            </div>

            <div v-if="recentOrders.length === 0" class="empty">
               <div class="spin"></div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="th">Order #</th>
                            <th class="th">Type</th>
                            <th class="th">Table</th>
                            <th class="th">Status</th>
                            <th class="th text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in recentOrders" :key="order.id"
                            class="border-b border-slate-50 transition hover:bg-slate-50/60">
                            <td class="td font-semibold text-slate-900">{{ orderLabel(order) }}</td>
                            <td class="td">
                                <span class="badge"
                                    :class="order.order_type === 'dine_in' ? 'badge-blue' : 'badge-purple'">
                                    {{ order.order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                                </span>
                            </td>
                            <td class="td">{{ order.table?.table_number || '-' }}</td>
                            <td class="td"><span class="badge" :class="statusClass(order.status)">{{ order.status }}</span></td>
                            <td class="td text-right font-bold text-slate-900">${{ Number(order.total).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- New Requests -->
        <div class="card animate-fade-up overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                <div>
                    <h2 class="text-lg font-extrabold tracking-tight text-slate-900">New Requests</h2>
                    <p class="text-xs text-slate-400">Staff registrations awaiting approval</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge" :class="newRequests.length > 0 ? 'badge-amber' : 'badge-slate'">
                        {{ newRequests.length }} pending
                    </span>
                    <router-link to="/requests"
                        class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        View all
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </router-link>
                </div>
            </div>

            <div v-if="newRequests.length === 0" class="empty">No pending requests. All good!</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="th">Name</th>
                            <th class="th">Email</th>
                            <th class="th">Role</th>
                            <th class="th">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in newRequests" :key="user.id"
                            class="border-b border-slate-50 transition hover:bg-slate-50/60">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-semibold text-slate-900">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="td text-slate-500">{{ user.email }}</td>
                            <td class="td">
                                <span class="badge badge-emerald capitalize">{{ user.role || 'cashier' }}</span>
                            </td>
                            <td class="td">
                                <span class="badge badge-amber">awaiting</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </div><!-- /grid -->
        </div>
</template>