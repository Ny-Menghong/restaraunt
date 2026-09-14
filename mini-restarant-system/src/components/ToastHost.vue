<script setup lang="ts">
import { AlertCircle, Info, X, PartyPopper } from 'lucide-vue-next';
import type { ToastItem, ToastType } from '../composables/useToast';
import { useToast } from '../composables/useToast';

const { toasts, remove } = useToast();

const config: Record<ToastType, { icon: any; iconBox: string; bar: string; glow: string }> = {
    success: {
        icon: PartyPopper,
        iconBox: 'bg-emerald-100 text-emerald-600',
        bar: 'bg-emerald-500',
        glow: 'shadow-emerald-500/20 ring-emerald-100',
    },
    error: {
        icon: AlertCircle,
        iconBox: 'bg-red-100 text-red-600',
        bar: 'bg-red-500',
        glow: 'shadow-red-500/20 ring-red-100',
    },
    info: {
        icon: Info,
        iconBox: 'bg-blue-100 text-blue-600',
        bar: 'bg-blue-500',
        glow: 'shadow-blue-500/20 ring-blue-100',
    },
};

const styleFor = (toast: ToastItem) => config[toast.type];

const durationMs = (toast: ToastItem) => `${toast.duration}ms`;
</script>

<template>
    <div class="pointer-events-none fixed right-4 top-4 z-[100] flex w-[21rem] max-w-[calc(100vw-2rem)] flex-col gap-3">
        <TransitionGroup name="toast">
            <div v-for="toast in toasts" :key="toast.id"
                class="toast-card pointer-events-auto relative overflow-hidden rounded-2xl bg-white p-4 pr-3 shadow-xl ring-1"
                :class="styleFor(toast).glow">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                        :class="styleFor(toast).iconBox">
                        <component :is="styleFor(toast).icon" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-extrabold leading-snug text-slate-900">{{ toast.title }}</p>
                        <p v-if="toast.message" class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ toast.message }}</p>
                    </div>
                    <button @click="remove(toast.id)"
                        class="shrink-0 rounded-lg p-1.5 text-slate-300 transition hover:bg-slate-100 hover:text-slate-500">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <span class="toast-progress absolute bottom-0 left-0 h-0.5 w-full"
                    :class="styleFor(toast).bar" :style="{ animationDuration: durationMs(toast) }"></span>
            </div>
        </TransitionGroup>
    </div>
</template>