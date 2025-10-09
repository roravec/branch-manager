<script setup>
const props = defineProps({
  branch: Object
})
</script>

<template>
  <div class="tab-content">
    <b>Informácie o pobočke</b>
    <p>Názov: {{ branch.name }}</p>
    <p>Adresa: {{ branch.address }}</p>
    <p>Zamestnanci: {{ branch.staff?.length || 0 }}</p>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
