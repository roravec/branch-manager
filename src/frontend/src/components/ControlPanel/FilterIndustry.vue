<script setup>
import { ref, computed, watch } from 'vue'
import { useBranchStore } from '@/stores/branches'

const store = useBranchStore();
const searchUtil = ref('');

const utilizations = computed(() => {
  const all = store.branches.map(b => b.utilization).filter(v => v != null);
  return [...new Set(all)].sort((a, b) => a - b);
})

const filteredUtilizations = computed(() => {
  if (!searchUtil.value) return utilizations.value
  return utilizations.value.filter(u => u.toString().includes(searchUtil.value));
})

const toggleUtilization = (u) => {
  const idx = store.filters.utilization?.indexOf?.(u) ?? -1;
  if (idx === -1) {
    if (!Array.isArray(store.filters.utilization)) store.filters.utilization = []
    store.filters.utilization.push(u);
  } else {
    store.filters.utilization.splice(idx, 1);
  }
}

const isSelected = (u) => store.filters.utilization?.includes(u)

const toggleAll = () => {
  if (store.filters.utilization?.length === utilizations.value.length) {
    store.filters.utilization = [];
  } else {
    store.filters.utilization = [...utilizations.value];
  }
}

watch(utilizations, (newVals) => {
  if (!store.filters.utilization || store.filters.utilization.length === 0) {
    store.filters.utilization = [...newVals];
  }
}, { immediate: true })
</script>

<template>
  <div>
    <h2>Vyťaženosť</h2>

    <input type="text" v-model="searchUtil" placeholder="Hľadať vyťaženosť" />

    <div class="select">
      <label v-for="u in filteredUtilizations" :key="u">
        <input type="checkbox" :value="u" :checked="isSelected(u)" @change="toggleUtilization(u)" class="checkbox"/>
        {{ u }}
      </label>
    </div>

    <button @click="toggleAll" class="btn">
      {{ store.filters.utilization.length === utilizations.length ? 'Zrušiť výber všetkých' : 'Vybrať všetky' }}
    </button>
  </div>
</template>

<style>
.checkbox{
  width: 5%;
}

.select{
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}
</style>
