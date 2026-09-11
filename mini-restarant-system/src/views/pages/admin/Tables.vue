<template>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Tables</h1>
                <p class="page-sub">Scan the QR code for customers to order</p>
            </div>
            <router-link to="/add-table" class="btn btn-primary">
                <Plus class="h-4 w-4" />
                Add New Table
            </router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="spin"></div>
        </div>
        <div v-else-if="tables.length === 0" class="empty">No tables found.</div>
        <div v-else class="stagger grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <div v-for="table in tables" :key="table.id"
                class="group overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10">
                <div class="relative bg-gradient-to-br from-slate-900 to-slate-800 px-5 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur">
                                <Grid3X3 class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">Table</p>
                                <p class="text-lg font-extrabold leading-tight">{{ table.table_number }}</p>
                            </div>
                        </div>
                        <span class="badge badge-green">Available</span>
                    </div>
                    <div class="mt-3 flex gap-4 text-xs text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <Users class="h-3.5 w-3.5" /> {{ table.capacity }} seats
                        </span>
                        <span class="flex items-center gap-1.5">
                            <MapPin class="h-3.5 w-3.5" /> {{ table.location }}
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <Qrcode :table="table" />
                    <div class="mt-4 flex gap-2">
                        <button @click="printQr(table)" class="btn btn-primary flex-1 px-2 py-2">
                            <Printer class="h-3.5 w-3.5" />
                            Print QR
                        </button>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button @click="openEditModal(table)" class="btn btn-ghost-blue flex-1 px-2 py-2">
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </button>
                        <button @click="deleteTable(table.id)" class="btn btn-ghost-danger flex-1 px-2 py-2">
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="modal-backdrop">
            <div class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm" @click="showEditModal = false"></div>
            <div class="modal-card relative z-50 animate-fade-up">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Edit Table</h2>
                        <p class="text-xs text-slate-400">Update table details</p>
                    </div>
                    <button @click="showEditModal = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="label">Table Number</label>
                        <input v-model="editForm.table_number" type="text" required class="input" />
                    </div>
                    <div>
                        <label class="label">Capacity</label>
                        <input v-model.number="editForm.capacity" type="number" min="1" required class="input" />
                    </div>
                    <div>
                        <label class="label">Location</label>
                        <input v-model="editForm.location" type="text" required class="input" />
                    </div>
                    <p v-if="editError" class="text-sm text-red-600">{{ editError }}</p>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" :disabled="editSubmitting" class="btn btn-primary flex-1">
                            <span v-if="editSubmitting">Saving...</span>
                            <span v-else>Update Table</span>
                        </button>
                        <button type="button" @click="showEditModal = false" class="btn btn-soft">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Plus, Pencil, Trash2, X, Grid3X3, Users, MapPin, Printer } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { tableService } from '../../../services/table';
import Qrcode from '../../../components/Qrcode.vue';
import type { Table } from '../../../models/table';

const loading = ref(false);
const tables = ref<Table[]>([]);
const showEditModal = ref(false);
const editSubmitting = ref(false);
const editError = ref('');
const editingId = ref<number | null>(null);

const editForm = ref({
    table_number: '',
    capacity: 2,
    location: '',
});

const fetchTables = async () => {
    loading.value = true;
    try {
        const response = await tableService.fetchTables();
        tables.value = response.tables;
    } catch (error) {
        console.error('Failed to fetch tables:', error);
    } finally {
        loading.value = false;
    }
};

const openEditModal = (table: Table) => {
    editingId.value = table.id;
    editForm.value = {
        table_number: String(table.table_number),
        capacity: table.capacity,
        location: table.location,
    };
    editError.value = '';
    showEditModal.value = true;
};

const submitEdit = async () => {
    if (!editingId.value) return;
    editSubmitting.value = true;
    editError.value = '';
    try {
        await tableService.updateTable(editingId.value, editForm.value);
        showEditModal.value = false;
        await fetchTables();
    } catch (e: any) {
        editError.value = e.response?.data?.message || 'Failed to update table';
    } finally {
        editSubmitting.value = false;
    }
};

const deleteTable = async (id: number) => {
    if (!confirm('Are you sure you want to delete this table?')) return;
    try {
        await tableService.deleteTable(id);
        await fetchTables();
    } catch (e) {
        console.error('Failed to delete table:', e);
    }
};

const printQr = async (table: Table) => {
    const qrUrl = `https://alternative-cds-stock-acer.trycloudflare.com/menu/${table.qr_token}`;
    const qrImage = await QRCode.toDataURL(qrUrl);

    const win = window.open('', '_blank', 'width=420,height=620');
    if (!win) return;

    win.document.write(`
        <html>
        <head>
            <title>QR Code - Table ${table.table_number}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, 'Segoe UI', Arial, sans-serif; background: #fff; display: flex; justify-content: center; }
                .label { width: 400px; padding: 28px 24px; text-align: center; }
                .brand { font-size: 20px; font-weight: 800; letter-spacing: -0.02em; color: #0f172a; }
                .table-num { margin-top: 4px; font-size: 32px; font-weight: 900; color: #059669; }
                .qr-box { margin: 20px auto; width: 280px; height: 280px; padding: 12px; border: 2px dashed #d1d5db; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
                .qr-box img { width: 100%; height: 100%; object-fit: contain; }
                .hint { margin-top: 14px; font-size: 14px; font-weight: 700; color: #0f172a; }
                .sub { margin-top: 4px; font-size: 12px; color: #64748b; line-height: 1.5; }
                .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="label">
                <div class="brand">Konoha Restaurant</div>
                <div class="table-num">Table ${table.table_number}</div>
                <div class="qr-box"><img src="${qrImage}" alt="QR Code" /></div>
                <div class="hint">Scan to Order</div>
                <div class="sub">Scan this QR code with your phone<br />to view the menu and place your order</div>
                <div class="footer">Konoha Restaurant</div>
            </div>
            <script>window.onload = function(){ setTimeout(function(){ window.print(); }, 300); }<\/script>
        </body>
        </html>
    `);
    win.document.close();
    win.focus();
};

onMounted(() => {
    fetchTables();
});
</script>