<script setup>
defineProps({
    branch: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <div>
        <div class="header">{{ branch.name }}</div>
        <div class="industry">{{ branch.utilization }}</div>
        <div>{{ branch.address }}</div>
        <div><b>Zamestnanci:</b> {{ branch.employees }}</div>
        <div>{{ branch.description }}</div>
        <div v-if="branch.website">
            🌐 <a :href="branch.website" target="_blank">{{ branch.website }}</a>
        </div>
    </div>
</template>

<style scoped>
.header {
    font-weight: bold;
    font-size: 15px;
    margin-bottom: 4px;
}

.industry {
    font-style: italic;
    margin-bottom: 4px;
}
</style>
