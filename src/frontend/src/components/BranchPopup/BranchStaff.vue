<script setup>

defineProps({
    staff: {
        type: Array,
        required: true
    }
})

const ROLE_LABELS = {
    0: "Zamestnanec",
    3: "Manažér"
}

</script>

<template>
    <table>
        <thead>
            <tr>
                <th>Meno</th>
                <th>Pozícia</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="emp in staff" :key="emp.id">
                <td>{{ emp.user.name }}</td>
                <td>{{ ROLE_LABELS[emp.userRights] }}</td>
            </tr>
        </tbody>
    </table>
</template>
