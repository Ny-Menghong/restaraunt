import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'info';

export interface ToastItem {
    id: number;
    type: ToastType;
    title: string;
    message?: string;
    duration: number;
}

const toasts = ref<ToastItem[]>([]);
let nextId = 1;

const remove = (id: number) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index !== -1) toasts.value.splice(index, 1);
};

const push = (type: ToastType, title: string, message?: string, duration = 4000) => {
    const id = nextId++;
    toasts.value.push({ id, type, title, message, duration });
    window.setTimeout(() => remove(id), duration);
    return id;
};

export function useToast() {
    return {
        toasts,
        success: (title: string, message?: string, duration?: number) =>
            push('success', title, message, duration),
        error: (title: string, message?: string, duration?: number) =>
            push('error', title, message, duration),
        info: (title: string, message?: string, duration?: number) =>
            push('info', title, message, duration),
        remove,
    };
}