<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useBranchStore } from '@/stores/branches'
import { useUserStore } from '@/stores/user'
import TabInfoBranchBasic from './TabInfoBranchBasic.vue'
import TabInfoBranchEmployees from './TabInfoBranchEmployees.vue'
import { geocodeAddress } from '@/utils/geocode.js'

const branchStore = useBranchStore();
const userStore = useUserStore();

const selectedId = ref('');
const activeTab = ref('basic');
const creatingNewBranch = ref();

const newBranch = ref({});

function startCreating() {
  newBranch.value = {
    name: '',
    coordinates: '',
    address: '',
    address2: '',
    description: '',
    employees: 0,
    utilization: 0,
  };
  creatingNewBranch.value = true;
  selectedId.value = '';
}

async function createBranch() {
  const formData = new FormData()
  formData.append('name', newBranch.value.name);
  formData.append('coordinates', newBranch.value.coordinates);
  formData.append('address', newBranch.value.address);
  formData.append('address2', newBranch.value.address2);
  formData.append('description', newBranch.value.description);
  formData.append('employees', newBranch.value.employees);
  formData.append('utilization', newBranch.value.utilization);

  try {
    const created = await branchStore.createBranch(formData);
    selectedId.value = created.id ;
    creatingNewBranch.value = false;
  } catch (err) {
    console.error(err);
    alert('Nepodarilo sa vytvoriť pobočku.');
  }
}

const tabs = [
  { name: 'basic', label: "Základné informácie" },
  { name: 'staff', label: "Zamestnanci" },
]

const selectedBranch = computed(() =>
  branchStore.branches.find(b => b.id == selectedId.value)
)

const componentsMap = {
  basic: TabInfoBranchBasic,
  staff: TabInfoBranchEmployees,
}

const currentComponent = computed(() => componentsMap[activeTab.value] || TabInfoBranchBasic);

async function updateBranchAddress() {
    const result = await geocodeAddress(newBranch.value.address);
    newBranch.value.address2 = result.district;
    newBranch.value.coordinates = result.coordinates;
}

watch(selectedId, () => {
  activeTab.value = 'basic';
})

onMounted(async () => {
  if (branchStore.branches.length > 0) {
    selectedId.value = branchStore.branches[0].id;
  }
})

</script>

<template>
  <div class="branch-content-container">
    <h2 v-if="creatingNewBranch">Nová pobočka</h2>
    <h2 v-else-if="userStore.isAdmin">Pobočky</h2>
    <h2 v-else>Pobočka</h2>

    <div v-if="!creatingNewBranch">
      <div class="select-create-row">
        <select v-model="selectedId" class="dropdown" :disabled="!userStore.isAdmin">
          <option disabled value="">-- Vyber pobočku --</option>
          <option v-for="b in branchStore.branches" :key="b.id" :value="b.id">
            {{ b.name }}
          </option>
        </select>

        <button v-if="userStore.isAdmin" @click="startCreating" class="btn create-btn">
          ➕
        </button>
      </div>
      <div class="branch-tabs-content">
        <div v-if="selectedBranch">
          <div class="tabs">
            <button v-for="tab in tabs" class="btn btn-reverse" :key="tab.name"
              :class="{ active: activeTab === tab.name }" @click="activeTab = tab.name">{{ tab.label }}
            </button>

          </div>
          <component v-if="selectedBranch" :is="currentComponent" :branch="selectedBranch" :key="activeTab" />

        </div>

        <div v-else-if="branchStore.loading">Načítavam pobočky...</div>
        <div v-else-if="branchStore.error">⚠️ {{ branchStore.error }}</div>
      </div>
    </div>

    <div v-else>
      <table>
        <tbody>
          <tr>
            <th>Názov</th>
            <td><input v-model="newBranch.name" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Adresa</th>
            <td>
              <input v-model="newBranch.address" @blur="updateBranchAddress" :readonly="!userStore.isAdmin" />
              <span v-if="geoError" style="color:red">{{ geoError }}</span>
            </td>
          </tr>
          <tr>
            <th>Okres</th>
            <td><input v-model="newBranch.address2" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Súradnice</th>
            <td><input v-model="newBranch.coordinates" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Zameranie</th>
            <td><input v-model="newBranch.utilization" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Popis</th>
            <td><input v-model="newBranch.description" :readonly="!userStore.isAdmin" /></td>
          </tr>
          <tr>
            <th>Počet zamestnancov</th>
            <td><input v-model="newBranch.employees" :readonly="!userStore.isAdmin" /></td>
          </tr>
        </tbody>
      </table>
      <button v-if="creatingNewBranch" @click="createBranch" class="btn rightSpacer">✅ Vytvoriť pobočku</button>
      <button v-if="creatingNewBranch" @click="creatingNewBranch = false" class="btn">❌ Zrušiť</button>
    </div>
  </div>
</template>

<style scoped>
.btn-reverse {
  background-color: var(--color-bg);
}

.branch-content-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: auto;
}

.branch-tabs-content {
  margin-top: 0.5em;
  flex: 1;
  box-sizing: border-box;
}

.dropdown {
  flex: 1;
}

.rightSpacer {
  margin-right: 0.5em;
}
</style>