<template>
  <span class="badge" :class="classes">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ status: { type: String, required: true } })

const map = {
  enquiry: 'bg-navy/10 text-navy', quotation: 'bg-champagne text-navy', deposit: 'bg-gold/20 text-navy',
  confirmed: 'bg-success/15 text-success', planning: 'bg-warning/15 text-warning', ready: 'bg-success/15 text-success',
  completed: 'bg-navy text-white', cancelled: 'bg-danger/15 text-danger',
  draft: 'bg-navy/10 text-navy', sent: 'bg-champagne text-navy', viewed: 'bg-gold/20 text-navy',
  accepted: 'bg-success/15 text-success', rejected: 'bg-danger/15 text-danger', expired: 'bg-navy/10 text-navy/50',
  unpaid: 'bg-danger/15 text-danger', partial: 'bg-warning/15 text-warning', paid: 'bg-success/15 text-success',
  todo: 'bg-navy/10 text-navy', in_progress: 'bg-warning/15 text-warning',
  active: 'bg-success/15 text-success', inactive: 'bg-navy/10 text-navy/50',
  pending: 'bg-warning/15 text-warning', failed: 'bg-danger/15 text-danger',
}

const classes = computed(() => map[props.status] || 'bg-navy/10 text-navy')
const label = computed(() => props.status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()))
</script>
