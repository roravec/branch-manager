<script setup>
const props = defineProps({
  selected: String
})
</script>

<template>
  <div class="tab-content">
    <b>Filtrovat podľa odvetvia</b>
    <select v-model="props.selected">
      <option>IT</option>
      <option>Stavebníctvo</option>
      <option>Obchod</option>
    </select>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
