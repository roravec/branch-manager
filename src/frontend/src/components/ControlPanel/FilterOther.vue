<script setup>
const props = defineProps({
  activeOnly: Boolean
})
</script>

<template>
  <div class="tab-content">
    <b>Ostatné filtre</b>
    <label>
      <input type="checkbox" v-model="props.activeOnly" /> Aktívne prevádzky
    </label>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}
</style>
