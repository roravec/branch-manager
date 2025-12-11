<script setup>
import { computed, ref, onMounted } from 'vue'
import BranchInfo from './BranchInfo.vue'
import BranchStaff from './BranchStaff.vue'

import BranchIcon from '../../assets/icons/BranchPopup/branch.svg'
import StaffIcon from '../../assets/icons/BranchPopup/staff.svg'

import { useUsersStore } from "@/stores/users"
import { useBranchStore } from "@/stores/branches"

const branchesStore = useBranchStore()
const usersStore = useUsersStore()

const props = defineProps({
  branch: { type: Object, required: true }
})

const activeTab = ref('branch')

const componentsMap = {
  branch: BranchInfo,
  staff: BranchStaff,
}

const currentComponent = computed(() => componentsMap[activeTab.value] || BranchInfo)

const currentProps = computed(() => {
  if (activeTab.value === 'staff') {
    return { staff: branchesStore.getBranchEmployees(props.branch.id) }
  }
  return { branch: props.branch }
})

const tabs = [
  { name: 'branch', icon: BranchIcon },
  { name: 'staff', icon: StaffIcon },
]

onMounted(async () => {
  await usersStore.loadUsers()
  await branchesStore.loadBranchEmployees(props.branch.id, usersStore.users)
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
