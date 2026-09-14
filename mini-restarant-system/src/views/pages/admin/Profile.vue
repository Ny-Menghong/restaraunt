<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStor } from '../../../stores/auth'
import { useToast } from '../../../composables/useToast'
import { Pencil, X, Check, Shield, Calendar, Mail, Phone, User, KeyRound, Camera } from 'lucide-vue-next'

const auth = useAuthStor()
const toast = useToast()

const editing = ref(false)
const saving = ref(false)
const loading = ref(false)
const showPassword = ref(false)

const avatarFile = ref<File | null>(null)
const avatarPreview = ref('')
const avatarRemoved = ref(false)

const previewAvatar = computed(() => {
    return avatarPreview.value || (avatarRemoved.value ? '' : (auth.user?.avatar || ''))
})

const form = ref({
    name: '',
    gender: '',
    phone: '',
    email: '',
})
const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const loadProfile = async () => {
    loading.value = true
    try {
        await auth.fetchProfile()
    } catch {
        toast.error('Failed to load profile.')
    } finally {
        loading.value = false
    }
}

const startEdit = () => {
    form.value = {
        name: auth.user?.name || '',
        gender: auth.user?.gender || 'male',
        phone: auth.user?.phone || '',
        email: auth.user?.email || '',
    }
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    avatarFile.value = null
    avatarPreview.value = ''
    avatarRemoved.value = false
    showPassword.value = false
    editing.value = true
}

const cancelEdit = () => {
    editing.value = false
    showPassword.value = false
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    avatarFile.value = null
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
    avatarPreview.value = ''
    avatarRemoved.value = false
}

const onAvatarChange = (event: Event) => {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    if (!file) return
    avatarFile.value = file
    avatarRemoved.value = false
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
    avatarPreview.value = URL.createObjectURL(file)
}

const clearAvatar = () => {
    avatarFile.value = null
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
    avatarPreview.value = ''
    avatarRemoved.value = true
}

const saveProfile = async () => {
    saving.value = true
    try {
        const payload = new FormData()
        payload.append('name', form.value.name)
        payload.append('gender', form.value.gender)
        payload.append('phone', form.value.phone || '')
        payload.append('email', form.value.email)
        if (avatarFile.value) {
            payload.append('avatar', avatarFile.value)
        } else if (avatarRemoved.value) {
            payload.append('remove_avatar', '1')
        }
        if (showPassword.value && passwordForm.value.current_password && passwordForm.value.password) {
            payload.append('current_password', passwordForm.value.current_password)
            payload.append('password', passwordForm.value.password)
            payload.append('password_confirmation', passwordForm.value.password_confirmation)
        }
        await auth.updateProfile(payload)
        editing.value = false
        showPassword.value = false
        toast.success('Profile updated!', 'Your profile has been saved.')
    } catch (e: any) {
        toast.error('Update failed.', e.response?.data?.message || 'Please check your input.')
    } finally {
        saving.value = false
    }
}

const initials = computed(() => {
    return (auth.user?.name || 'U').charAt(0).toUpperCase()
})

const roleColor: Record<string, string> = {
    admin: 'bg-purple-100 text-purple-700',
    manager: 'bg-blue-100 text-blue-700',
    cashier: 'bg-emerald-100 text-emerald-700',
}

onMounted(loadProfile)
</script>

<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Profile</h1>
                <p class="page-sub">View and manage your account details</p>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Avatar Card -->
            <div class="card animate-fade-up p-6">
                <div class="flex flex-col items-center text-center">
                    <img v-if="auth.user?.avatar"
                        :src="auth.user.avatar"
                        :alt="auth.user.name"
                        class="h-24 w-24 rounded-2xl object-cover ring-4 ring-emerald-500/20 shadow-lg" />
                    <div v-else
                        class="flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-3xl font-extrabold text-white shadow-lg shadow-emerald-500/30">
                        {{ initials }}
                    </div>
                    <h2 class="mt-4 text-lg font-extrabold text-slate-900">{{ auth.user?.name }}</h2>
                    <p class="text-sm text-slate-500">{{ auth.user?.email }}</p>
                    <span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold capitalize"
                        :class="roleColor[auth.user?.role || 'cashier'] || 'badge-slate'">
                        <Shield class="h-3 w-3" />
                        {{ auth.user?.role || 'cashier' }}
                    </span>

                    <div class="mt-6 w-full space-y-3 border-t border-slate-100 pt-5 text-left text-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">
                                <User class="h-4 w-4 text-slate-500" />
                            </span>
                            <div>
                                <p class="text-[11px] font-medium text-slate-400">Full Name</p>
                                <p class="font-semibold text-slate-900">{{ auth.user?.name || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">
                                <Mail class="h-4 w-4 text-slate-500" />
                            </span>
                            <div>
                                <p class="text-[11px] font-medium text-slate-400">Email</p>
                                <p class="font-semibold text-slate-900">{{ auth.user?.email || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">
                                <Phone class="h-4 w-4 text-slate-500" />
                            </span>
                            <div>
                                <p class="text-[11px] font-medium text-slate-400">Phone</p>
                                <p class="font-semibold text-slate-900">{{ auth.user?.phone || '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">
                                <Calendar class="h-4 w-4 text-slate-500" />
                            </span>
                            <div>
                                <p class="text-[11px] font-medium text-slate-400">Gender</p>
                                <p class="font-semibold capitalize text-slate-900">{{ auth.user?.gender || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Edit Form / Info -->
            <div class="lg:col-span-2 animate-fade-up" style="animation-delay: 0.08s;">
                <div class="card p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Personal Information</h3>
                            <p class="text-xs text-slate-400">{{ editing ? 'Update your details below' : 'Your account information' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <template v-if="editing">
                                <button @click="cancelEdit" class="btn btn-soft px-3 py-2 text-xs">
                                    <X class="h-4 w-4" />
                                    Cancel
                                </button>
                                <button @click="saveProfile" :disabled="saving" class="btn btn-primary px-4 py-2 text-xs">
                                    <Check class="h-4 w-4" />
                                    {{ saving ? 'Saving...' : 'Save' }}
                                </button>
                            </template>
                            <button v-else @click="startEdit" class="btn btn-primary px-4 py-2 text-xs">
                                <Pencil class="h-4 w-4" />
                                Edit Profile
                            </button>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <div v-if="!editing">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Full Name</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ auth.user?.name || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Email</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ auth.user?.email || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Phone</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ auth.user?.phone || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Gender</p>
                                <p class="mt-1 text-sm font-bold capitalize text-slate-900">{{ auth.user?.gender || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Role</p>
                                <p class="mt-1 text-sm font-bold capitalize text-slate-900">{{ auth.user?.role || '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-400">Status</p>
                                <span class="mt-1 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold"
                                    :class="auth.user?.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="auth.user?.status === 'active' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                    {{ auth.user?.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <form v-else @submit.prevent="saveProfile" class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="label">Full Name</label>
                                <input v-model="form.name" type="text" class="input" required />
                            </div>
                            <div>
                                <label class="label">Email</label>
                                <input v-model="form.email" type="email" class="input" required />
                            </div>
                            <div>
                                <label class="label">Phone</label>
                                <input v-model="form.phone" type="tel" class="input" placeholder="Optional" />
                            </div>
                            <div>
                                <label class="label">Gender</label>
                                <select v-model="form.gender" class="input">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">Avatar</label>
                                <div class="flex items-start gap-4">
                                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                        <img v-if="previewAvatar" :src="previewAvatar" alt="Preview"
                                            class="h-full w-full object-cover" />
                                        <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                                            <User class="h-6 w-6" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="btn btn-soft w-full cursor-pointer px-4 py-2 text-center">
                                            <Camera class="mr-1.5 inline h-4 w-4" />
                                            Choose image
                                            <input type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                                        </label>
                                        <p v-if="avatarFile" class="mt-1.5 truncate text-xs text-slate-500">{{ avatarFile.name }}</p>
                                        <button v-if="previewAvatar" type="button" @click="clearAvatar"
                                            class="mt-1.5 text-xs font-medium text-red-500 hover:underline">
                                            Remove avatar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="border-t border-slate-100 pt-5">
                            <button type="button" @click="showPassword = !showPassword"
                                class="flex items-center gap-2 text-sm font-bold text-slate-600 transition hover:text-emerald-600">
                                <KeyRound class="h-4 w-4" />
                                {{ showPassword ? 'Hide Password Fields' : 'Change Password' }}
                            </button>
                            <div v-if="showPassword" class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
                                <div>
                                    <label class="label">Current Password</label>
                                    <input v-model="passwordForm.current_password" type="password" class="input" placeholder="Required to change" />
                                </div>
                                <div>
                                    <label class="label">New Password</label>
                                    <input v-model="passwordForm.password" type="password" class="input" placeholder="Min 6 characters" />
                                </div>
                                <div>
                                    <label class="label">Confirm Password</label>
                                    <input v-model="passwordForm.password_confirmation" type="password" class="input" placeholder="Confirm" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>