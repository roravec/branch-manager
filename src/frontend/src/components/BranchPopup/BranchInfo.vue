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
        <div class="industry">{{ branch.industry }}</div>
        <div>{{ branch.address }}</div>
        <div><b>Otvorené:</b> {{ branch.hours }}</div>
        <div><b>Zamestnanci:</b> {{ branch.staff.length }}</div>
        <div>{{ branch.note }}</div>
        <div v-if="branch.website">
            🌐 <a :href="branch.website" target="_blank">{{ branch.website }}</a>
        </div>
        <div v-if="branch.phone">
            📞 {{ branch.phone }}
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
