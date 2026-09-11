<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStor } from '../../stores/auth'
import { Mail, Lock, User, ArrowRight, ChefHat, UtensilsCrossed, VenetianMask } from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStor()
const name = ref('')
const gender = ref('male')
const email = ref('')
const password = ref('')
const error = ref('')

const submit = async () => {
    error.value = ''
    try {
        await auth.register(name.value, gender.value, email.value, password.value)
        router.push({ name: 'dashboard' })
    } catch (e: any) {
        error.value = e.response?.data?.message || e.response?.data?.errors?.email?.[0] || 'Register failed'
    }
}
</script>
<template>
    <div class="flex min-h-screen bg-[#f4f6fb]">
        <!-- Branding Panel -->
        <div class="relative hidden w-1/2 overflow-hidden lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-700 via-emerald-800 to-[#0b1220]"></div>
            <div
                class="absolute inset-0 bg-[radial-gradient(80%_60%_at_20%_0%,rgba(52,211,153,0.35),transparent_55%)]">
            </div>
            <div
                class="absolute inset-0 bg-[radial-gradient(60%_50%_at_90%_90%,rgba(45,212,191,0.25),transparent_50%)]">
            </div>
            <div
                class="absolute inset-0 opacity-[0.06] [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:26px_26px]">
            </div>

            <div class="relative flex h-full flex-col justify-between p-12">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur">
                        <ChefHat class="h-7 w-7" />
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Konoha Restaurant</span>
                </div>

                <div>
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-emerald-200 backdrop-blur">
                        <UtensilsCrossed class="h-3.5 w-3.5" />
                        Join the Konoha team
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white">
                        Create your<br />staff account.
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-emerald-100/70">
                        Get access to the POS, orders, reports and the full restaurant management toolkit.
                    </p>
                    <div class="mt-8 flex items-center gap-2 text-xs font-medium text-emerald-100/60">
                        <div class="flex -space-x-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-400 text-xs font-bold text-emerald-900 ring-2 ring-emerald-800">N</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-400 text-xs font-bold text-teal-900 ring-2 ring-emerald-800">S</div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-400 text-xs font-bold text-lime-900 ring-2 ring-emerald-800">K</div>
                        </div>
                        Ready when you are
                    </div>
                </div>

                <p class="text-xs text-emerald-100/40">© {{ new Date().getFullYear() }} Konoha Restaurant System</p>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="flex flex-1 items-center justify-center p-6">
            <div class="w-full max-w-md animate-fade-up">
                <div class="mb-8 text-center lg:hidden">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">
                        <ChefHat class="h-8 w-8" />
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Konoha Restaurant</h1>
                </div>

                <div class="card p-8 shadow-xl shadow-slate-900/5">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Create account</h2>
                    <p class="mt-1 text-sm text-slate-500">Join the Konoha staff</p>

                    <form @submit.prevent="submit" class="mt-7 space-y-4">
                        <div>
                            <label class="label" for="name">Full Name</label>
                            <div class="relative">
                                <User class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input id="name" v-model="name" type="text" placeholder="Your name" required class="input pl-10" />
                            </div>
                        </div>

                        <div>
                            <label class="label" for="gender">Gender</label>
                            <div class="relative">
                                <VenetianMask class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <select id="gender" v-model="gender" class="input pl-10 appearance-none">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="label" for="email">Email</label>
                            <div class="relative">
                                <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input id="email" v-model="email" type="email" placeholder="you@example.com" required class="input pl-10" />
                            </div>
                        </div>

                        <div>
                            <label class="label" for="password">Password</label>
                            <div class="relative">
                                <Lock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input id="password" v-model="password" type="password" placeholder="Min 6 characters" required class="input pl-10" />
                            </div>
                        </div>

                        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                        <button type="submit" :disabled="auth.loading" class="btn btn-primary w-full py-3">
                            <span v-if="auth.loading">
                                <span class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                Creating...
                            </span>
                            <template v-else>
                                Create Account
                                <ArrowRight class="h-4 w-4" />
                            </template>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-500">
                        Already have an account?
                        <router-link to="/login" class="font-semibold text-emerald-600 hover:text-emerald-700">
                            Sign In
                        </router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>