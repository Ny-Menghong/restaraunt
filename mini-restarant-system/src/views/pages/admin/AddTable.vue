
<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../api/axios'
import {
    ArrowLeft,
    Grid3X3,
    UserPlus,
    Users,
    MapPin,
    Hash,
    Sparkles,
    CheckCircle2,
} from 'lucide-vue-next'

const router = useRouter()

const table_number = ref('')
const capacity = ref<number>(2)
const location = ref('')
const loading = ref(false)
const error = ref('')

const submit = async () => {
    loading.value = true
    error.value = ''

    try {
        await api.post('/tables', {
            table_number: table_number.value,
            capacity: capacity.value,
            location: location.value,
        })

        router.push({ name: 'table' })
    } catch (e: any) {
        error.value =
            e.response?.data?.message ||
            e.response?.data?.errors?.table_number?.[0] ||
            'Failed to create table'
    } finally {
        loading.value = false
    }
}

const displayTableNumber = computed(() => {
    return table_number.value || 'TB-001'
})

const displayLocation = computed(() => {
    return location.value || 'Main Dining Area'
})
</script>

<template>
    <div class="min-h-screen bg-slate-50/70">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

            <!-- Back -->
            <button
                @click="router.push({ name: 'table' })"
                class="group mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition-all hover:text-slate-900"
            >
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white shadow-sm transition group-hover:-translate-x-1 group-hover:border-slate-300"
                >
                    <ArrowLeft class="h-4 w-4" />
                </span>

                Back to Tables
            </button>

            <!-- Header -->
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 shadow-sm"
                    >
                        <Sparkles class="h-3.5 w-3.5 text-amber-500" />
                        Restaurant Management
                    </div>

                    <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                        Add New Table
                    </h1>

                    <p class="mt-2 text-sm text-slate-500 sm:text-base">
                        Create a dining table and generate its QR ordering access.
                    </p>
                </div>
            </div>

            <!-- Main -->
            <div class="grid gap-6 lg:grid-cols-[1fr_360px]">

                <!-- Form Card -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <!-- Card Header -->
                    <div
                        class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 px-6 py-7 text-white"
                    >
                        <!-- Decorative -->
                        <div
                            class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/5"
                        ></div>

                        <div
                            class="absolute -bottom-20 -right-5 h-48 w-48 rounded-full bg-white/[0.03]"
                        ></div>

                        <div class="relative flex items-center gap-4">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/10 shadow-inner backdrop-blur"
                            >
                                <Grid3X3 class="h-7 w-7 text-white" />
                            </div>

                            <div>
                                <h2 class="text-lg font-extrabold">
                                    Table Configuration
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Set up the basic information for this table.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="p-6 sm:p-7">

                        <!-- Table Number -->
                        <div class="mb-5">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Table Number
                            </label>

                            <div class="relative">
                                <Hash
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="table_number"
                                    type="text"
                                    placeholder="e.g. TB-003"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5"
                                />
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Use a unique identifier such as TB-001.
                            </p>
                        </div>

                        <!-- Capacity -->
                        <div class="mb-5">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Seating Capacity
                            </label>

                            <div class="relative">
                                <Users
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model.number="capacity"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition hover:border-slate-300 focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5"
                                />
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Number of customers this table can accommodate.
                            </p>
                        </div>

                        <!-- Location -->
                        <div class="mb-6">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Location
                            </label>

                            <div class="relative">
                                <MapPin
                                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="location"
                                    type="text"
                                    placeholder="e.g. VIP Room"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus:border-slate-900 focus:bg-white focus:ring-4 focus:ring-slate-900/5"
                                />
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Example: Main Hall, VIP Room, Outdoor Area.
                            </p>
                        </div>

                        <!-- Error -->
                        <div
                            v-if="error"
                            class="mb-5 flex items-start gap-3 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-700"
                        >
                            <div
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 font-bold text-xs"
                            >
                                !
                            </div>

                            <span>{{ error }}</span>
                        </div>

                        <!-- Buttons -->
                        <div
                            class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row"
                        >
                            <router-link
                                to="/table"
                                class="flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 sm:w-28"
                            >
                                Cancel
                            </router-link>

                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                            >
                                <span
                                    v-if="loading"
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                <UserPlus
                                    v-else
                                    class="h-4 w-4"
                                />

                                <span>
                                    {{ loading ? 'Creating Table...' : 'Create Table' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preview -->
                <div
                    class="h-fit overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <p class="text-sm font-extrabold text-slate-900">
                            Table Preview
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            This is how your table information will look.
                        </p>
                    </div>

                    <div class="p-5">

                        <!-- Table visual -->
                        <div
                            class="relative flex h-52 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50"
                        >
                            <div
                                class="absolute left-6 top-6 h-20 w-20 rounded-full bg-slate-200/60 blur-2xl"
                            ></div>

                            <div
                                class="absolute bottom-4 right-5 h-24 w-24 rounded-full bg-slate-200/60 blur-2xl"
                            ></div>

                            <!-- Table -->
                            <div class="relative">
                                <div
                                    class="flex h-32 w-32 items-center justify-center rounded-full border-8 border-white bg-slate-900 shadow-2xl shadow-slate-900/20"
                                >
                                    <div
                                        class="flex h-24 w-24 flex-col items-center justify-center rounded-full border border-white/10 bg-slate-800"
                                    >
                                        <Grid3X3 class="mb-1 h-6 w-6 text-white" />

                                        <span class="text-xs font-bold text-white">
                                            {{ displayTableNumber }}
                                        </span>
                                    </div>
                                </div>

                                <!-- chairs -->
                                <div
                                    class="absolute -left-8 top-1/2 h-9 w-5 -translate-y-1/2 rounded-full bg-slate-300"
                                ></div>

                                <div
                                    class="absolute -right-8 top-1/2 h-9 w-5 -translate-y-1/2 rounded-full bg-slate-300"
                                ></div>

                                <div
                                    class="absolute -top-7 left-1/2 h-5 w-9 -translate-x-1/2 rounded-full bg-slate-300"
                                ></div>

                                <div
                                    class="absolute -bottom-7 left-1/2 h-5 w-9 -translate-x-1/2 rounded-full bg-slate-300"
                                ></div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="mt-5 space-y-3">

                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm"
                                    >
                                        <Hash class="h-4 w-4 text-slate-500" />
                                    </div>

                                    <span class="text-xs font-semibold text-slate-500">
                                        Table
                                    </span>
                                </div>

                                <span class="text-sm font-extrabold text-slate-900">
                                    {{ displayTableNumber }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm"
                                    >
                                        <Users class="h-4 w-4 text-slate-500" />
                                    </div>

                                    <span class="text-xs font-semibold text-slate-500">
                                        Capacity
                                    </span>
                                </div>

                                <span class="text-sm font-extrabold text-slate-900">
                                    {{ capacity }} Guests
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm"
                                    >
                                        <MapPin class="h-4 w-4 text-slate-500" />
                                    </div>

                                    <span class="text-xs font-semibold text-slate-500">
                                        Location
                                    </span>
                                </div>

                                <span
                                    class="max-w-[150px] truncate text-sm font-extrabold text-slate-900"
                                >
                                    {{ displayLocation }}
                                </span>
                            </div>
                        </div>

                        <!-- QR notice -->
                        <div
                            class="mt-5 flex gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4"
                        >
                            <CheckCircle2
                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                            />

                            <div>
                                <p class="text-xs font-extrabold text-emerald-800">
                                    QR Ordering Ready
                                </p>

                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    A QR code will be connected to this table for
                                    customer ordering.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

