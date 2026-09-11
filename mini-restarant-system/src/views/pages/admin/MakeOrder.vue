<template>
    <div class="flex h-[calc(100dvh-4rem)] overflow-hidden lg:h-screen">
        <!-- Left: Menu Items -->
        <div class="flex-1 overflow-y-auto p-5 md:p-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="page-title">Make Order</h1>
                    <p class="page-sub">Create a takeaway order</p>
                </div>
                <div class="flex gap-2">
                    <span class="badge badge-emerald">{{ filteredFoods.length }} available</span>
                    <span class="badge badge-slate">{{ cartItems.length }} items</span>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input v-model="searchQuery" type="text" placeholder="Search food..."
                        class="input pl-10" />
                </div>
                <select v-model="selectedCategory" class="input sm:w-56">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <!-- Food Grid -->
            <div v-if="filteredFoods.length === 0" class="empty">
                No dishes match your search.
            </div>
            <div v-else class="stagger grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                <div v-for="food in filteredFoods" :key="food.id"
                    @click="addToCart(food)"
                    class="group cursor-pointer overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-600/10">
                    <div class="relative h-28 overflow-hidden md:h-32">
                        <img :src="food.image || '/placeholder-food.svg'"
                            :alt="food.name" class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 transition group-hover:opacity-100">
                        </div>
                        <div class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-emerald-600 opacity-0 shadow transition group-hover:opacity-100">
                            <Plus class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="p-3.5">
                        <h3 class="truncate text-sm font-bold text-slate-900">{{ food.name }}</h3>
                        <div class="mt-1.5 flex items-center justify-between">
                            <span class="text-base font-extrabold text-emerald-600">${{ Number(food.price).toFixed(2) }}</span>
                            <span class="text-[11px] font-medium text-slate-400">Stock {{ food.quantity }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Cart -->
        <div class="hidden w-[22rem] flex-col border-l border-slate-200 bg-white lg:flex xl:w-[24rem]">
            <!-- Header -->
            <div class="border-b border-slate-100 p-5">
                <h2 class="text-lg font-extrabold tracking-tight text-slate-900">Takeaway Order</h2>
                <p class="mt-1 text-xs text-slate-400">No table required for takeaway</p>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-5">
                <div v-if="cartItems.length === 0"
                    class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-300">
                        <ShoppingBag class="h-8 w-8" />
                    </div>
                    <p class="text-sm font-semibold text-slate-500">Cart is empty</p>
                    <p class="mt-1 text-xs text-slate-400">Click a food item to add it</p>
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div v-for="(item, index) in cartItems" :key="item.food.id" class="flex items-center gap-3 py-3.5">
                        <img :src="item.food.image || '/placeholder-food.svg'" :alt="item.food.name"
                            class="h-12 w-12 rounded-xl object-cover" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-900">{{ item.food.name }}</p>
                            <p class="text-xs text-slate-400">${{ Number(item.food.price).toFixed(2) }} each</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button @click="changeQty(index, -1)"
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition hover:bg-slate-200">
                                <Minus class="h-3.5 w-3.5" />
                            </button>
                            <span class="w-6 text-center text-sm font-bold text-slate-900">{{ item.quantity }}</span>
                            <button @click="changeQty(index, 1)"
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 transition hover:bg-emerald-200">
                                <Plus class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <button @click="removeFromCart(index)"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-slate-300 transition hover:bg-red-50 hover:text-red-500">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="border-t border-slate-100 p-5">
                <div class="space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-bold text-slate-900">${{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-500">Discount</span>
                        <input v-model.number="discount" type="number" min="0" step="0.01" placeholder="0.00"
                            class="flex-1 rounded-lg border border-slate-200 px-2.5 py-1.5 text-right text-sm font-semibold outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
                    </div>
                    <div class="flex justify-between border-t border-dashed border-slate-200 pt-2.5 text-base font-extrabold text-slate-900">
                        <span>Total</span>
                        <span class="text-emerald-600">${{ total.toFixed(2) }}</span>
                    </div>
                    <div>
                        <select v-model="paymentMethod" class="input">
                            <option value="cash">Cash</option>
                            <option value="aba">ABA</option>
                            <option value="acleda">ACLEDa</option>
                            <option value="bakong">Bakong</option>
                        </select>
                    </div>
                </div>
                <button @click="placeOrder" :disabled="cartItems.length === 0 || ordering" class="btn btn-primary mt-4 w-full py-3">
                    <span v-if="ordering">
                        <span class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        Placing Order...
                    </span>
                    <template v-else>
                        <CreditCard class="h-4 w-4" />
                        Place Takeaway Order - ${{ total.toFixed(2) }}
                    </template>
                </button>
                <button @click="clearCart"
                    class="btn btn-soft mt-2 w-full">
                    <Trash2 class="h-4 w-4" />
                    Clear Cart
                </button>
            </div>
        </div>

        <!-- Mobile cart bar -->
        <button v-if="cartItems.length > 0"
            @click="mobileCartOpen = !mobileCartOpen"
            class="fixed bottom-4 right-4 z-40 flex items-center gap-3 rounded-2xl bg-emerald-600 px-5 py-3.5 text-white shadow-2xl shadow-emerald-600/40 transition lg:hidden">
            <ShoppingBag class="h-5 w-5" />
            <span class="text-sm font-bold">{{ cartItems.length }} items</span>
            <span class="text-sm font-extrabold">${{ total.toFixed(2) }}</span>
        </button>

        <!-- Mobile cart drawer -->
        <div v-if="mobileCartOpen" class="fixed inset-0 z-50 flex justify-end lg:hidden">
            <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" @click="mobileCartOpen = false"></div>
            <div class="relative flex w-full max-w-md flex-col bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 p-5">
                    <h2 class="text-lg font-extrabold text-slate-900">Takeaway Order</h2>
                    <button @click="mobileCartOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-5">
                    <div v-if="cartItems.length === 0" class="empty">Cart is empty</div>
                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="(item, index) in cartItems" :key="item.food.id" class="flex items-center gap-3 py-3.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">{{ item.food.name }}</p>
                                <p class="text-xs text-slate-400">${{ Number(item.food.price).toFixed(2) }}</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="changeQty(index, -1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><Minus class="h-3.5 w-3.5" /></button>
                                <span class="w-6 text-center text-sm font-bold">{{ item.quantity }}</span>
                                <button @click="changeQty(index, 1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"><Plus class="h-3.5 w-3.5" /></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-slate-100 p-5">
                    <div class="flex justify-between text-base font-extrabold">
                        <span>Total</span>
                        <span class="text-emerald-600">${{ total.toFixed(2) }}</span>
                    </div>
                    <button @click="placeOrder" :disabled="cartItems.length === 0 || ordering" class="btn btn-primary mt-4 w-full py-3">
                        Place Takeaway Order - ${{ total.toFixed(2) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { Search, Plus, Minus, Trash2, ShoppingBag, CreditCard, X } from 'lucide-vue-next';
import { foodService } from '../../../services/foods';
import { ordersService } from '../../../services/orders';
import { categoiyService } from '../../../services/categories';

interface Food {
    id: number;
    category_id: number;
    name: string;
    price: number;
    image: string;
    quantity: number;
    status: boolean;
    category?: { id: number; name: string };
}

interface CartItem {
    food: Food;
    quantity: number;
}

const foods = ref<Food[]>([]);
const categories = ref<{ id: number; name: string }[]>([]);
const cartItems = ref<CartItem[]>([]);
const searchQuery = ref('');
const selectedCategory = ref('');
const discount = ref(0);
const paymentMethod = ref('cash');
const ordering = ref(false);
const mobileCartOpen = ref(false);

const filteredFoods = computed(() => {
    return foods.value.filter(f => {
        if (!f.status) return false;
        if (searchQuery.value && !f.name.toLowerCase().includes(searchQuery.value.toLowerCase())) return false;
        if (selectedCategory.value && f.category_id !== Number(selectedCategory.value)) return false;
        return true;
    });
});

const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + Number(item.food.price) * item.quantity, 0);
});

const total = computed(() => {
    return Math.max(0, subtotal.value - discount.value);
});

const addToCart = (food: Food) => {
    if (!food.status) return;
    const existing = cartItems.value.find(item => item.food.id === food.id);
    if (existing) {
        existing.quantity++;
    } else {
        cartItems.value.push({ food, quantity: 1 });
    }
};

const changeQty = (index: number, delta: number) => {
    const item = cartItems.value[index];
    item.quantity += delta;
    if (item.quantity <= 0) {
        cartItems.value.splice(index, 1);
    }
};

const removeFromCart = (index: number) => {
    cartItems.value.splice(index, 1);
};

const clearCart = () => {
    cartItems.value = [];
    discount.value = 0;
};

const placeOrder = async () => {
    if (!cartItems.value.length) return;
    ordering.value = true;
    try {
        await ordersService.createOrder({
            table_id: null,
            order_type: 'takeaway',
            payment_method: paymentMethod.value,
            discount: discount.value,
            items: cartItems.value.map(item => ({
                food_id: item.food.id,
                quantity: item.quantity,
            })),
        });
        clearCart();
        mobileCartOpen.value = false;
        alert('Takeaway order placed successfully!');
    } catch (e: any) {
        alert(e.response?.data?.message || 'Failed to place order.');
    } finally {
        ordering.value = false;
    }
};

onMounted(async () => {
    try {
        const [foodsRes, catsRes] = await Promise.all([
            foodService.fetchFood(),
            categoiyService.fetchCategories(),
        ]);
        foods.value = foodsRes.foods;
        categories.value = Array.isArray(catsRes) ? catsRes : catsRes.data || [];
    } catch (e) {
        console.error('Failed to load data:', e);
    }
});
</script>
