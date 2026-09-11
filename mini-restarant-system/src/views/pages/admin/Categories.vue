<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Categories</h1>
                <p class="page-sub">Group your dishes into collections</p>
            </div>
            <button @click="openModal()" class="btn btn-primary">
                <Plus class="h-4 w-4" />
                Add Category
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="categories.length === 0" class="empty">No categories found.</div>
        <div v-else class="stagger grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="category in categories" :key="category.id"
                class="group overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                <div class="relative h-32 overflow-hidden">
                    <img v-if="category.image" :src="category.image" :alt="category.name"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
                    <div v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-300">
                        <Tag class="h-9 w-9" />
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    <span class="absolute bottom-3 right-3 badge badge-slate shadow">
                        {{ category.products?.length || 0 }} items
                    </span>
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-extrabold text-slate-900">{{ category.name }}</h3>
                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">/{{ category.slug }}</p>
                        </div>
                        <span class="badge" :class="category.status ? 'badge-green' : 'badge-red'">
                            {{ category.status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="mt-2 line-clamp-2 min-h-[32px] text-xs leading-relaxed text-slate-500">
                        {{ category.description || 'No description' }}
                    </p>
                    <div class="mt-3 flex gap-2 border-t border-slate-100 pt-3">
                        <button @click="openModal(category)" class="btn btn-ghost-blue flex-1 px-2 py-2">
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </button>
                        <button @click="deleteCategory(category.id)" class="btn btn-ghost-danger flex-1 px-2 py-2">
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
            <div class="modal-card relative z-50 animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">{{ editingId ? 'Edit Category' : 'Add Category' }}</h2>
                        <p class="text-xs text-slate-400">{{ editingId ? 'Update the category details' : 'Create a new category' }}</p>
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
                        <label class="label">Slug <span class="text-xs font-normal text-slate-400">(auto-generated if empty)</span></label>
                        <input v-model="form.slug" type="text" placeholder="auto-generated" class="input" />
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
                        <span class="text-sm font-semibold text-slate-700">Active</span>
                    </label>
                    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="submitting" class="btn btn-primary flex-1">
                            <span v-if="submitting">Saving...</span>
                            <span v-else>{{ editingId ? 'Update Category' : 'Create Category' }}</span>
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
import { Plus, Pencil, Trash2, X, Tag } from 'lucide-vue-next';
import { categoiyService } from '../../../services/categories';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string;
    image: string;
    status: boolean;
    products?: any[];
}

const categories = ref<Category[]>([]);
const loading = ref(false);
const showModal = ref(false);
const submitting = ref(false);
const editingId = ref<number | null>(null);
const error = ref('');

const form = ref({
    name: '',
    slug: '',
    image: '',
    description: '',
    status: true,
});

const resetForm = () => {
    form.value = { name: '', slug: '', image: '', description: '', status: true };
    editingId.value = null;
    error.value = '';
};

const openModal = (category?: Category) => {
    resetForm();
    if (category) {
        editingId.value = category.id;
        form.value = {
            name: category.name,
            slug: category.slug,
            image: category.image || '',
            description: category.description || '',
            status: category.status,
        };
    }
    showModal.value = true;
};

const fetchCategories = async () => {
    loading.value = true;
    try {
        const response = await categoiyService.fetchCategories();
        categories.value = Array.isArray(response) ? response : response.data || [];
    } catch (e) {
        console.error('Failed to fetch categories:', e);
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    submitting.value = true;
    error.value = '';
    try {
        const payload: any = { ...form.value };
        if (!payload.slug) delete payload.slug;
        if (!payload.image) delete payload.image;
        if (!payload.description) delete payload.description;
        if (editingId.value) {
            await categoiyService.updateCategory(editingId.value, payload);
        } else {
            await categoiyService.createCategory(payload);
        }
        showModal.value = false;
        await fetchCategories();
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Failed to save category';
    } finally {
        submitting.value = false;
    }
};

const deleteCategory = async (id: number) => {
    if (!confirm('Are you sure you want to delete this category?')) return;
    try {
        await categoiyService.deleteCategory(id);
        await fetchCategories();
    } catch (e) {
        console.error('Failed to delete category:', e);
    }
};

onMounted(() => {
    fetchCategories();
});
</script>