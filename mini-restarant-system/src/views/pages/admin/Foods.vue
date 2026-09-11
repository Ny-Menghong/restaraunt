<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Foods</h1>
                <p class="page-sub">Manage your menu dishes</p>
            </div>
            <button @click="openModal()" class="btn btn-primary">
                <Plus class="h-4 w-4" />
                Add Food
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="foods.length === 0" class="empty">No foods found. Add your first dish!</div>
        <div v-else class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="food in foods" :key="food.id"
                class="group overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                <!-- Image -->
                <div class="relative h-44 overflow-hidden">
                    <img v-if="food.image" :src="food.image" :alt="food.name"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                    <div v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-300">
                        <UtensilsCrossed class="h-10 w-10" />
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    <span class="absolute left-3 top-3 badge shadow"
                        :class="food.status ? 'badge-green' : 'badge-red'">
                        {{ food.status ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="absolute bottom-3 right-3 text-xs font-bold text-white drop-shadow">
                        {{ food.quantity }} in stock
                    </span>
                </div>
                <!-- Content -->
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-extrabold text-slate-900">{{ food.name }}</h3>
                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-emerald-600">
                                {{ food.category?.name || 'N/A' }}
                            </p>
                        </div>
                        <span class="text-lg font-extrabold text-slate-900">${{ Number(food.price).toFixed(2) }}</span>
                    </div>
                    <p class="mt-2 line-clamp-2 min-h-[36px] text-xs leading-relaxed text-slate-500">
                        {{ food.description || 'No description' }}
                    </p>
                    <div class="mt-3 flex gap-2 border-t border-slate-100 pt-3">
                        <button @click="openModal(food)" class="btn btn-ghost-blue flex-1 px-2 py-2">
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </button>
                        <button @click="deleteFood(food.id)" class="btn btn-ghost-danger flex-1 px-2 py-2">
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal-backdrop">
            <div class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="modal-card relative z-50 max-h-[90vh] overflow-y-auto animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">{{ editingId ? 'Edit Food' : 'Add Food' }}</h2>
                        <p class="text-xs text-slate-400">{{ editingId ? 'Update the dish details' : 'Create a new dish' }}</p>
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
                    <div>
                        <label class="label">Category</label>
                        <select v-model="form.category_id" required class="input">
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Price</label>
                            <input v-model.number="form.price" type="number" step="0.01" min="0" required class="input" />
                        </div>
                        <div>
                            <label class="label">Quantity</label>
                            <input v-model.number="form.quantity" type="number" min="0" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Image URL</label>
                        <input v-model="form.image" type="text" placeholder="https://..." class="input" />
                    </div>
                    <div>
                        <label class="label">Description</label>
                        <textarea v-model="form.description" rows="2" class="input"></textarea>
                    </div>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input v-model="form.status" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm font-semibold text-slate-700">Active <span class="text-xs font-normal text-slate-400">(visible in POS & menu)</span></span>
                    </label>
                    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="submitting" class="btn btn-primary flex-1">
                            <span v-if="submitting">Saving...</span>
                            <span v-else>{{ editingId ? 'Update Food' : 'Create Food' }}</span>
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
import { Plus, Pencil, Trash2, X, UtensilsCrossed } from 'lucide-vue-next';
import { foodService } from '../../../services/foods';
import { categoiyService } from '../../../services/categories';

interface Food {
    id: number;
    category_id: number;
    name: string;
    description: string;
    price: number;
    image: string;
    quantity: number;
    status: boolean;
    category?: { id: number; name: string };
}

interface Category {
    id: number;
    name: string;
}

const foods = ref<Food[]>([]);
const categories = ref<Category[]>([]);
const loading = ref(false);
const showModal = ref(false);
const submitting = ref(false);
const editingId = ref<number | null>(null);
const error = ref('');

const form = ref({
    name: '',
    category_id: null as number | null,
    price: 0,
    quantity: 0,
    image: '',
    description: '',
    status: true,
});

const resetForm = () => {
    form.value = { name: '', category_id: null, price: 0, quantity: 0, image: '', description: '', status: true };
    editingId.value = null;
    error.value = '';
};

const openModal = (food?: Food) => {
    resetForm();
    if (food) {
        editingId.value = food.id;
        form.value = {
            name: food.name,
            category_id: food.category_id,
            price: food.price,
            quantity: food.quantity,
            image: food.image || '',
            description: food.description || '',
            status: food.status,
        };
    }
    showModal.value = true;
};

const fetchData = async () => {
    loading.value = true;
    try {
        const [foodsRes, catsRes] = await Promise.all([
            foodService.fetchFood(),
            categoiyService.fetchCategories(),
        ]);
        foods.value = foodsRes.foods;
        categories.value = Array.isArray(catsRes) ? catsRes : catsRes.data || [];
    } catch (e) {
        console.error('Failed to fetch data:', e);
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    submitting.value = true;
    error.value = '';
    try {
        const payload: any = { ...form.value };
        if (!payload.image) delete payload.image;
        if (!payload.description) delete payload.description;
        if (editingId.value) {
            await foodService.updateFood(editingId.value, payload);
        } else {
            await foodService.createFood(payload);
        }
        showModal.value = false;
        await fetchData();
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Failed to save food';
    } finally {
        submitting.value = false;
    }
};

const deleteFood = async (id: number) => {
    if (!confirm('Are you sure you want to delete this food item?')) return;
    try {
        await foodService.deleteFood(id);
        await fetchData();
    } catch (e) {
        console.error('Failed to delete food:', e);
    }
};

onMounted(() => {
    fetchData();
});
</script>