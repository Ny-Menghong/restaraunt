<template>
    <div class="min-h-screen" style="background: linear-gradient(180deg, #f4f6fb 0%, #eef2f7 100%)">
        <!-- Header -->
        <header class="sticky top-0 z-20 border-b border-slate-200/60 bg-white/80 backdrop-blur-lg">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                        <ChefHat class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-base font-extrabold tracking-tight text-slate-900">Konoha</p>
                        <p class="text-[11px] font-medium text-slate-400">Restaurant & Dining</p>
                    </div>
                </div>
                <button @click="showCart = true"
                    class="relative flex items-center gap-2.5 rounded-2xl bg-slate-900 px-4 py-2.5 text-white shadow-lg transition hover:bg-slate-800">
                    <ShoppingBag class="h-5 w-5" />
                    <span class="text-sm font-bold">{{ cartItems.length }}</span>
                    <span v-if="cartTotal > 0" class="rounded-lg bg-white/15 px-2 py-0.5 text-xs font-bold">${{ cartTotal.toFixed(2) }}</span>
                    <span v-if="cartItems.length > 0"
                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white ring-2 ring-white">
                        {{ cartItems.length }}
                    </span>
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <!-- Table Banner -->
            <div v-if="tableInfo" class="relative mb-10 overflow-hidden rounded-3xl">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-700 via-emerald-800 to-[#0b1220]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(70%_80%_at_90%_0%,rgba(52,211,153,0.4),transparent_55%)]"></div>
                <div class="absolute inset-0 opacity-[0.06] [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:24px_24px]"></div>

                <div class="relative flex flex-col gap-6 p-6 sm:p-8 md:flex-row md:items-center md:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-emerald-200 backdrop-blur">
                            <QrCode class="h-3.5 w-3.5" />
                            You are ordering from
                        </span>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white md:text-4xl">
                            Table {{ tableInfo.table_number }}
                        </h2>
                        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-emerald-100/70">
                            <MapPin class="h-4 w-4" />
                            {{ tableInfo.location }}
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div class="rounded-2xl bg-white/10 px-5 py-4 text-center backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200/70">Seats</p>
                            <p class="mt-0.5 text-xl font-extrabold text-white">{{ tableInfo.capacity }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-5 py-4 text-center backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200/70">Table</p>
                            <p class="mt-0.5 text-xl font-extrabold text-white">#{{ tableInfo.table_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active order -->
            <div v-if="currentOrder" class="mb-8 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50">
                <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/30">
                            <ShoppingBag class="h-5 w-5" />
                        </div>
                        <div>
                            <span class="badge badge-emerald">{{ orderLabel(currentOrder) }} in progress</span>
                            <p class="mt-1 text-sm font-semibold text-emerald-700">
                                Want more food? Keep adding — it goes on the same order.
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Running total</p>
                        <p class="text-2xl font-extrabold text-emerald-700">${{ currentOrderTotal.toFixed(2) }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 border-t border-emerald-100 px-5 py-3.5">
                    <span v-for="item in currentOrder.items" :key="item.food_id"
                        class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-emerald-100">
                        <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">x{{ item.quantity }}</span>
                        {{ item.food?.name || 'Food #' + item.food_id }}
                    </span>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex flex-col items-center justify-center py-24">
                <div class="spin"></div>
                <p class="mt-4 text-sm text-slate-400">Loading our fresh menu...</p>
            </div>

            <!-- Food Section -->
            <section v-else>
                <div class="mb-6 flex items-end justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Food Menu</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ foods.length }} delicious items available</p>
                    </div>
                    <span class="badge badge-emerald hidden sm:inline-flex">{{ foods.length }} items</span>
                </div>

                <!-- Food Grid -->
                <div v-if="foods.length === 0" class="empty">Menu is empty — check back soon!</div>
                <div v-else class="stagger grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="food in foods" :key="food.id"
                        class="group overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-slate-900/10">
                        <!-- Image -->
                        <div class="relative h-52 overflow-hidden">
                            <img v-if="food.image" :src="food.image" :alt="food.name"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                            <div v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-300">
                                <UtensilsCrossed class="h-12 w-12" />
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent"></div>
                            <span class="absolute left-3 top-3 badge shadow-lg"
                                :class="food.status ? 'badge-green' : 'badge-red'">
                                {{ food.status ? 'Available' : 'Unavailable' }}
                            </span>
                            <span v-if="food.quantity > 0"
                                class="absolute right-3 top-3 rounded-full bg-black/40 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                                {{ food.quantity }} left
                            </span>
                        </div>
                        <!-- Content -->
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="line-clamp-1 text-lg font-extrabold tracking-tight text-slate-900">{{ food.name }}</h3>
                                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-600">
                                        {{ food.category?.name || 'Konoha Special' }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-2 line-clamp-2 min-h-[40px] text-sm leading-relaxed text-slate-500">
                                {{ food.description || 'Freshly prepared with the finest ingredients.' }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-medium text-slate-400">Price</p>
                                    <p class="text-xl font-extrabold text-emerald-600">${{ Number(food.price).toFixed(2) }}</p>
                                </div>
                                <button @click="addToCart(food)" :disabled="!food.status"
                                    class="btn px-4 py-2.5 disabled:cursor-not-allowed"
                                    :class="food.status ? 'btn-primary' : 'btn-soft opacity-60'">
                                    <Plus class="h-4 w-4" />
                                    {{ food.status ? 'Add to Order' : 'Unavailable' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Cart Drawer -->
        <div v-if="showCart" class="fixed inset-0 z-50 flex justify-end">
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showCart = false"></div>
            <div class="relative flex w-full max-w-md flex-col bg-white shadow-2xl animate-fade-up">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <ShoppingBag class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Your Order</h2>
                            <p class="text-xs text-slate-400">
                                {{ currentOrder ? 'Adding to ' + orderLabel(currentOrder) : cartItems.length + ' item(s) in cart' }}
                            </p>
                        </div>
                    </div>
                    <button @click="showCart = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <!-- Already ordered -->
                    <div v-if="currentOrder?.items?.length" class="mb-6">
                        <p class="mb-1 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-600">
                            <CheckCircle class="h-3.5 w-3.5" />
                            Already ordered &middot; {{ currentOrder.items.length }} item(s)
                        </p>
                        <div class="divide-y divide-emerald-50 rounded-2xl bg-emerald-50/50 px-4">
                            <div v-for="item in currentOrder.items" :key="item.id"
                                class="flex items-center gap-3 py-3">
                                <img v-if="item.food?.image" :src="item.food.image" :alt="item.food?.name"
                                    class="h-11 w-11 shrink-0 rounded-xl object-cover" />
                                <div v-else
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-400">
                                    <UtensilsCrossed class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ item.food?.name || 'Food #' + item.food_id }}</p>
                                    <p class="text-xs text-slate-400">${{ Number(item.price).toFixed(2) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-lg bg-emerald-100 px-2 py-1 text-xs font-extrabold text-emerald-700">x{{ item.quantity }}</span>
                                    <span class="w-16 text-right text-sm font-bold text-slate-700">${{ Number(item.subtotal).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New items -->
                    <p v-if="cartItems.length > 0" class="mb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Adding now &middot; {{ cartItems.length }} item(s)
                    </p>
                    <div v-if="cartItems.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-300">
                            <ShoppingBag class="h-8 w-8" />
                        </div>
                        <p class="text-sm font-bold text-slate-600">{{ currentOrder ? 'Add more delicious dishes!' : 'Your cart is empty' }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ currentOrder ? 'Browse the menu and pick something new' : 'Add some delicious dishes!' }}
                        </p>
                    </div>
                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="(item, index) in cartItems" :key="item.food.id" class="flex items-center gap-4 py-4">
                            <img v-if="item.food.image" :src="item.food.image" :alt="item.food.name"
                                class="h-16 w-16 rounded-2xl object-cover" />
                            <div v-else
                                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-300">
                                <UtensilsCrossed class="h-7 w-7" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">{{ item.food.name }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-emerald-600">${{ Number(item.food.price).toFixed(2) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="changeQty(index, -1)"
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200">
                                    <Minus class="h-4 w-4" />
                                </button>
                                <span class="w-8 text-center font-extrabold text-slate-900">{{ item.quantity }}</span>
                                <button @click="changeQty(index, 1)"
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 transition hover:bg-emerald-200">
                                    <Plus class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="cartItems.length > 0" class="border-t border-slate-100 p-5">
                    <div v-if="currentOrder" class="mb-2 flex items-center justify-between rounded-xl bg-emerald-50 px-3.5 py-2.5">
                        <span class="text-xs font-semibold text-emerald-700">Current order total</span>
                        <span class="text-sm font-extrabold text-emerald-700">${{ currentOrderTotal.toFixed(2) }}</span>
                    </div>
                    <div class="mb-2 flex items-center justify-between text-sm text-slate-500">
                        <span>Subtotal</span>
                        <span class="text-xs text-slate-400">{{ cartItems.length }} items</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-extrabold text-slate-900">Total</span>
                        <span class="text-2xl font-extrabold text-emerald-600">${{ cartTotal.toFixed(2) }}</span>
                    </div>
                    <button @click="placeOrder" :disabled="ordering" class="btn btn-primary mt-4 w-full py-3.5">
                        <span v-if="ordering">
                            <span class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            {{ currentOrder ? 'Updating Order...' : 'Placing Order...' }}
                        </span>
                        <template v-else>
                            {{ currentOrder ? 'Add to Order' : 'Place Order' }} - ${{ cartTotal.toFixed(2) }}
                            <ArrowRight class="h-4 w-4" />
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { onMounted, ref, computed } from 'vue'
import { tableService } from '../../../services/table'
import { foodService } from '../../../services/foods'
import { ordersService } from '../../../services/orders'
import { orderLabel } from '../../../utils/order'
import { useToast } from '../../../composables/useToast'
import { ShoppingBag, ChefHat, QrCode, MapPin, Plus, Minus, X, ArrowRight, UtensilsCrossed, CheckCircle } from 'lucide-vue-next'
import type { Food } from '../../../models/food'

const route = useRoute()
const toast = useToast()
const tableInfo = ref<any>(null)
const foods = ref<Food[]>([])
const loading = ref(true)
const showCart = ref(false)
const ordering = ref(false)
const currentOrder = ref<any>(null)

interface CartItem {
    food: Food;
    quantity: number;
}

const cartItems = ref<CartItem[]>([])

const cartTotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + Number(item.food.price) * item.quantity, 0)
})

const currentOrderTotal = computed(() => Number(currentOrder.value?.total ?? 0))

const orderStorageKey = computed(() => `konoha_menu_order_${route.params.qr_token}`)

const addToCart = (food: Food) => {
    const existing = cartItems.value.find(item => item.food.id === food.id)
    if (existing) {
        existing.quantity++
    } else {
        cartItems.value.push({ food, quantity: 1 })
    }
}

const changeQty = (index: number, delta: number) => {
    const item = cartItems.value[index]
    item.quantity += delta
    if (item.quantity <= 0) {
        cartItems.value.splice(index, 1)
    }
}

const placeOrder = async () => {
    if (!cartItems.value.length) return
    ordering.value = true
    try {
        const itemPayload = cartItems.value.map(item => ({
            food_id: item.food.id,
            quantity: item.quantity,
        }))
        if (currentOrder.value) {
            const response = await ordersService.addItems(currentOrder.value.id, itemPayload)
            currentOrder.value = response.data
            toast.success('Added to your order!', 'You can keep adding more anytime.')
        } else {
            const response = await ordersService.createOrder({
                table_id: tableInfo.value?.id || null,
                order_type: 'dine_in',
                items: itemPayload,
            })
            localStorage.setItem(orderStorageKey.value, String(response.data.id))
            currentOrder.value = response.data
            toast.success('Order placed successfully!', 'You can add more food to your order anytime.')
        }
        cartItems.value = []
        showCart.value = false
    } catch (e) {
        toast.error('Failed to place order.', 'Please try again.')
    } finally {
        ordering.value = false
    }
}

const loadCurrentOrder = async () => {
    const savedId = localStorage.getItem(orderStorageKey.value)
    if (!savedId) return
    try {
        const response = await ordersService.getOrder(Number(savedId))
        if (response.success && response.order?.status !== 'confirmed') {
            currentOrder.value = response.order
        } else {
            localStorage.removeItem(orderStorageKey.value)
        }
    } catch (e) {
        localStorage.removeItem(orderStorageKey.value)
    }
}

const fetchData = async () => {
    try {
        loading.value = true
        const token = route.params.qr_token as string
        const [tableData, foodData] = await Promise.all([
            tableService.fetchTableMenu(token),
            foodService.fetchFood(),
        ])
        tableInfo.value = tableData.table
        foods.value = foodData.foods || []
        await loadCurrentOrder()
    } catch (error) {
        console.error('Failed to fetch menu:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchData()
})
</script>