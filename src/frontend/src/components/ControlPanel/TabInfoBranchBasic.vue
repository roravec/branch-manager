<script setup>
import { ref } from 'vue'
import { geocodeAddress } from '@/utils/geocode.js'
import { useUserStore } from '@/stores/user'
import { useBranchStore } from '@/stores/branches'

const props = defineProps({
    branch: Object
})

const loadingGeo = ref(false);
const geoError = ref(null);

const userStore = useUserStore();
const branchStore = useBranchStore();


async function saveBranch() {
    const formData = new FormData()

    formData.append('name', props.branch.name);
    formData.append('coordinates', props.branch.coordinates);
    formData.append('address', props.branch.address);
    formData.append('address2', props.branch.address2);
    formData.append('description', props.branch.description);
    formData.append('utilization', props.branch.utilization);
    formData.append('employees', props.branch.employees);

    const result = await branchStore.updateBranch(props.branch.id, formData);
}

async function updateBranchAddress() {
    const result = await geocodeAddress(props.branch.address)
    console.log('Geocode result:', result)
    props.branch.address2 = result.district
    props.branch.coordinates = result.coordinates
}

async function deleteBranch() {
    const confirmDelete = confirm(`Naozaj chcete odstrániť pobočku ${props.branch.name}?`);
    if (!confirmDelete) return;

    const result = branchStore.deleteBranch(props.branch.id);
}
</script>


<template>
    <table>
        <tbody>
            <tr>
                <th>Názov</th>
                <td><input v-model="props.branch.name" :readonly="!userStore.isAdmin" /></td>
            </tr>
            <tr>
                <th>Adresa</th>
                <td>
                    <input v-model="props.branch.address" @blur="updateBranchAddress" :readonly="!userStore.isAdmin" />
                    <span v-if="geoError" style="color:red">{{ geoError }}</span>
                </td>
            </tr>
            <tr>
                <th>Okres</th>
                <td><input v-model="props.branch.address2" :readonly="!userStore.isAdmin" /></td>
            </tr>
            <tr>
                <th>Súradnice</th>
                <td><input v-model="props.branch.coordinates" :readonly="!userStore.isAdmin" /></td>
            </tr>
            <tr>
                <th>Zameranie</th>
                <td><input v-model="props.branch.utilization" :readonly="!userStore.isAdmin" /></td>
            </tr>
            <tr>
                <th>Popis</th>
                <td><input v-model="props.branch.description" :readonly="!userStore.isAdmin" /></td>
            </tr>
            <tr>
                <th>Počet zamestnancov</th>
                <td>{{ props.branch.employees }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <div v-if="userStore.isAdmin">
        <button @click="saveBranch" class="btn rightSpacer">💾 Uložiť zmeny</button>
        <button @click="deleteBranch" class="btn rightSpacer">🗑️ Vymazať pobočku</button>
    </div>
</template>

<style scoped>
.rightSpacer {
    margin-right: 0.5em;
}
</style>