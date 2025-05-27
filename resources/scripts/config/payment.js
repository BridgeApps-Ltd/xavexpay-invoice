import { useGlobalStore } from '@/scripts/admin/stores/global'

export default {
  get domain() {
    const globalStore = useGlobalStore()
    return globalStore.payment_settings?.payment_domain_url || process.env.VUE_APP_PAYMENT_DOMAIN
  },
  get apiKey() {
    const globalStore = useGlobalStore()
    return globalStore.payment_settings?.payment_api_key || process.env.VUE_APP_PAYMENT_API_KEY
  },
  get tenantId() {
    const globalStore = useGlobalStore()
    return globalStore.payment_settings?.payment_tenant_id || process.env.VUE_APP_PAYMENT_TENANT_ID || 1004
  },
  get context() {
    const globalStore = useGlobalStore()
    return globalStore.payment_settings?.payment_context || process.env.VUE_APP_PAYMENT_CONTEXT || 'Invoice'
  },
  get status() {
    const globalStore = useGlobalStore()
    return globalStore.payment_settings?.payment_status || process.env.VUE_APP_PAYMENT_STATUS || 'CREATED'
  }
} 
