<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/api.js';

const logs = ref([]);
const loading = ref(false);
const error = ref(null);

const currentPage = ref(1);
const pageSize = ref(10); // Number of logs per page
const showArchive = ref(false);

const fetchLogs = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/logs');
    logs.value = response.data;
  } catch (err) {
    console.error('Failed to fetch logs:', err);
    error.value = 'Nepodarilo sa načítať logy.';
  } finally {
    loading.value = false;
  }
};

const filteredLogs = computed(() => {
  if (showArchive.value) {
    return logs.value;
  }
  const oneHourAgo = new Date(Date.now() - 60 * 60 * 1000);
  return logs.value.filter(log => {
      // Assuming log.timestamp is "YYYY-MM-DD HH:mm:ss" which might need adaptation for some browsers
      // Replacing space with T to ensure ISO format parsing compatibility
      const logDate = new Date(log.timestamp.replace(' ', 'T'));
      return logDate > oneHourAgo;
  });
});

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / pageSize.value));

const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLogs.value.slice(start, end);
});

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const tryParseJSON = (str) => {
  try {
    const obj = JSON.parse(str);
    if (obj && typeof obj === 'object') {
      return JSON.stringify(obj, null, 2);
    }
  } catch (e) {
    // Not JSON
  }
  return str;
};

const isJSON = (str) => {
  try {
    const obj = JSON.parse(str);
    return obj && typeof obj === 'object';
  } catch (e) {
    return false;
  }
};

onMounted(() => {
  fetchLogs();
});
</script>

<template>
  <div class="tab-content">
    <div class="header">
        <b>Logy</b>
        <div class="toggle-container">
          <span>Posledná hodina</span>
          <label class="switch">
            <input type="checkbox" v-model="showArchive">
            <span class="slider round"></span>
          </label>
          <span>Archív</span>
        </div>
    </div>
    
    <div v-if="loading">Načítavam...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="filteredLogs.length" class="table-container">
      <table>
        <thead>
          <tr>
            <th class="timestamp-col">Dátum a čas</th>
            <th>Akcia</th>
            <th>Cieľ</th>
            <th>Správa</th>
            <th>Používateľ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in paginatedLogs" :key="log.id">
            <td class="timestamp-col">{{ log.timestamp }}</td>
            <td>{{ log.action }}</td>
            <td>{{ log.targetType }} ({{ log.targetId }})</td>
            <td class="message-cell">
              <pre v-if="isJSON(log.message)">{{ tryParseJSON(log.message) }}</pre>
              <span v-else>{{ log.message }}</span>
            </td>
            <td>{{ log.userId }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      Žiadne logy za vybrané obdobie.
    </div>

     <div v-if="filteredLogs.length > 0" class="pagination">
      <button @click="prevPage" :disabled="currentPage === 1" class="btn-small">Predchádzajúca</button>
      <span>Strana {{ currentPage }} z {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-small">Nasledujúca</button>
    </div>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  height: 100%;
  overflow: hidden;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-right: 1em;
}

.toggle-container {
  display: flex;
  align-items: center;
  gap: 0.5em;
  font-size: 0.9em;
}

/* Toggle Switch Styling */
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 20px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--color-bg-secondary);
  border: 1px solid var(--color-border, #ccc);
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 14px;
  width: 14px;
  left: 3px;
  bottom: 2px;
  background-color: var(--color-text);
  transition: .4s;
}

input:checked + .slider {
  background-color: var(--color-accent);
}

input:focus + .slider {
  box-shadow: 0 0 1px var(--color-accent);
}

input:checked + .slider:before {
  transform: translateX(20px);
  background-color: white;
}

.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

.table-container {
  overflow-y: auto;
  flex: 1;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9em;
}

th, td {
  text-align: left;
  padding: 8px;
  border-bottom: 1px solid var(--color-border, #ccc);
  vertical-align: top;
}

.message-cell pre {
  margin: 0;
  white-space: pre-wrap;
  font-family: inherit;
  font-size: 0.9em;
  background-color: var(--color-bg-secondary, #fafafa);
  padding: 5px;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
}

th {
  background-color: var(--color-bg);
  position: sticky;
  top: 0;
  z-index: 1; 
}

.error {
  color: red;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1em;
  padding: 0.5em;
  border-top: 1px solid var(--color-border, #ccc);
}

.btn-small {
  padding: 5px 10px;
  border: 1px solid var(--color-border, #ccc);
  background-color: var(--color-bg-secondary);
  color: var(--color-text);
  border-radius: 4px;
  cursor: pointer;
}

.btn-small:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-small:hover:not(:disabled) {
  background-color: var(--color-accent);
  color: white;
}

.timestamp-col {
  white-space: nowrap;
  width: 1%; /* Shrink to fit content */
}
</style>
