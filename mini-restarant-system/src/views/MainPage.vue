<template>
    <div class="min-h-screen">
        <Sidebar />
        <main class="min-h-screen bg-[#f4f6fb] lg:pl-64" style="margin-left: 30px;">
            <router-view />
        </main>
    </div>
</template>

<script setup lang="ts">
import Sidebar from './Sidebar.vue';
</script>