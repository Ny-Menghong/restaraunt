<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../api/axios'
import { ArrowLeft, Grid3X3, UserPlus } from 'lucide-vue-next'

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
        error.value = e.response?.data?.message || e.response?.data?.errors?.table_number?.[0] || 'Failed to create table'
    } finally {
        loading.value = false
    }
}
</script>
<template>
    <div class="page max-w-xl">
        <button @click="router.push({ name: 'table' })"
            class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            <ArrowLeft class="h-4 w-4" />
            Back to Tables
        </button>

        <div class="mb-7">
            <h1 class="page-title">Add New Table</h1>
            <p class="page-sub">Create a table with a QR code for ordering</p>
        </div>

        <div class="card animate-fade-up">
            <div class="mb-6 flex items-center gap-4 rounded-xl bg-gradient-to-r from-slate-900 to-slate-700 p-4 text-white">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 backdrop-blur">
                    <Grid3X3 class="h-6 w-6" />
                </div>
                <div>
                    <p class="font-extrabold tracking-tight">Table Configuration</p>
                    <p class="text-xs text-slate-300">The QR code links to the customer menu</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="label">Table Number</label>
                    <input v-model="table_number" type="text" placeholder="e.g. TB-003" required class="input" />
                </div>
                <div>
                    <label class="label">Capacity</label>
                    <input v-model.number="capacity" type="number" min="1" required class="input" />
                </div>
                <div>
                    <label class="label">Location</label>
                    <input v-model="location" type="text" placeholder="e.g. VIP Room" required class="input" />
                </div>
                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="loading" class="btn btn-primary flex-1">
                        <UserPlus class="h-4 w-4" />
                        <span v-if="loading">Creating...</span>
                        <span v-else>Add Table</span>
                    </button>
                    <router-link to="/table" class="btn btn-soft">Cancel</router-link>
                </div>
            </form>
        </div>
    </div>
</template>