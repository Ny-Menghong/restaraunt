<template>
    <div class="qr-card">
        <div class="qr-box">
            <img v-if="qrImage" :src="qrImage" alt="Table QR Code" class="qr-img" />
            <div v-else class="qr-loading">Generating QR...</div>
        </div>
        <p class="qr-title">Scan to Order</p>
        <p class="qr-hint">Scan this QR code with your phone to view the menu</p>
        <div class="qr-token">
            <span>{{ table.qr_token }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import QRCode from 'qrcode'
import type { Table } from '../models/table'

const props = defineProps<{
    table: Table
}>()

const qrImage = ref('')
// https://taxation-permanent-character-defining.trycloudflare.com
const url = "http://localhost:5173";
onMounted(async () => {
    qrImage.value = await QRCode.toDataURL(
        `${url}/menu/${props.table.qr_token}`
    )
})
</script>

<style lang="css" scoped>
.qr-card {
    text-align: center;
    padding: 18px;
    border-radius: 18px;
    background: linear-gradient(180deg, #fafbfc 0%, #f1f5f9 100%);
    border: 1px dashed #d7dde6;
}

.qr-box {
    width: 170px;
    height: 170px;
    margin: 0 auto 14px;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.qr-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.qr-loading {
    color: #94a3b8;
    font-size: 12px;
}

.qr-title {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: -0.01em;
    color: #0f172a;
}

.qr-hint {
    margin: 0;
    color: #7b8794;
    font-size: 12px;
    line-height: 1.5;
}

.qr-token {
    margin-top: 12px;
}

.qr-token span {
    display: inline-block;
    max-width: 100%;
    padding: 6px 12px;
    background: #ffffff;
    border-radius: 999px;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
    word-break: break-all;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}
</style>