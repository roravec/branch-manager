<script setup>
import { ref, onMounted } from "vue"
import api from "@/api.js"
import { useUsersStore } from "@/stores/users"
import { useBranchStore } from "@/stores/branches"

const ROLE_LABELS = {
  0: "Zamestnanec",
  3: "Manažér"
}

const props = defineProps({
  branch: Object
})

const usersStore = useUsersStore()
const branchesStore = useBranchStore()

const selectedUser = ref(null)
const loadingUsers = ref(false)

const branchEmployees = ref([])

async function loadBranchEmployees() {
  loadingUsers.value = true
  await usersStore.loadUsers()
  await branchesStore.loadBranchEmployees(props.branch.id, usersStore.users)

  branchEmployees.value = branchesStore.getBranchEmployees(props.branch.id)
  loadingUsers.value = false
}

async function addEmployee() {
  if (!selectedUser.value) return

  try {
    const formData = new FormData()
    formData.append("branchId", props.branch.id)
    formData.append("userId", selectedUser.value)
    await api.post("/branchHasUser", formData)

    const newUser = usersStore.users.find(u => u.id == selectedUser.value)
    branchesStore.addEmployee(props.branch.id, { userId: selectedUser.value, user: newUser, userRights: 0 })
    branchEmployees.value = branchesStore.getBranchEmployees(props.branch.id)

    selectedUser.value = null
    alert("Zamestnancovi bol pridaný");
  } catch (err) {
    alert("CHYBA pri prídávaní zamestnanca");
    console.error("Chyba pri pridaní:", err)
  }
}

async function updateUserRole(id, newRole) {
  try {
    const formData = new FormData()
    formData.append("branchId", props.branch.id)
    formData.append("userId", id)
    formData.append("userRights", newRole)

    await api.post(`/edit/branchHasUser/${id}`, formData)

    const emp = branchEmployees.value.find(e => e.userId === id)
    if (emp) emp.userRights = newRole
    alert("Zamestnancovi bola zmenená rola");
  } catch (err) {
    alert("CHYBA pri zmene rele zamestnanca");
    console.error("Chyba pri zmene role:", err)
  }
}

async function removeEmployee(id) {
  try {
    await api.delete(`/branchHasUser/${id}`)
    branchesStore.removeEmployee(props.branch.id, id)
    branchEmployees.value = branchesStore.getBranchEmployees(props.branch.id)
    alert("Zamestnanec bol odstránený");
  } catch (err) {
    alert("CHYBA pri odstranovaní zamestnanca");
    console.error("Chyba pri odstraňovaní:", err)
  }
}

onMounted(() => loadBranchEmployees())
</script>

<template>
  <div v-if="!loadingUsers">
    <table>
      <thead>
        <tr>
          <th>Meno</th>
          <th>Identifikátor</th>
          <th>Rola</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="emp in branchEmployees" :key="emp.id">
          <td>{{ emp.user.name }}</td>
          <td>{{ emp.user.identifier }}</td>
          <td>
            <select v-model="emp.userRights" @change="updateUserRole(emp.id, emp.userRights)">
              <option v-for="(label, val) in ROLE_LABELS" :value="val" :key="val">
                {{ label }}
              </option>
            </select>
          </td>
          <td>
            <button @click="removeEmployee(emp.id)" class="btn">Odstrániť</button>
          </td>
        </tr>
      </tbody>
    </table>

    <h4>Pridať zamestnanca</h4>

    <select v-model="selectedUser" class="rightSpacer">
      <option disabled value="">-- vyber používateľa --</option>

      <option v-for="user in usersStore.users" :key="user.id" :value="user.id"
        :disabled="branchEmployees.some(e => e.userId === user.id)" class="rightSpacer">
        {{ user.name }} ({{ user.identifier }})
      </option>
    </select>

    <button @click="addEmployee" :disabled="!selectedUser" class="btn">➕ Pridať</button>
  </div>
  <div v-else>
    Načítavam zamestnancov...
  </div>
</template>

<style scoped>
.rightSpacer {
  margin-right: 0.5em;
}
</style>