export default {
  domain: process.env.VUE_APP_PAYMENT_DOMAIN,
  apiKey: process.env.VUE_APP_PAYMENT_API_KEY,
  tenantId: process.env.VUE_APP_PAYMENT_TENANT_ID || 1004,
  context: process.env.VUE_APP_PAYMENT_CONTEXT || 'Invoice',
  status: process.env.VUE_APP_PAYMENT_STATUS || 'CREATED'
} 
