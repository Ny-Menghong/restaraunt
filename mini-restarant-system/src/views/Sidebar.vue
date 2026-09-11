<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import {
    Menu, X, LayoutDashboard, ShoppingCart, ShoppingBag, NotebookTabs,
    Utensils, Tag, Grid3X3, Users, CreditCard,
    ChartSpline, LogOut, ChefHat,
} from 'lucide-vue-next';
import { useAuthStor } from '../stores/auth';

const auth = useAuthStor();
const route = useRoute();
const router = useRouter();
const open = ref(false);

interface MenuItem {
    title: string;
    to: string;
    icon: any;
    roles?: string[];
}

const menuItems = ref<MenuItem[]>([
    { title: 'Dashboard', to: '/', icon: LayoutDashboard },
    { title: 'POS', to: '/pos', icon: ShoppingCart },
    { title: 'Make Order', to: '/make-order', icon: ShoppingBag },
    { title: 'Orders', to: '/orders', icon: NotebookTabs },
    { title: 'Payments', to: '/payments', icon: CreditCard },
    { title: 'Reports', to: '/reports', icon: ChartSpline },
]);
const managementItems = ref<MenuItem[]>([
    { title: 'Foods', to: '/foods', icon: Utensils, roles: ['admin', 'manager'] },
    { title: 'Categories', to: '/categories', icon: Tag, roles: ['admin', 'manager'] },
    { title: 'Tables', to: '/table', icon: Grid3X3, roles: ['admin', 'manager', 'cashier'] },
    { title: 'Customers', to: '/customers', icon: Users, roles: ['admin', 'manager'] },
]);
const systemItems = ref<MenuItem[]>([
    { title: 'Users', to: '/users', icon: Users, roles: ['admin'] },
]);

const roleName = (user: any) => {
    return user?.role || '';
};
const canShow = (item: MenuItem) => {
    if (!item.roles) return true;
    return item.roles.includes(roleName(auth.user));
};
const isActive = (item: MenuItem) => route.path === item.to;
const closeSidebar = () => { open.value = false; };

const handleLogout = async () => {
    try {
        await auth.logout();
        router.push({ name: 'login' });
    } catch {
        router.push({ name: 'login' });
    }
};
</script>
<template>
    <div>
        <!-- Mobile Header -->
        <header
            class="sticky top-0 z-30 flex h-16 items-center border-b border-slate-200 bg-white px-4 lg:hidden">
            <button @click="open = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100">
                <Menu class="h-6 w-6" />
            </button>
            <div class="ml-3 flex items-center gap-2">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/30">
                    <ChefHat class="h-5 w-5" />
                </div>
                <span class="text-lg font-extrabold tracking-tight text-slate-800">Konoha</span>
            </div>
        </header>
        <!-- Overlay -->
        <div v-if="open" @click="closeSidebar" class="fixed inset-0 z-40 bg-slate-900/30 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white text-slate-600 shadow-xl shadow-slate-200/60 transition-transform duration-300 lg:translate-x-0">
            <!-- Background -->
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(100%_40%_at_50%_0%,rgba(16,185,129,0.08),transparent_60%)]">
            </div>

            <!-- Logo -->
            <div class="relative flex h-20 items-center gap-3 border-b border-slate-100 px-6">
                <div
                    class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                    <ChefHat class="h-6 w-6" />
                    <!-- <span
                        class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-white">
                    </span> -->
                </div>
                <div class="min-w-0">
                    <p class="text-base font-extrabold tracking-tight text-slate-900">Konoha</p>
                    <p class="truncate text-[11px] font-medium text-slate-400">Restaurant Management</p>
                </div>
                <button @click="closeSidebar"
                    class="ml-auto rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 lg:hidden">
                    <X class="h-5 w-5" />
                </button>
            </div>
            <!-- User chip -->
            <div class="relative px-4 pt-4">
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <img v-if="auth.user?.avatar" :src="auth.user.avatar" :alt="auth.user.name"
                        class="h-10 w-10 rounded-xl object-cover ring-2 ring-emerald-500/30" />
                    <div v-else
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-base font-extrabold text-white shadow-md shadow-emerald-500/20">
                        {{ (auth.user?.name || 'A').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-slate-900">{{ auth.user?.name || 'Staff' }}</p>
                        <p class="truncate text-[11px] font-medium capitalize text-emerald-600">{{ roleName(auth.user) || 'staff' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="relative flex-1 overflow-y-auto px-3 py-4">
                <template v-if="menuItems.length">
                    <p class="mb-2 flex items-center gap-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                        <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                        Main
                    </p>
                    <div class="space-y-0.5">
                        <template v-for="item in menuItems" :key="item.title">
                            <RouterLink :to="item.to" @click="closeSidebar"
                                :class="isActive(item)
                                    ? 'bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-500/10'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold transition-all duration-200">
                                <span
                                    :class="isActive(item) ? 'opacity-100' : 'opacity-0'"
                                    class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emerald-500 transition-opacity duration-200"></span>
                                <component :is="item.icon"
                                    :class="isActive(item) ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'"
                                    class="h-[18px] w-[18px] shrink-0 transition-colors duration-200" />
                                <span class="flex-1 whitespace-nowrap">{{ item.title }}</span>
                                <span v-if="isActive(item)" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            </RouterLink>
                        </template>
                    </div>
                </template>

                <!-- Management -->
                <template v-if="canShow(managementItems[0]) || canShow(managementItems[1]) || canShow(managementItems[2]) || canShow(managementItems[3])">
                    <p class="mb-2 mt-6 flex items-center gap-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                        <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                        Management
                    </p>
                    <div class="space-y-0.5">
                        <template v-for="item in managementItems" :key="item.title">
                            <RouterLink v-if="canShow(item)" :to="item.to" @click="closeSidebar"
                                :class="isActive(item)
                                    ? 'bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-500/10'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold transition-all duration-200">
                                <span
                                    :class="isActive(item) ? 'opacity-100' : 'opacity-0'"
                                    class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emerald-500 transition-opacity duration-200"></span>
                                <component :is="item.icon"
                                    :class="isActive(item) ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'"
                                    class="h-[18px] w-[18px] shrink-0 transition-colors duration-200" />
                                <span class="flex-1 whitespace-nowrap">{{ item.title }}</span>
                                <span v-if="isActive(item)" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            </RouterLink>
                        </template>
                    </div>
                </template>

                <!-- System -->
                <template v-if="systemItems.some(canShow)">
                    <p class="mb-2 mt-6 flex items-center gap-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                        <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                        System
                    </p>
                    <div class="space-y-0.5">
                        <template v-for="item in systemItems" :key="item.title">
                            <RouterLink v-if="canShow(item)" :to="item.to" @click="closeSidebar"
                                :class="isActive(item)
                                    ? 'bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-500/10'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
                                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold transition-all duration-200">
                                <span
                                    :class="isActive(item) ? 'opacity-100' : 'opacity-0'"
                                    class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emerald-500 transition-opacity duration-200"></span>
                                <component :is="item.icon"
                                    :class="isActive(item) ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-500'"
                                    class="h-[18px] w-[18px] shrink-0 transition-colors duration-200" />
                                <span class="flex-1 whitespace-nowrap">{{ item.title }}</span>
                                <span v-if="isActive(item)" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            </RouterLink>
                        </template>
                    </div>
                </template>
            </nav>

            <!-- Footer -->
            <div class="relative border-t border-slate-100 p-4">
                <button @click="handleLogout"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold text-slate-500 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                    <LogOut class="h-[18px] w-[18px] text-slate-400 transition-colors group-hover:text-red-500" />
                    <span class="flex-1 text-left">Logout</span>
                </button>
                <p class="mt-3 px-3 text-[10px] font-medium text-slate-400">© {{ new Date().getFullYear() }} Konoha Restaurant</p>
            </div>
        </aside>
    </div>
</template>