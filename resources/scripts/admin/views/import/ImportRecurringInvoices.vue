<template>
  <div class="max-w-5xl mx-auto">
    <BaseCard>
      <BaseCardHeader :title="$t('import.import_recurring_invoices.title')">
        <p class="text-sm text-gray-500 mb-3">
          {{ $t('import.import_recurring_invoices.description') }}
        </p>
      </BaseCardHeader>

      <BaseCardContent>
        <!-- Step 1: Download Template -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ $t('import.step_1_download') }}
          </h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ $t('import.download_template_description_recurring') }}
          </p>
          <BaseButton 
            variant="primary-outline" 
            @click="downloadCsvTemplate"
          >
            <BaseIcon name="DownloadIcon" class="mr-2" />
            {{ $t('import.download_csv_template') }}
          </BaseButton>
        </div>

        <!-- Step 2: Upload CSV -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ $t('import.step_2_upload') }}
          </h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ $t('import.upload_csv_description_recurring') }}
          </p>

          <div 
            class="border-2 border-dashed border-gray-300 rounded-md p-6 flex flex-col items-center"
            :class="{ 'border-primary-400 bg-primary-50': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onFileDropped"
          >
            <BaseIcon name="DocumentUploadIcon" class="w-12 h-12 text-gray-400 mb-4" />
            
            <div class="text-center">
              <p class="text-sm text-gray-500">
                {{ $t('import.drag_and_drop') }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                {{ $t('import.or') }}
              </p>
              <label 
                for="file-upload" 
                class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 cursor-pointer"
              >
                {{ $t('import.browse_files') }}
              </label>
              <input 
                id="file-upload" 
                type="file" 
                accept=".csv" 
                class="sr-only" 
                @change="onFileSelected"
              />
            </div>
          </div>

          <div v-if="selectedFile" class="mt-4 flex items-center text-sm text-gray-700">
            <BaseIcon name="DocumentIcon" class="w-5 h-5 mr-2 text-gray-400" />
            <span>{{ selectedFile.name }}</span>
            <button 
              class="ml-2 text-primary-500 hover:text-primary-600"
              @click="selectedFile = null"
            >
              <BaseIcon name="XCircleIcon" class="w-5 h-5" />
            </button>
          </div>

          <BaseButton 
            variant="primary" 
            class="mt-4"
            :disabled="!selectedFile || isUploading"
            @click="importCsvFile"
          >
            <span v-if="isUploading">
              <BaseIcon name="LoadingIcon" class="animate-spin mr-2" />
              {{ $t('import.uploading') }}
            </span>
            <span v-else>
              <BaseIcon name="UploadIcon" class="mr-2" />
              {{ $t('import.import_csv') }}
            </span>
          </BaseButton>
        </div>

        <!-- Results section -->
        <div v-if="importResults.success || importResults.errors.length" class="mt-8 p-4 border rounded-md">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ $t('import.import_results') }}
          </h3>
          
          <div v-if="importResults.success" class="mb-4 p-3 bg-green-50 text-green-700 rounded-md">
            <div class="flex">
              <BaseIcon name="CheckCircleIcon" class="w-5 h-5 mr-2" />
              <span>{{ $t('import.successfully_imported', { count: importResults.success }) }}</span>
            </div>
          </div>

          <div v-if="importResults.errors.length" class="mb-4 p-3 bg-red-50 text-red-700 rounded-md">
            <div class="flex mb-2">
              <BaseIcon name="ExclamationCircleIcon" class="w-5 h-5 mr-2" />
              <span>{{ $t('import.errors_found', { count: importResults.errors.length }) }}</span>
            </div>
            <ul class="ml-7 list-disc">
              <li v-for="(error, index) in importResults.errors" :key="index" class="mt-1 text-sm">
                {{ error }}
              </li>
            </ul>
          </div>
        </div>
      </BaseCardContent>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import Papa from 'papaparse'
import { useNotificationStore } from '@/scripts/stores/notification'
import { useRecurringInvoiceStore } from '@/scripts/admin/stores/recurring-invoice'
import { useCustomerStore } from '@/scripts/admin/stores/customer'
import { useTaxTypeStore } from '@/scripts/admin/stores/tax-type'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const notificationStore = useNotificationStore()
const recurringInvoiceStore = useRecurringInvoiceStore()
const customerStore = useCustomerStore()
const taxTypeStore = useTaxTypeStore()

const isDragging = ref(false)
const selectedFile = ref(null)
const isUploading = ref(false)
const importResults = ref({
  success: 0,
  errors: []
})

// Download CSV template
function downloadCsvTemplate() {
  // CSV Header
  const headers = [
    'customer_email',
    'start_date',
    'end_date',
    'frequency',
    'items',
    'quantities',
    'prices',
    'amounts',
    'taxes',
    'tax_types',
    'notes'
  ]
  
  // Example data row
  const exampleData = [
    'customer@example.com',
    '2025-05-14',
    '2026-05-14',
    'monthly',
    'Web Hosting,Domain Renewal',
    '1,1',
    '20,15',
    '20,15',
    '2,1.5',
    'VAT,GST',
    'Monthly subscription'
  ]
  
  // Create CSV content
  const csvContent = [
    headers.join(','),
    exampleData.join(',')
  ].join('\n')
  
  // Create a Blob and download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  
  link.setAttribute('href', url)
  link.setAttribute('download', 'recurring_invoice_import_template.csv')
  link.style.display = 'none'
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Handle file drop
function onFileDropped(event) {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file && file.type === 'text/csv') {
    selectedFile.value = file
  } else {
    notificationStore.showNotification({
      type: 'error',
      message: t('import.only_csv_allowed')
    })
  }
}

// Handle file select
function onFileSelected(event) {
  const file = event.target.files[0]
  if (file && file.type === 'text/csv') {
    selectedFile.value = file
  } else {
    notificationStore.showNotification({
      type: 'error',
      message: t('import.only_csv_allowed')
    })
  }
}

// Process CSV and import recurring invoices
async function importCsvFile() {
  if (!selectedFile.value) return
  
  isUploading.value = true
  importResults.value = {
    success: 0,
    errors: []
  }
  
  // Load customers for email lookup
  try {
    await customerStore.fetchCustomers({ limit: 10000 })
  } catch (error) {
    notificationStore.showNotification({
      type: 'error',
      message: t('import.error_loading_customers')
    })
    isUploading.value = false
    return
  }
  
  // Load tax types for validation
  try {
    await taxTypeStore.fetchTaxTypes()
  } catch (error) {
    notificationStore.showNotification({
      type: 'error',
      message: t('import.error_loading_tax_types')
    })
    isUploading.value = false
    return
  }
  
  // Parse CSV using PapaParse
  Papa.parse(selectedFile.value, {
    header: true,
    skipEmptyLines: true,
    complete: async function(results) {
      const { data, errors, meta } = results
      
      if (errors.length > 0) {
        for (const error of errors) {
          importResults.value.errors.push(`CSV parsing error: ${error.message} at row ${error.row}`)
        }
      }
      
      // Process each row
      for (let i = 0; i < data.length; i++) {
        const row = data[i]
        try {
          await processRecurringInvoiceRow(row, i + 2) // i+2 because of 0-index and header row
        } catch (error) {
          importResults.value.errors.push(`Row ${i + 2}: ${error.message || 'Unknown error'}`)
        }
      }
      
      isUploading.value = false
      
      if (importResults.value.success > 0) {
        notificationStore.showNotification({
          type: 'success',
          message: t('import.import_completed', { count: importResults.value.success })
        })
      } else if (importResults.value.errors.length > 0) {
        notificationStore.showNotification({
          type: 'error',
          message: t('import.import_failed')
        })
      }
    },
    error: function(error) {
      importResults.value.errors.push(`CSV parsing failed: ${error.message}`)
      isUploading.value = false
      notificationStore.showNotification({
        type: 'error',
        message: t('import.csv_parse_error')
      })
    }
  })
}

// Process a single recurring invoice row
async function processRecurringInvoiceRow(row, rowNumber) {
  // 1. Validate required fields
  if (!row.customer_email) {
    throw new Error('Customer email is required')
  }
  
  if (!row.start_date) {
    throw new Error('Start date is required')
  }
  
  if (!row.frequency) {
    throw new Error('Frequency is required')
  }
  
  if (!row.items) {
    throw new Error('At least one item is required')
  }
  
  // Validate frequency
  const validFrequencies = ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'half-yearly', 'yearly']
  if (!validFrequencies.includes(row.frequency.toLowerCase())) {
    throw new Error(`Invalid frequency "${row.frequency}". Valid values are: ${validFrequencies.join(', ')}`)
  }
  
  // 2. Find customer by email
  const customer = customerStore.customers.find(c => 
    c.email.trim().toLowerCase() === row.customer_email.trim().toLowerCase()
  )
  
  if (!customer) {
    throw new Error(`Customer with email "${row.customer_email}" not found`)
  }
  
  // 3. Parse items, quantities, prices, taxes
  const items = row.items.split(',').map(item => item.trim())
  const quantities = (row.quantities || '').split(',').map(q => parseFloat(q.trim()) || 1)
  const prices = (row.prices || '').split(',').map(p => parseFloat(p.trim()) || 0)
  const amounts = (row.amounts || '').split(',').map(a => parseFloat(a.trim()) || 0)
  const taxes = (row.taxes || '').split(',').map(t => parseFloat(t.trim()) || 0)
  const taxTypes = (row.tax_types || '').split(',').map(tt => tt.trim())
  
  // Ensure all arrays have the same length
  const itemCount = items.length
  while (quantities.length < itemCount) quantities.push(1)
  while (prices.length < itemCount) prices.push(0)
  while (amounts.length < itemCount) amounts.push(0)
  while (taxes.length < itemCount) taxes.push(0)
  while (taxTypes.length < itemCount) taxTypes.push('')
  
  // 4. Create invoice items
  const invoiceItems = []
  let subtotal = 0
  
  for (let i = 0; i < itemCount; i++) {
    // Calculate amount if not provided
    if (!amounts[i] && prices[i] && quantities[i]) {
      amounts[i] = prices[i] * quantities[i]
    }
    
    // Find tax type id if provided
    let taxTypeId = null
    if (taxTypes[i]) {
      const taxType = taxTypeStore.taxTypes.find(tt => 
        tt.name.trim().toLowerCase() === taxTypes[i].trim().toLowerCase()
      )
      if (taxType) {
        taxTypeId = taxType.id
      }
    }
    
    invoiceItems.push({
      name: items[i],
      description: items[i],
      quantity: quantities[i],
      price: prices[i],
      total: amounts[i],
      tax: taxes[i] || 0,
      tax_id: taxTypeId
    })
    
    subtotal += amounts[i]
  }
  
  // 5. Create recurring invoice object
  const recurringInvoiceData = {
    customer_id: customer.id,
    starts_at: row.start_date,
    send_automatically: true,
    frequency: row.frequency.toLowerCase(),
    status: 'ACTIVE',
    notes: row.notes || '',
    items: invoiceItems,
    sub_total: subtotal,
    total: subtotal + invoiceItems.reduce((sum, item) => sum + item.tax, 0),
    tax_per_item: 'YES',
    discount_per_item: 'NO',
    discount: 0,
    discount_type: 'fixed'
  }
  
  // Add end date if provided
  if (row.end_date) {
    recurringInvoiceData.ends_at = row.end_date
  }
  
  // 6. Submit the recurring invoice
  try {
    await recurringInvoiceStore.addRecurringInvoice(recurringInvoiceData)
    importResults.value.success++
  } catch (error) {
    throw new Error(error.response?.data?.message || 'Failed to create recurring invoice')
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style> 