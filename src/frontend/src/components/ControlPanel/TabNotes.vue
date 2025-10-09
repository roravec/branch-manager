<script setup>
const props = defineProps({
  notes: Array
})
</script>

<template>
  <div class="tab-content">
    <b>Poznámky pobočky</b>
    <div v-if="notes?.length">
      <div v-for="(note, i) in notes" :key="i">
        - {{ note }}
      </div>
    </div>
    <div v-else>
      Žiadne poznámky
    </div>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
