<template>
  <div v-if="showWarning" class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
    <div class="flex">
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      </div>
      <div class="ml-3">
        <p class="text-sm text-red-700">
          Database is not configured for this company. 
          <router-link 
            :to="{ name: 'settings.database' }" 
            class="font-medium underline text-red-700 hover:text-red-600"
          >
            Configure database settings
          </router-link>
        </p>
      </div>
      <div class="ml-auto pl-3">
        <div class="-mx-1.5 -my-1.5">
          <button
            @click="dismissWarning"
            class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600"
          >
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useCompanyStore } from '@/admin/stores/company'
import { useRouter } from 'vue-router'

export default {
  name: 'DatabaseWarningBanner',
  setup() {
    const showWarning = ref(false)
    const companyStore = useCompanyStore()
    const router = useRouter()

    const checkDatabaseSettings = async () => {
      try {
        const response = await fetch('/api/v1/company/database-settings/check', {
          headers: {
            'company': companyStore.company.id,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        const data = await response.json()
        showWarning.value = !data.hasSettings
      } catch (error) {
        console.error('Error checking database settings:', error)
        // Don't show warning on error to avoid false positives
        showWarning.value = false
      }
    }

    const dismissWarning = () => {
      showWarning.value = false
    }

    onMounted(() => {
      checkDatabaseSettings()
    })

    return {
      showWarning,
      dismissWarning
    }
  }
}
</script> 