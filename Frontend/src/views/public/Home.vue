<template>
  <div>
    <PublicNav />

    <section class="bg-navy text-white">
      <div class="max-w-7xl mx-auto px-6 py-24 grid md:grid-cols-2 gap-12 items-center">
        <div>
          <p class="text-gold font-semibold tracking-wide text-sm mb-4 uppercase">Event Management & Booking</p>
          <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">
            Creating unforgettable moments.<br /><span class="text-gold">Managed professionally.</span>
          </h1>
          <p class="text-white/70 mt-6 max-w-md">
            From first enquiry to final invoice — EventFlow brings your weddings, conferences and celebrations
            into one organised, transparent system.
          </p>
          <div class="flex flex-wrap gap-4 mt-8">
            <router-link to="/request-quote" class="btn-gold">Plan Your Event</router-link>
            <router-link to="/request-quote" class="btn-outline !border-white/30 !text-white hover:!bg-white/10">Request a Quote</router-link>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="card !bg-white/5 !border-white/10 text-white">
            <p class="text-3xl font-extrabold text-gold">250+</p>
            <p class="text-sm text-white/60 mt-1">Events delivered</p>
          </div>
          <div class="card !bg-white/5 !border-white/10 text-white">
            <p class="text-3xl font-extrabold text-gold">98%</p>
            <p class="text-sm text-white/60 mt-1">Client satisfaction</p>
          </div>
          <div class="card !bg-white/5 !border-white/10 text-white">
            <p class="text-3xl font-extrabold text-gold">40+</p>
            <p class="text-sm text-white/60 mt-1">Trusted vendors</p>
          </div>
          <div class="card !bg-white/5 !border-white/10 text-white">
            <p class="text-3xl font-extrabold text-gold">12</p>
            <p class="text-sm text-white/60 mt-1">Years in business</p>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 py-20">
      <h2 class="text-2xl font-extrabold text-center">How it works</h2>
      <p class="text-navy/50 text-center mt-2 max-w-xl mx-auto">
        One connected pipeline — from your first enquiry to a completed, paid, celebrated event.
      </p>
      <div class="grid md:grid-cols-4 gap-6 mt-12">
        <div v-for="(step, i) in steps" :key="step.title" class="card">
          <div class="w-9 h-9 rounded-full bg-gold text-navy font-bold flex items-center justify-center text-sm">{{ i + 1 }}</div>
          <h3 class="font-bold mt-4">{{ step.title }}</h3>
          <p class="text-sm text-navy/50 mt-1">{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <section class="bg-champagne/40 py-20">
      <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-2xl font-extrabold text-center">What our clients say</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
          <div v-for="t in testimonials" :key="t.name" class="card">
            <p class="text-navy/70 text-sm">"{{ t.quote }}"</p>
            <p class="font-semibold mt-4 text-sm">{{ t.name }}</p>
            <p class="text-xs text-navy/40">{{ t.event }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-4xl mx-auto px-6 py-20 text-center">
      <h2 class="text-2xl font-extrabold">Ready to plan something unforgettable?</h2>
      <p class="text-navy/50 mt-2">Tell us about your event and we'll send a tailored quotation within 48 hours.</p>
      <router-link to="/request-quote" class="btn-gold mt-6 inline-flex">Request a Quote</router-link>
    </section>

    <PublicFooter />
  </div>
</template>

<script setup>
import PublicNav from '@/components/PublicNav.vue'
import PublicFooter from '@/components/PublicFooter.vue'

const steps = [
  { title: 'Enquiry', desc: 'Tell us your date, guest count and vision.' },
  { title: 'Quotation', desc: 'Receive a clear, itemised quote to review.' },
  { title: 'Confirm', desc: 'Accept and pay a deposit to lock your date.' },
  { title: 'Celebrate', desc: 'We handle staff, vendors and logistics.' },
]

const testimonials = [
  { name: 'Sarah N.', event: 'Wedding, Kampala', quote: 'Every detail was tracked and shared with us — no more WhatsApp chaos.' },
  { name: 'NovaTech Ltd', event: 'Corporate Summit', quote: 'The quotation and invoicing process was fast and completely transparent.' },
  { name: 'James K.', event: 'Anniversary Party', quote: 'From enquiry to the big day, everything felt organised and professional.' },
]
</script>
