<script setup>
const props = defineProps({
  value: String
})
</script>

<template>
  <div class="tab-content">
    <b>Filtrovať podľa okresu</b>
    <input type="text" placeholder="Hľadať okres..." v-model="props.value" />
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
