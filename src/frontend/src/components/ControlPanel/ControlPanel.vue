<script setup>
import { ref, computed } from 'vue'

import FilterDistrict from './FilterDistrict.vue'
import FilterIndustry from './FilterIndustry.vue'
import FilterStaff from './FilterStaff.vue'
import FilterOther from './FilterOther.vue'
import TabInfoBranch from './TabInfoBranch.vue'
import TabInfoPersonal from './TabInfoPersonal.vue'
import TabNotes from './TabNotes.vue'
import TabLogViewer from './TabLogViewer.vue'
import TabSettings from './TabSettings.vue'

import DistrictIcon from '../../assets/icons/ControlPanel/district.svg'
import IndustryIcon from '../../assets/icons/ControlPanel/industry.svg'
import StaffIcon from '../../assets/icons/ControlPanel/staff.svg'
import OthersIcon from '../../assets/icons/ControlPanel/others.svg'

import BranchIcon from '../../assets/icons/ControlPanel/branch.svg'
import InfoIcon from '../../assets/icons/ControlPanel/info.svg'
import NotesIcon from '../../assets/icons/ControlPanel/notes.svg'
import LogsIcon from '../../assets/icons/ControlPanel/archive.svg'
import SettingsIcon from '../../assets/icons/ControlPanel/settings.svg'

import LogOutButton from '../../assets/icons/ControlPanel/logout.svg'

//dummy data
const mockBranch = { name: "Pobočka Bratislava", staff: [{ name: "Jozef" }], resources: [{ type: "Printer", count: 2 }] }
const mockUser = { name: "Marek", role: "Admin", position: "Vedúci" }
const mockNotes = [{ text: "note 1" }, { text: "note 2" }]
const mockLogs = [{ action: "Vytvorenie pobočky", date: "2025-10-08" }]

const filterTabs = [
  //filters
  { name: 'districts', icon: DistrictIcon, component: FilterDistrict },
  { name: 'industries', icon: IndustryIcon, component: FilterIndustry },
  { name: 'staff', icon: StaffIcon, component: FilterStaff },
  { name: 'other', icon: OthersIcon, component: FilterOther }
]

const dataTabs = [
  //data
  { name: 'branch', icon: BranchIcon, component: TabInfoBranch },
  { name: 'info', icon: InfoIcon, component: TabInfoPersonal },
  { name: 'notes', icon: NotesIcon, component: TabNotes },
  { name: 'logs', icon: LogsIcon, component: TabLogViewer },
  { name: 'settings', icon: SettingsIcon, component: TabSettings }
]

const allTabs = [...filterTabs, ...dataTabs]
const activeTab = ref(null)

const currentTabComponent = computed(() => {
  const tab = allTabs.find(t => t.name === activeTab.value)
  return tab ? tab.component : null
})

const currentProps = computed(() => {
  switch (activeTab.value) {
    case 'branch': return { branch: mockBranch }
    case 'info': return { user: mockUser }
    case 'notes': return { notes: mockNotes }
    case 'logs': return { logs: mockLogs }
    case 'settings': return { settings: {} }
    default: return {}
  }
})


const toggleTab = (tabName) => {
  activeTab.value = activeTab.value === tabName ? null : tabName
}
</script>

<template>
  <div class="control-panel-wrapper">
    <div class="control-panel">

      <div class="tabs">

        <button v-for="tab in filterTabs" :key="tab.name" :class="{ active: activeTab === tab.name }"
          @click="toggleTab(tab.name)">
          <component :is="tab.icon" class="icon" />
        </button>

        <div class="tab-separator"></div>

        <button v-for="tab in dataTabs" :key="tab.name" :class="{ active: activeTab === tab.name }"
          @click="toggleTab(tab.name)">
          <component :is="tab.icon" class="icon" />
        </button>

        <button class="bottom-element btn" @click="$emit('logout')">
          <LogOutButton class="icon" />
        </button>

      </div>

      <transition name="slide-fade">
        <component v-if="currentTabComponent" :is="currentTabComponent" v-bind="currentProps" :key="activeTab"
          class="panel-content" />
      </transition>

    </div>
  </div>
</template>

<style scoped>
.control-panel-wrapper {
  position: absolute;
  top: .5em;
  left: .5em;
  z-index: 1000;
  width: auto;
  max-height: 100vh;
}

.control-panel {
  position: relative;
  background: var(--color-bg);
  border-radius: 1.5em;
  padding: 10px 10px 5px 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: flex-start;
  width: auto;
  height: calc(100vh - 1em);

}

.tabs {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6em;
  height: 100%;
}

.tab-separator {
  width: 70%;
  height: 1px;
  background-color: var(--color-accent);
  opacity: 0.3;
  margin: 0.5em 0;
}

.panel-content {
  position: absolute;
  left: 100%;
  top: 0;
  margin-left: -2em;
  padding: 15px;
  padding-left: 2.5em;
  width: 50em;
  height: 100%;
  background: var(--color-bg-secondary);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 0.8em;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: -1;
}

.panel-content input,
.panel-content select {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid var(--color-accent);
  background: var(--color-bg-secondary);
  color: var(--color-text);
}

.panel-content label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}


.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(-10px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}


.bottom-element {
  margin-top: auto;
  margin-bottom: .5em;
}
</style>