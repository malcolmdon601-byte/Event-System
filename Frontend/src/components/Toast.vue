<template>
  <transition name="fade">
    <div v-if="message" class="fixed bottom-6 right-6 z-50 rounded-xl px-4 py-3 shadow-card text-sm font-medium"
         :class="type === 'error' ? 'bg-danger text-white' : 'bg-navy text-white'">
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
defineProps({ message: String, type: { type: String, default: 'success' } })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
