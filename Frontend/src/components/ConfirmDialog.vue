<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-navy/40 backdrop-blur-sm px-4">
    <div class="card max-w-sm w-full">
      <h3 class="font-bold text-lg">{{ title }}</h3>
      <p class="text-sm text-navy/60 mt-2">{{ message }}</p>
      <div class="flex justify-end gap-3 mt-6">
        <button class="btn-outline" @click="$emit('cancel')">Cancel</button>
        <button class="btn-primary !bg-danger" @click="$emit('confirm')">{{ confirmLabel }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ open: Boolean, title: String, message: String, confirmLabel: { type: String, default: 'Delete' } })
defineEmits(['confirm', 'cancel'])
</script>
