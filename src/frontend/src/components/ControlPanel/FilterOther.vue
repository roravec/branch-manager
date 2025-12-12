<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBranchStore } from '@/stores/branches'
import { useSpecializationsStore } from '@/stores/specializations'

const branchStore = useBranchStore()
const specStore = useSpecializationsStore()

const search = ref('')
const isLoading = ref(true)

// zoznam všetkých špecializácií zo store
const specializations = computed(() => specStore.specializations ?? [])

// filtrovanie podľa textu
const filteredSpecializations = computed(() => {
  if (!search.value) return specializations.value
  const q = search.value.toLowerCase()
  return specializations.value.filter(s => s.name?.toLowerCase().includes(q))
})

// toggle jednej špecializácie (podľa ID)
const toggleSpecialization = (specId) => {
  const idx = branchStore.filters.specialization.indexOf(specId)
  if (idx === -1) {
    branchStore.filters.specialization.push(specId)
  } else {
    branchStore.filters.specialization.splice(idx, 1)
  }
}

// či je špecializácia vybratá
const isChecked = (specId) =>
  branchStore.filters.specialization.includes(specId)

// vybrať / zrušiť všetky
const toggleAll = () => {
  if (branchStore.filters.specialization.length === specializations.value.length) {
    branchStore.filters.specialization = []
  } else {
    branchStore.filters.specialization = specializations.value.map(s => s.id)
  }
}

// načítaj špecializácie pri mountnutí
onMounted(async () => {
  if (!specStore.specializations.length) {
    await specStore.loadSpecializations()
  }
  isLoading.value = false
})
</script>

<template>
  <div>
    <h2>Špecializácie</h2>

    <div v-if="isLoading">Načítavam špecializácie...</div>

    <template v-else>
      <input
        type="text"
        v-model="search"
        placeholder="Hľadať špecializáciu"
      />

      <div class="select">
        <label
          v-for="spec in filteredSpecializations"
          :key="spec.id"
        >
          <input
            type="checkbox"
            class="checkbox"
            :value="spec.id"
            :checked="isChecked(spec.id)"
            @change="toggleSpecialization(spec.id)"
          />
          {{ spec.name }}
        </label>
      </div>

      <button @click="toggleAll" class="btn">
        {{
          branchStore.filters.specialization.length === specializations.length
            ? 'Zrušiť výber všetkých'
            : 'Vybrať všetky'
        }}
      </button>
    </template>
  </div>
</template>

<style>
.checkbox {
  width: 5%;
}

.select {
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}
</style>
