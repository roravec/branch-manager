<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { useSpecializationsStore } from "@/stores/specializations";

const specializations = useSpecializationsStore();

const selectedId = ref(null);

const editName = ref("");
const editDescription = ref("");

const newName = ref("");
const newDescription = ref("");

onMounted(async () => {
  await specializations.loadSpecializations();

  if (specializations.allSpecializations.length > 0) {
    selectedId.value = specializations.allSpecializations[0].id;
  }
});

const selectedSpec = computed(() =>
  specializations.allSpecializations.find(s => s.id == selectedId.value)
);

watch(selectedSpec, (val) => {
  if (val) {
    editName.value = val.name;
    editDescription.value = val.description;
  }
});

async function createSpec() {
  if (!newName.value) return;

  await specializations.addSpecialization(newName.value, newDescription.value);

  newName.value = "";
  newDescription.value = "";

  selectedId.value = specializations.allSpecializations[0]?.id || null;
}

async function updateSpec() {
  if (!selectedSpec.value) return;

  await specializations.updateSpecialization(
    selectedSpec.value.id,
    editName.value,
    editDescription.value
  );
}

async function deleteSpec() {
  if (!selectedSpec.value) return;

  await specializations.deleteSpecialization(selectedSpec.value.id);

  selectedId.value = specializations.allSpecializations[0]?.id || null;
}
</script>

<template>
  <div>
    <h1>Špecializácie</h1>

    <div>
      <select v-model="selectedId" class="dropdown">
        <option v-for="spec in specializations.allSpecializations" :key="spec.id" :value="spec.id">
          {{ spec.name }}
        </option>
      </select>
    </div>

    <div v-if="selectedSpec">
      <h3>Zvolená špecializácia</h3>
      <div class="formRow">
        <label>Názov:</label>
        <input v-model="editName" />
      </div>
      <div class="formRow">
        <label>Popis:</label>
        <input v-model="editDescription"></input>
      </div>
      <button @click="updateSpec" class="btn rightSpacer">Upraviť</button>
      <button @click="deleteSpec" class="btn">Zmazať</button>
    </div>
    <hr>
    <div>
      <h3>Nová špecializácia</h3>
      <div class="formRow">
        <label>Názov:</label>
        <input v-model="newName" />
      </div>
      <div class="formRow">
        <label>Popis:</label>
        <input v-model="newDescription"></input>
      </div>
      <button @click="createSpec" class="btn">Vytvoriť</button>
    </div>

  </div>
</template>

<style scoped>
.formRow {
  display: flex;
  align-items: center;
  margin: 8px 0;
}

.dropdown {
  width: 100%;
}

.rightSpacer {
  margin-right: 0.5em;
}

label {
width: 15%;
}
</style>
