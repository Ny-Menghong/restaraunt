<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Reports & Analytics</h1>
                <p class="page-sub">Understand how your restaurant performs</p>
            </div>
        </div>

        <!-- Date Range -->
        <div class="card mb-8 flex flex-col gap-4 p-5 sm:flex-row sm:items-end">
            <div>
                <label class="label">From</label>
                <input v-model="dateFrom" type="date" class="input sm:w-44" />
            </div>
            <div>
                <label class="label">To</label>
                <input v-model="dateTo" type="date" class="input sm:w-44" />
            </div>
            <button @click="fetchReport" class="btn btn-dark sm:ml-1">
                <FileBarChart class="h-4 w-4" />
                Generate Report
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>

        <template v-else-if="report">
            <!-- Stats Cards -->
            <div class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="card in statCards" :key="card.label"
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-br p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                    :class="[card.gradient, card.shadow]">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-150"></div>
                    <div class="relative">
                        <div class="rounded-xl bg-white/15 p-2.5 backdrop-blur inline-flex">
                            <component :is="card.icon" class="h-5 w-5" />
                        </div>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-white/70">{{ card.label }}</p>
                        <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ card.value() }}</p>
                    </div>
                </div>
            </div>

            <!-- Second row -->
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Pending Orders</p>
                        <span class="badge badge-yellow">{{ report.pending_orders }}</span>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ report.pending_orders }}</p>
                </div>
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Cancelled Orders</p>
                        <span class="badge badge-red">{{ report.cancelled_orders }}</span>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ report.cancelled_orders }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-sm font-medium text-slate-500">Dine In vs Takeaway</p>
                    <div class="mt-2 flex items-center gap-4">
                        <p class="text-2xl font-extrabold text-slate-900">{{ report.orders_by_type.dine_in }}</p>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <p class="text-2xl font-extrabold text-slate-900">{{ report.orders_by_type.takeaway }}</p>
                    </div>
                    <div class="mt-3 flex gap-3 text-[11px] font-semibold">
                        <span class="badge badge-blue">Dine In</span>
                        <span class="badge badge-purple">Takeaway</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top Foods -->
                <div class="card animate-fade-up overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div>
                            <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Top Selling Foods</h2>
                            <p class="text-xs text-slate-400">Best performers this period</p>
                        </div>
                        <Utensils class="h-5 w-5 text-emerald-500" />
                    </div>
                    <div v-if="report.top_foods?.length === 0" class="empty">No data</div>
                    <div v-else class="divide-y divide-slate-50 px-3">
                        <div v-for="(item, index) in report.top_foods" :key="index"
                            class="flex items-center justify-between px-3 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold"
                                    :class="index === 0 ? 'bg-amber-100 text-amber-600' : index === 1 ? 'bg-slate-200 text-slate-600' : index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-slate-100 text-slate-400'">
                                    #{{ index + 1 }}
                                </span>
                                <img v-if="item.food?.image" :src="item.food.image"
                                    class="h-10 w-10 shrink-0 rounded-xl object-cover" />
                                <div v-else
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-400">
                                    <Utensils class="h-5 w-5" />
                                </div>
                                <span class="truncate text-sm font-bold text-slate-900">{{ item.food?.name || 'Unknown' }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-extrabold text-slate-900">{{ item.total_quantity }} sold</p>
                                <p class="text-xs text-emerald-600">${{ Number(item.total_revenue).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="card animate-fade-up overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div>
                            <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Payment Methods</h2>
                            <p class="text-xs text-slate-400">How your customers pay</p>
                        </div>
                        <CreditCard class="h-5 w-5 text-emerald-500" />
                    </div>
                    <div v-if="report.payment_methods?.length === 0" class="empty">No data</div>
                    <div v-else class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                        <div v-for="method in report.payment_methods" :key="method.payment_method"
                            class="rounded-2xl bg-gradient-to-br p-5 text-white shadow-md transition-transform duration-300 hover:-translate-y-0.5"
                            :class="methodGradient(method.payment_method)">
                            <p class="text-3xl font-extrabold tracking-tight">{{ method.count }}</p>
                            <p class="mt-1 text-sm font-semibold capitalize text-white/80">{{ method.payment_method }}</p>
                            <p class="mt-2 text-xs font-bold text-white/60">${{ Number(method.total).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { FileBarChart, Utensils, CreditCard, NotebookTabs, Banknote, TrendingUp, CircleCheck } from 'lucide-vue-next';
import { reportService } from '../../../services/reports';
import type { Report } from '../../../models/report';

const dateFrom = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
const dateTo = ref(new Date().toISOString().split('T')[0]);
const report = ref<Report | null>(null);
const loading = ref(false);

const statCards = computed(() => [
    {
        label: 'Total Orders',
        value: () => report.value?.total_orders ?? 0,
        icon: NotebookTabs,
        gradient: 'from-blue-500 to-indigo-600',
        shadow: 'shadow-blue-500/30',
    },
    {
        label: 'Total Revenue',
        value: () => '$' + Number(report.value?.total_revenue ?? 0).toFixed(2),
        icon: Banknote,
        gradient: 'from-emerald-500 to-teal-600',
        shadow: 'shadow-emerald-500/30',
    },
    {
        label: 'Avg Order Value',
        value: () => '$' + Number(report.value?.average_order_value ?? 0).toFixed(2),
        icon: TrendingUp,
        gradient: 'from-orange-500 to-amber-600',
        shadow: 'shadow-orange-500/30',
    },
    {
        label: 'Completed Orders',
        value: () => report.value?.completed_orders ?? 0,
        icon: CircleCheck,
        gradient: 'from-purple-500 to-fuchsia-600',
        shadow: 'shadow-purple-500/30',
    },
]);

const methodGradient = (method: string) => {
    const gradients: Record<string, string> = {
        cash: 'from-emerald-500 to-teal-600',
        aba: 'from-blue-500 to-indigo-600',
        acleda: 'from-red-500 to-rose-600',
        bakong: 'from-purple-500 to-fuchsia-600',
    };
    return gradients[method] || 'from-slate-600 to-slate-700';
};

const fetchReport = async () => {
    loading.value = true;
    try {
        const response = await reportService.fetchReports(dateFrom.value, dateTo.value);
        report.value = response.report;
    } catch (e) {
        console.error('Failed to fetch report:', e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchReport();
});
</script>