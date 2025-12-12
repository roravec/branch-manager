<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { useSpecializationsStore } from "@/stores/specializations";
import { useUserStore } from '@/stores/user'

const specializations = useSpecializationsStore();
const userStore = useUserStore();

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
  alert("Špecifikácia je vytvorená");
}

async function updateSpec() {
  if (!selectedSpec.value) return;

  await specializations.updateSpecialization(
    selectedSpec.value.id,
    editName.value,
    editDescription.value
  );

  alert("Špecifikácia bola aktualizovaná");
}

async function deleteSpec() {
  if (!selectedSpec.value) return;

  await specializations.deleteSpecialization(selectedSpec.value.id);

  selectedId.value = specializations.allSpecializations[0]?.id || null;
  alert("Špecifikácia bola vymazaná");
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
        <input v-model="editName" :readonly="!userStore.isAdmin"/>
      </div>
      <div class="formRow">
        <label>Popis:</label>
        <input v-model="editDescription" :readonly="!userStore.isAdmin"></input>
      </div>
      <button v-if="userStore.isAdmin" @click="updateSpec" class="btn rightSpacer" >Upraviť</button>
      <button v-if="userStore.isAdmin" @click="deleteSpec" class="btn">Zmazať</button>
    </div>
    <hr>
    <div v-if="userStore.isAdmin">
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
