<script setup>
import { marked } from 'marked';

const props = defineProps({
  notes: Array
})

const renderMarkdown = (text) => {
  if (!text) return '';
  return marked.parse(text);
}
</script>

<template>
  <div class="tab-content">
    <b>Poznámky pobočky</b>
    <div v-if="notes?.length" class="notes-container">
      <div v-for="(note, i) in notes" :key="i" class="note-item">
        <div v-html="renderMarkdown(note.text)" class="markdown-content"></div>
        <hr v-if="i < notes.length - 1" class="separator" />
      </div>
    </div>
    <div v-else>
      Žiadne poznámky
    </div>
  </div>
</template>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  height: 100%;
}

.notes-container {
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1em;
}

.note-item {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
}

.separator {
  border: 0;
  border-top: 1px solid var(--color-border, #ccc);
  margin: 0;
  opacity: 0.5;
}

.markdown-content :deep(p) {
  margin: 0;
}

.markdown-content :deep(ul), .markdown-content :deep(ol) {
  margin: 0.5em 0;
  padding-left: 1.5em;
}

.markdown-content :deep(strong), .markdown-content :deep(b) {
  font-weight: bold;
}
</style>
