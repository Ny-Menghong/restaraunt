<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Point of Sale</h1>
                <p class="page-sub">Orders waiting to be paid — collect payment from customers</p>
            </div>
            <div class="flex gap-2">
                <span class="badge badge-amber">{{ summarised.unpaid }} unpaid</span>
                <button @click="fetchOrders" class="btn btn-soft">
                    <RefreshCw class="h-4 w-4" />
                    Refresh
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchQuery" type="text" placeholder="Search order # or table..."
                    class="input pl-10" />
            </div>
            <div class="relative">
                <select v-model="typeFilter" class="input w-44 appearance-none pr-8">
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
        <div v-else-if="filteredOrders.length === 0" class="empty">
            No unpaid orders right now.
            <span class="mt-1 block text-sm text-slate-400">
                Orders from QR self-ordering (tables) and Make Order (takeaway) will appear here once placed.
            </span>
        </div>
        <div v-else class="stagger grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div v-for="order in filteredOrders" :key="order.id"
                class="card overflow-hidden transition-all duration-300 hover:shadow-lg hover:shadow-slate-900/5">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 font-extrabold text-white shadow-md shadow-emerald-600/20">
                            {{ order.order_number.split('-').pop() }}
                        </div>
                        <div>
                            <h3 class="font-extrabold tracking-tight text-slate-900">{{ order.order_number }}</h3>
                            <p class="text-xs text-slate-500">
                                <span class="badge badge-slate">{{ order.order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}</span>
                                <span v-if="order.table" class="ml-1"> Table {{ order.table.table_number }}</span>
                                <span v-if="order.customer" class="ml-1"> &middot; {{ order.customer.name }}</span>
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ formatDate(order.created_at) }}</p>
                        </div>
                    </div>
                    <button @click="openPay(order)"
                        class="btn btn-primary px-4 py-2 text-sm">
                        <CreditCard class="h-4 w-4" />
                        Collect Payment
                    </button>
                </div>

                <!-- Items -->
                <div v-if="order.items?.length" class="px-5 pt-4">
                    <div class="divide-y divide-slate-50">
                        <div v-for="item in order.items" :key="item.id"
                            class="flex items-center gap-3 py-2.5">
                            <img v-if="item.food?.image" :src="item.food.image" :alt="item.food.name"
                                class="h-12 w-12 shrink-0 rounded-xl object-cover ring-1 ring-slate-100" />
                            <div v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100">
                                <Utensils class="h-5 w-5 text-slate-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900">{{ item.food?.name || 'Food #' + item.food_id }}</p>
                                <p v-if="item.food?.description" class="truncate text-xs text-slate-400">{{ item.food.description }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    ${{ Number(item.price).toFixed(2) }}
                                    <span class="text-slate-300">x</span>
                                    <span class="font-bold text-slate-600">{{ item.quantity }}</span>
                                </p>
                            </div>
                            <span class="font-bold text-slate-900">${{ Number(item.subtotal).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                    <span class="text-xs text-slate-500">
                        <span v-if="Number(order.discount) > 0" class="text-red-500">Discount: -${{ Number(order.discount).toFixed(2) }} </span>
                        <span class="font-bold text-slate-900">Total: ${{ Number(order.total).toFixed(2) }}</span>
                    </span>
                    <span v-if="order.payment" class="badge" :class="order.payment.status === 'paid' ? 'badge-blue' : 'badge-amber'">
                        {{ order.payment.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="payingOrder" class="modal-backdrop">
            <div class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm" @click="closePay"></div>
            <div class="modal-card relative z-50 max-h-[90vh] overflow-y-auto animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Collect Payment</h2>
                        <p class="text-xs text-slate-400">{{ payingOrder.order_number }} &middot; {{ payingOrder.order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}</p>
                    </div>
                    <button @click="closePay" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="divide-y divide-slate-100">
                            <div v-for="item in payingOrder.items" :key="item.id"
                                class="flex items-center gap-3 py-1.5 text-sm">
                                <img v-if="item.food?.image" :src="item.food.image" :alt="item.food.name"
                                    class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-100" />
                                <div v-else
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                                    <Utensils class="h-4 w-4 text-slate-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900">{{ item.food?.name || 'Food #' + item.food_id }}</p>
                                    <p class="text-xs text-slate-400">${{ Number(item.price).toFixed(2) }} x {{ item.quantity }}</p>
                                </div>
                                <span class="font-semibold text-slate-900">${{ Number(item.subtotal).toFixed(2) }}</span>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between border-t border-dashed border-slate-200 pt-2 text-base font-extrabold text-slate-900">
                            <span>Total</span>
                            <span class="text-emerald-600">${{ Number(payingOrder.total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="label">Payment Method</label>
                        <select v-model="paymentMethod" @change="resetBakong" class="input">
                            <option value="cash">Cash</option>
                            <option value="aba">ABA</option>
                            <option value="acleda">ACLEDa</option>
                            <option value="bakong">Bakong</option>
                        </select>
                    </div>

                    <!-- Bakong QR flow -->
                    <div v-if="paymentMethod === 'bakong'" class="rounded-2xl border border-slate-100 p-4 text-center">
                        <template v-if="!bakongQrImage">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50">
                                <QrCode class="h-8 w-8 text-emerald-600" />
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-900">Bakong KHQR</p>
                            <p class="mb-4 mt-1 text-xs text-slate-400">
                                Generate a QR code for the customer to scan with the Bakong app
                            </p>
                            <div class="flex gap-3">
                                <button @click="startBakong" :disabled="bakongLoading"
                                    class="btn btn-primary flex-1 py-2.5">
                                    <span v-if="bakongLoading">Generating QR...</span>
                                    <template v-else>
                                        <QrCode class="h-4 w-4" />
                                        Generate QR
                                    </template>
                                </button>
                                <button @click="closePay" class="btn btn-soft">Cancel</button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="mx-auto w-52 rounded-2xl bg-white p-4 shadow-lg shadow-slate-900/5 ring-1 ring-slate-100">
                                <img :src="bakongQrImage" alt="Bakong QR" class="mx-auto w-full" />
                            </div>
                            <p class="mt-4 text-sm font-bold text-slate-900">
                                Waiting for payment...
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Scan with Bakong to pay <span class="font-bold text-emerald-600">${{ Number(payingOrder?.total).toFixed(2) }}</span>
                            </p>
                            <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-600">
                                <Clock class="h-3.5 w-3.5" />
                                Expires in {{ bakongSeconds }}s
                            </p>
                            <button @click="closePay" class="mt-4 w-full rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                Cancel Payment
                            </button>
                        </template>
                    </div>

                    <!-- Standard payment -->
                    <template v-else>
                        <p v-if="payError" class="text-sm text-red-600">{{ payError }}</p>
                        <div class="flex gap-3 pt-1">
                            <button @click="standardPay" :disabled="processing"
                                class="btn btn-primary flex-1 py-3">
                                <span v-if="processing">Processing...</span>
                                <template v-else>
                                    <CheckCircle2 class="h-4 w-4" />
                                    Confirm Payment - ${{ Number(payingOrder.total).toFixed(2) }}
                                </template>
                            </button>
                            <button @click="closePay" class="btn btn-soft">Cancel</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { Search, ChevronDown, RefreshCw, CreditCard, CheckCircle2, X, Utensils, QrCode, Clock } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { ordersService } from '../../../services/orders';
import { bakongService } from '../../../services/bakong';
import api from '../../../api/axios';

interface OrderItem {
    id: number;
    food_id: number;
    quantity: number;
    price: string;
    subtotal: string;
    food?: { id: number; name: string; image: string; price: string; description?: string };
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
    payment?: { id: number; status: string; amount: string; payment_method: string };
}

const orders = ref<Order[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const typeFilter = ref('');

const payingOrder = ref<Order | null>(null);
const paymentMethod = ref('cash');
const processing = ref(false);
const payError = ref('');

const bakongMd5 = ref('');
const bakongQrImage = ref('');
const bakongSeconds = ref(120);
const bakongLoading = ref(false);
let bakongTimer: number | null = null;

const filteredOrders = computed(() => {
    return orders.value.filter(o => {
        if (!o.payment || o.payment.status !== 'paid') return true;
        return false;
    }).filter(o => {
        if (typeFilter.value && o.order_type !== typeFilter.value) return false;
        const q = searchQuery.value.toLowerCase();
        if (!q) return true;
        const tableNo = o.table?.table_number ? 'table ' + o.table.table_number.toLowerCase() : '';
        return o.order_number.toLowerCase().includes(q) || tableNo.includes(q);
    });
});

const summarised = computed(() => {
    return {
        unpaid: filteredOrders.value.length,
    };
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
};

const fetchOrders = async () => {
    loading.value = true;
    try {
        const response = await ordersService.orders({});
        orders.value = response.orders;
    } catch (e) {
        console.error('Failed to fetch orders:', e);
    } finally {
        loading.value = false;
    }
};

const openPay = (order: Order) => {
    payingOrder.value = order;
    paymentMethod.value = order.payment?.payment_method || 'cash';
    payError.value = '';
    resetBakong();
};

const resetBakong = () => {
    stopBakongPolling();
    bakongMd5.value = '';
    bakongQrImage.value = '';
    bakongSeconds.value = 120;
    bakongLoading.value = false;
};

const stopBakongPolling = () => {
    if (bakongTimer !== null) {
        window.clearInterval(bakongTimer);
        bakongTimer = null;
    }
};

const closePay = () => {
    payingOrder.value = null;
    processing.value = false;
    payError.value = '';
    resetBakong();
};

const startBakong = async () => {
    if (!payingOrder.value) return;
    bakongLoading.value = true;
    payError.value = '';
    try {
        const data = await bakongService.checkout(Number(payingOrder.value.total));
        if (!data.success || !data.qr) {
            throw new Error(data.message || 'Failed to generate QR');
        }
        bakongMd5.value = data.md5;
        bakongSeconds.value = data.expires_in || 120;
        bakongQrImage.value = await QRCode.toDataURL(data.qr);
        bakongTimer = window.setInterval(async () => {
            bakongSeconds.value -= 1;
            if (bakongSeconds.value <= 0) {
                stopBakongPolling();
                payError.value = 'QR code expired. Please generate a new one.';
                bakongQrImage.value = '';
                return;
            }
            try {
                const result = await bakongService.verify(bakongMd5.value);
                const code = result?.responseCode;
                if (code === 0) {
                    stopBakongPolling();
                    await processPayment('bakong');
                } else if (result?.failed) {
                    stopBakongPolling();
                    payError.value = 'Transaction failed. Please try again.';
                }
            } catch (e) {
                console.error('Bakong verify failed:', e);
            }
        }, 2500);
    } catch (e: any) {
        payError.value = e.response?.data?.message || e.message || 'Failed to generate QR.';
        resetBakong();
    } finally {
        bakongLoading.value = false;
    }
};

const processPayment = async (method?: string) => {
    if (!payingOrder.value) return;
    processing.value = true;
    payError.value = '';
    const chosen = method || paymentMethod.value;
    try {
        await api.post('/payment', {
            order_id: payingOrder.value.id,
            amount: payingOrder.value.total,
            payment_method: chosen,
        });
        if (payingOrder.value.payment) {
            payingOrder.value.payment.status = 'paid';
            payingOrder.value.payment.payment_method = chosen;
        }
        payingOrder.value.status = 'completed';
        closePay();
        await fetchOrders();
    } catch (e: any) {
        throw e;
    } finally {
        processing.value = false;
    }
};

const standardPay = async () => {
    try {
        await processPayment(paymentMethod.value);
    } catch (e: any) {
        payError.value = e.response?.data?.message || e.response?.data?.error || 'Payment failed.';
    }
};

onMounted(() => {
    fetchOrders();
});

onUnmounted(() => {
    stopBakongPolling();
});
</script>