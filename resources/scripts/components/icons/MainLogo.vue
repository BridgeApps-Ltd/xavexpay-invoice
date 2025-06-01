<template>
  <svg viewBox="0 0 372 81.333336" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop :stop-color="darkColor" offset="0%" />
        <stop :stop-color="lightColor" offset="100%" />
      </linearGradient>
    </defs>
    <image
      width="372"
      height="81.333336"
      preserveAspectRatio="none"
      :xlink:href="logoUrl"
      fill="url(#logoGradient)"
    />
  </svg>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  darkColor: {
    type: String,
    default: 'rgba(var(--color-primary-500), var(--tw-text-opacity))',
  },
  lightColor: {
    type: String,
    default: 'rgba(var(--color-primary-400), var(--tw-text-opacity))',
  },
})

const logoUrl = computed(() => {
  return new URL('../../../../public/storage/invvex-logo-white.png', import.meta.url).href
})
</script>
