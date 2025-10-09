<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  resources: {
    type: Array,
    required: false,
    default: () => []
  }
})


const testResources = ref([
  { type: 'Printer', count: 3 },
  { type: 'Meeting Room', count: 2 },
  { type: 'Projector', count: 1 }
])

const activeResources = computed(() =>
  props.resources && props.resources.length ? props.resources : testResources.value
)
</script>

<template>
  <div>
    <div><b>Počet rôznych zdrojov:</b> {{ activeResources.length }}</div>

    <table>
      <thead>
        <tr>
          <th>Zdroj</th>
          <th>Počet</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(res, i) in activeResources" :key="i">
          <td>{{ res.type }}</td>
          <td>{{ res.count }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
