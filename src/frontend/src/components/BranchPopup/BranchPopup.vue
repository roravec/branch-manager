<script setup>
import { ref, computed, onMounted } from 'vue'
import BranchInfo from './BranchInfo.vue'
import BranchStaff from './BranchStaff.vue'
import BranchResources from './BranchResources.vue'
import api from "@/api.js"

import BranchIcon from '../../assets/icons/BranchPopup/branch.svg'
import StaffIcon from '../../assets/icons/BranchPopup/staff.svg'
import ResourcesIcon from '../../assets/icons/BranchPopup/resources.svg'
import { useUsersStore } from "@/stores/users"

const usersStore = useUsersStore();
const branchEmployees = ref([]);

const props = defineProps({
  branch: { type: Object, required: true }
})

const activeTab = ref('branch')

const componentsMap = {
  branch: BranchInfo,
  staff: BranchStaff,
  resources: BranchResources
}

const currentComponent = computed(() => componentsMap[activeTab.value] || BranchInfo)

const currentProps = computed(() => {
  switch (activeTab.value) {
    case 'staff':
      return { staff: branchEmployees.value }
    case 'resources':
      return { resources: props.branch?.resources || [] }
    default:
      return { branch: props.branch }
  }
})

const tabs = [
  { name: 'branch', icon: BranchIcon },
  { name: 'staff', icon: StaffIcon },
  { name: 'resources', icon: ResourcesIcon }
]

onMounted(async () => {
  try {
    const response = await api.get(`/branchHasUsers/${props.branch.id}`)
    branchEmployees.value = response.data.map(item => {
      const user = usersStore.users.find(u => u.id == item.userId);
      return {
        ...item,
        user
      };
    });
  } catch (err) {
    console.error("Chyba pri načítaní:", err)
  }
})
</script>

<template>
  <div class="popup">
    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.name"
        :class="{ active: activeTab === tab.name }"
        @click="activeTab = tab.name"
      >
        <component :is="tab.icon" class="icon" />
      </button>
    </div>

    <transition name="fade" mode="out-in">
      <component
        :is="currentComponent"
        v-bind="currentProps"
        :key="activeTab"
      />
    </transition>
  </div>
</template>