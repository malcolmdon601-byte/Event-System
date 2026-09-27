<template>
  <div class="card flex items-start justify-between">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-navy/50">{{ label }}</p>
      <p class="text-2xl font-extrabold mt-1">{{ value }}</p>
      <p v-if="hint" class="text-xs text-navy/40 mt-1">{{ hint }}</p>
    </div>
    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" :class="accent ? 'bg-gold/15 text-gold' : 'bg-navy/5 text-navy/50'">
      {{ icon }}
    </div>
  </div>
</template>

<script setup>
defineProps({ label: String, value: [String, Number], hint: String, icon: { type: String, default: '●' }, accent: { type: Boolean, default: false } })
</script>
