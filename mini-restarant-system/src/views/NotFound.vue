<template>
    <div>
        <h1 class="text-2xl">Error:</h1>
    </div>
</template>

<script setup lang="ts">
import { useErrorStore } from '../stores/error';
const errorStore = useErrorStore();
</script>
<style lang="css" scoped>

</style>