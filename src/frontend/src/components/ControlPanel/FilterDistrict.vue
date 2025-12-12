<script setup>
import { ref, computed, watch } from 'vue'
import { useBranchStore } from '@/stores/branches'

const store = useBranchStore();
const search = ref('');

const districts = computed(() => {
  const all = store.branches.map(b => b.address2).filter(Boolean);
  return [...new Set(all)].sort();
})

const filteredDistricts = computed(() => {
  if (!search.value) return districts.value;
  return districts.value.filter(d => d.toLowerCase().includes(search.value.toLowerCase()));
})

const toggleDistrict = (district) => {
  const idx = store.filters.district.indexOf(district);
  if (idx === -1) {
    store.filters.district.push(district);

  }
  else store.filters.district.splice(idx, 1);
}

const isChecked = (district) => store.filters.district.includes(district);

const toggleAll = () => {
  if (store.filters.district.length === districts.value.length) {
    store.filters.district = [];
  } else {
    store.filters.district = [...districts.value];
  }
}

watch(districts, (newDistricts) => {
  if (store.filters.district.length === 0) {
    store.filters.district = [...newDistricts];
  }
}, { immediate: true })
</script>

<template>
  <div>
    <h2>Okresy</h2>

    <input type="text" v-model="search" placeholder="Hľadať okres" />
    <div class="select">
      <label v-for="district in filteredDistricts" :key="district">
        <input type="checkbox" :value="district" :checked="isChecked(district)" @change="toggleDistrict(district)" class="checkbox" />
        {{ district }}
      </label>
    </div>
    <button @click="toggleAll" class="btn">
      {{ store.filters.district.length === districts.length ? 'Zrušiť výber všetkých' : 'Vybrať všetky' }}
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
