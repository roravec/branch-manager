<script setup>
const props = defineProps({
  minCount: Number
})
</script>

<template>
  <div class="tab-content">
    <b>Filtrovať podľa počtu zamestnancov</b>
    <input type="number" placeholder="Min. počet" v-model="props.minCount" />
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
