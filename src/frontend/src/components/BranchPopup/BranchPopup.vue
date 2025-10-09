<script setup>
import { ref, computed } from 'vue'
import BranchInfo from './BranchInfo.vue'
import BranchStaff from './BranchStaff.vue'
import BranchResources from './BranchResources.vue'

import BranchIcon from '../../assets/icons/BranchPopup/branch.svg'
import StaffIcon from '../../assets/icons/BranchPopup/staff.svg'
import ResourcesIcon from '../../assets/icons/BranchPopup/resources.svg'

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
      return { staff: props.branch?.staff || [] }
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