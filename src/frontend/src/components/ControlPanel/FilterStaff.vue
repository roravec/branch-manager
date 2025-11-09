<script setup>
import { useBranchStore } from '@/stores/branches'

const store = useBranchStore()

const resetEmployees = () => {
  store.filters.employeesMin = null;
  store.filters.employeesMax = null;
}

</script>

<template>
  <div>
    <h2>Počet zamestnancov</h2>

    <div class="input-row">
      <label>Min</label>
      <input type="number" v-model.number="store.filters.employeesMin" placeholder="Min" />
    </div>

    <div class="input-row">
      <label>Max</label>
      <input type="number" v-model.number="store.filters.employeesMax" placeholder="Max" />
    </div>

    <button @click="resetEmployees" class="btn">
      Resetovať filter
    </button>
  </div>
</template>

<style scoped>
.input-row {
  display: flex;
  align-items: center;
}

.input-row label {
  width: 10%;
}
</style>
