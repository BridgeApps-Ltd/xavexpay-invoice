<template>
  <div>
    <header class="bg-white shadow">
      <!-- ... existing header content ... -->
    </header>
    <DatabaseWarningBanner />
    <!-- ... rest of the template ... -->
  </div>
</template>

<script>
import DatabaseWarningBanner from '@/admin/components/DatabaseWarningBanner.vue'

export default {
  components: {
    DatabaseWarningBanner
  }
  // ... rest of the component ...
}
</script> 