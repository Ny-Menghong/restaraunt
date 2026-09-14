<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">New Requests</h1>
                <p class="page-sub">Approve or reject staff registration requests</p>
            </div>
            <div v-if="pendingUsers.length > 0" class="badge badge-amber">
                {{ pendingUsers.length }} pending
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="pendingUsers.length === 0" class="empty">No pending requests. All good!</div>
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
                            <th class="th">Status</th>
                            <th class="th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in pendingUsers" :key="user.id"
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
                                <span class="badge badge-emerald capitalize">{{ user.role || 'cashier' }}</span>
                            </td>
                            <td class="td">
                                <select :value="user.status"
                                    @change="changeStatus(user.id, ($event.target as HTMLSelectElement).value)"
                                    class="input !w-auto !py-1 !text-xs capitalize">
                                    <option value="inActive">inActive</option>
                                    <option value="active">active</option>
                                </select>
                            </td>
                            <td class="td">
                                <div class="flex justify-end gap-2">
                                    <button @click="approveUser(user)" class="btn-icon text-emerald-600 hover:bg-emerald-50" title="Approve">
                                        <CircleCheckBig class="h-4 w-4" />
                                    </button>
                                    <button @click="rejectUser(user)" class="btn-icon text-red-500 hover:bg-red-50" title="Reject">
                                        <XIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { CircleCheckBig, XIcon } from 'lucide-vue-next';
import { userService } from '../../../services/users';

interface User {
    id: number;
    name: string;
    email: string;
    gender: string;
    phone: string | null;
    role?: string;
    status: string;
}

const pendingUsers = ref<User[]>([]);
const loading = ref(false);

const fetchPendingUsers = async () => {
    loading.value = true;
    try {
        const response = await userService.fetchPendingUsers();
        pendingUsers.value = response.users;
    } catch (e) {
        console.error('Failed to fetch pending users:', e);
    } finally {
        loading.value = false;
    }
};

const approveUser = async (user: User) => {
    try {
        await userService.updateUser(user.id, { status: 'active' });
        await fetchPendingUsers();
    } catch (e) {
        console.error('Failed to approve user:', e);
    }
};

const rejectUser = async (user: User) => {
    if (!confirm(`Reject and delete ${user.name}?`)) return;
    try {
        await userService.deleteUser(user.id);
        await fetchPendingUsers();
    } catch (e) {
        console.error('Failed to reject user:', e);
    }
};

const changeStatus = async (id: number, status: string) => {
    try {
        await userService.updateUser(id, { status });
        await fetchPendingUsers();
    } catch (e) {
        console.error('Failed to change status:', e);
    }
};

onMounted(() => {
    fetchPendingUsers();
});
</script>