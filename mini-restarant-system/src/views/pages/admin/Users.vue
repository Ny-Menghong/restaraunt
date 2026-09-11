<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Users</h1>
                <p class="page-sub">Manage staff accounts and access</p>
            </div>
            <button @click="showModal = true; resetForm()" class="btn btn-primary">
                <UserPlus class="h-4 w-4" />
                Add User
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="users.length === 0" class="empty">No users found.</div>
        <div v-else class="table-shell overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="th">Name</th>
                            <th class="th">Email</th>
                            <th class="th">Gender</th>
                            <th class="th">Phone</th>
                            <th class="th">Role</th>
                            <th class="th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id"
                            class="border-b border-slate-50 transition last:border-0 hover:bg-slate-50/60">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="td">{{ user.email }}</td>
                            <td class="td capitalize">{{ user.gender }}</td>
                            <td class="td">{{ user.phone || '-' }}</td>
                            <td class="td">
                                <span class="badge badge-emerald">{{ user.role || 'N/A' }}</span>
                            </td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <button @click="editUser(user)" class="btn-icon text-blue-600 hover:bg-blue-50" title="Edit">
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button @click="deleteUser(user.id)" class="btn-icon text-red-500 hover:bg-red-50" title="Delete">
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
            <div class="modal-card relative z-50 max-h-[90vh] overflow-y-auto animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">{{ editingId ? 'Edit User' : 'Add User' }}</h2>
                        <p class="text-xs text-slate-400">{{ editingId ? 'Update the staff details' : 'Create a staff account' }}</p>
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
                        <label class="label">Email</label>
                        <input v-model="form.email" type="email" required class="input" />
                    </div>
                    <div>
                        <label class="label">Password <span class="text-xs font-normal text-slate-400">{{ editingId ? '(leave blank to keep current)' : '' }}</span></label>
                        <input v-model="form.password" type="password" :required="!editingId"
                            :placeholder="editingId ? 'Leave blank to keep current' : ''" class="input" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Gender</label>
                            <select v-model="form.gender" required class="input">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Phone</label>
                            <input v-model="form.phone" type="text" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Role</label>
                        <select v-model="form.role" required class="input">
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>
                    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="submitting" class="btn btn-primary flex-1">
                            <span v-if="submitting">Saving...</span>
                            <span v-else>{{ editingId ? 'Update User' : 'Create User' }}</span>
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
import { UserPlus, Pencil, Trash2, X } from 'lucide-vue-next';
import { userService } from '../../../services/users';

interface User {
    id: number;
    name: string;
    email: string;
    gender: string;
    phone: string | null;
    role?: string;
}

const users = ref<User[]>([]);
const roles = ['admin', 'manager', 'cashier'];
const loading = ref(false);
const showModal = ref(false);
const submitting = ref(false);
const editingId = ref<number | null>(null);
const error = ref('');

const form = ref({
    name: '',
    email: '',
    password: '',
    gender: 'male',
    phone: '',
    role: 'cashier',
});

const resetForm = () => {
    form.value = { name: '', email: '', password: '', gender: 'male', phone: '', role: 'cashier' };
    editingId.value = null;
    error.value = '';
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const response = await userService.fetchUsers();
        users.value = response.users;
    } catch (e) {
        console.error('Failed to fetch users:', e);
    } finally {
        loading.value = false;
    }
};

const editUser = (user: User) => {
    editingId.value = user.id;
    form.value = {
        name: user.name,
        email: user.email,
        password: '',
        gender: user.gender,
        phone: user.phone || '',
        role: user.role || 'cashier',
    };
    showModal.value = true;
};

const submitForm = async () => {
    submitting.value = true;
    error.value = '';
    try {
        const payload: any = {
            name: form.value.name,
            email: form.value.email,
            gender: form.value.gender,
            phone: form.value.phone || undefined,
            role: form.value.role,
        };
        if (form.value.password) {
            payload.password = form.value.password;
        }
        if (editingId.value) {
            await userService.updateUser(editingId.value, payload);
        } else {
            payload.password = form.value.password;
            await userService.createUser(payload);
        }
        showModal.value = false;
        await fetchUsers();
    } catch (e: any) {
        error.value = e.response?.data?.message || 'Failed to save user';
    } finally {
        submitting.value = false;
    }
};

const deleteUser = async (id: number) => {
    if (!confirm('Are you sure you want to delete this user?')) return;
    try {
        await userService.deleteUser(id);
        await fetchUsers();
    } catch (e) {
        console.error('Failed to delete user:', e);
    }
};

onMounted(() => {
    fetchUsers();
});
</script>