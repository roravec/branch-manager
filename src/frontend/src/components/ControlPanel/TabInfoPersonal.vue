<script setup>
const props = defineProps({
  user: Object
})
</script>

<template>
  <div class="tab-content">
    <b>Informácie o používateľovi</b>
    <p>Meno: {{ user.name }}</p>
    <p>Rola: {{ user.role }}</p>
    <p>Vedúci: {{ user.manager }}</p>
    <p>Pobočka: {{ user.branch }}</p>
    <p>Pozícia: {{ user.position }}</p>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
