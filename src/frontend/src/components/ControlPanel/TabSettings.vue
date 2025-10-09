<script setup>
const props = defineProps({
  settings: Object
})
</script>

<template>
  <div class="tab-content">
    <b>Nastavenia</b>
    <p>Užívateľ: {{ settings.user || '---' }}</p>
    <p>Notifikácie: {{ settings.notifications ? 'Zapnuté' : 'Vypnuté' }}</p>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
