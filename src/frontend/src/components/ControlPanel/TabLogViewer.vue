<script setup>
const props = defineProps({
  logs: Array
})
</script>

<template>
  <div class="tab-content">
    <b>Logy</b>
    <div v-if="logs?.length">
      <div v-for="(log, i) in logs" :key="i">
        {{ log.timestamp }} - {{ log.action }}
      </div>
    </div>
    <div v-else>
      Žiadne logy
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
