<script setup>
defineProps({
    staff: {
        type: Array,
        required: true
    }
})
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
            <tr v-for="(emp, i) in staff" :key="i">
                <td>{{ emp.name }}</td>
                <td>{{ emp.position }}</td>
            </tr>
        </tbody>
    </table>
</template>
