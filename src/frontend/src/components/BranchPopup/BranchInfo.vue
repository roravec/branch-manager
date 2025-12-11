<script setup>
import { onMounted, ref } from 'vue'
import { useSpecializationsStore } from '@/stores/specializations'
import { useBranchStore } from '@/stores/branches'
const props = defineProps({
    branch: {
        type: Object,
        required: true
    }
})

const store = useSpecializationsStore();
const branchStore = useBranchStore();

const specialization = ref(null)

onMounted(async () => {
    console.log(props.branch);
    await store.loadSpecializations();
    await store.loadBranchSpec(props.branch.id);
    await branchStore.loadBranchEmployeeCount(props.branch.id);
    specialization.value = store.getBranchSpecStr(props.branch.id);
})

</script>

<template>
    <div>
        <div class="header">{{ props.branch.name }}</div>
        <div class="industry">{{ specialization }}</div>
        <div>{{ props.branch.address }}</div>
        <div><b>Zamestnanci:</b> {{ branchStore.getBranchEmployeeCount(props.branch.id) }}</div>
        <div>{{ props.branch.description }}</div>

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
