<template>
  <div class="max-w-5xl mx-auto">
    <BaseCard>
      <BaseCardHeader :title="$t('import.import_invoices.title')">
        <p class="text-sm text-gray-500 mb-3">
          {{ $t('import.import_invoices.description') }}
        </p>
      </BaseCardHeader>

      <BaseCardContent>
        <!-- Step 1: Download Template -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ $t('import.step_1_download') }}
          </h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ $t('import.download_template_description') }}
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
            {{ $t('import.upload_csv_description') }}
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
import { useInvoiceStore } from '@/scripts/admin/stores/invoice'
import { useCustomerStore } from '@/scripts/admin/stores/customer'
import { useTaxTypeStore } from '@/scripts/admin/stores/tax-type'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const notificationStore = useNotificationStore()
const invoiceStore = useInvoiceStore()
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
  // CSV Header - removed amount since we'll calculate it
  const headers = [
    'invoice_number',
    'customer_email',
    'invoice_date',
    'due_date',
    'item_name',
    'quantity',
    'price',
    'tax_type',
    'notes'
  ]
  
  // Example data rows with multiple items for same invoice
  const exampleData = [
    // First invoice, first item
    [
      '1',
      'customer@example.com',
      '2025-05-14',
      '2025-06-14',
      'Web Design',
      '1',
      '100',
      'VAT',
      'Thank you for your business'
    ].join(','),
    // First invoice, second item
    [
      '1',
      'customer@example.com',
      '2025-05-14',
      '2025-06-14',
      'Special Consulting',
      '1',
      '200',
      'GST',
      'Consulting fee'
    ].join(','),
    // Second invoice with single item
    [
      '2',
      'client@company.com',
      '2025-05-15',
      '2025-06-15',
      'Sick Note',
      '1',
      '150',
      'VAT',
      'Special Service over call'
    ].join(',')
  ]
  
  // Create CSV content
  const csvContent = [
    headers.join(','),
    ...exampleData
  ].join('\n')
  
  // Create a Blob and download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  
  link.setAttribute('href', url)
  link.setAttribute('download', 'invoice_import_template.csv')
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

// Process CSV and import invoices
async function importCsvFile() {
  if (!selectedFile.value) {
    return
  }

  isUploading.value = true
  importResults.value = {
    success: 0,
    errors: []
  }

  // Load customers for email lookup
  try {
    console.log('Loading customers...')
    await customerStore.fetchCustomers({ limit: 10000 })
    console.log('Customers loaded successfully')
  } catch (error) {
    console.error('Error loading customers:', error)
    notificationStore.showNotification({
      type: 'error',
      message: t('import.error_loading_customers')
    })
    isUploading.value = false
    return
  }
   
  // Load tax types for validation
  try {
    console.log('Loading tax types...')
    await taxTypeStore.fetchTaxTypes()
    console.log('Tax types loaded successfully')
  } catch (error) {
    console.error('Error loading tax types:', error)
    notificationStore.showNotification({
      type: 'error',
      message: t('import.error_loading_tax_types')
    })
    isUploading.value = false
    return
  }

  // Group rows by invoice number
  const invoiceGroups = new Map()

  Papa.parse(selectedFile.value, {
    header: true,
    skipEmptyLines: true,
    complete: async function(results) {
      try {
        console.log('CSV parsing completed. Processing rows...')
        console.log('Total rows found:', results.data.length)

        if (results.errors.length > 0) {
          console.error('CSV parsing errors:', results.errors)
          results.errors.forEach(error => {
            importResults.value.errors.push(`CSV parsing error at row ${error.row}: ${error.message}`)
          })
        }

        // Group rows by invoice number
        results.data.forEach((row, index) => {
          console.log(`Processing row ${index + 1}:`, row)
          if (!invoiceGroups.has(row.invoice_number)) {
            invoiceGroups.set(row.invoice_number, {
              customer_email: row.customer_email,
              invoice_date: row.invoice_date,
              due_date: row.due_date,
              notes: row.notes,
              items: []
            })
          }
          
          // Add item to the invoice group
          invoiceGroups.get(row.invoice_number).items.push({
            name: row.item_name,
            quantity: parseFloat(row.quantity) || 1,
            price: parseFloat(row.price) || 0,
            amount: parseFloat(row.amount) || 0,
            tax: parseFloat(row.tax) || 0,
            tax_type: row.tax_type || ''
          })
        })

        console.log('Invoice groups created:', invoiceGroups.size)

        // Process each invoice group
        for (const [invoiceNumber, groupData] of invoiceGroups) {
          console.log(`Processing invoice #${invoiceNumber}...`)
          try {
            await processInvoiceGroup(groupData, invoiceNumber)
            importResults.value.success++
            console.log(`Successfully processed invoice #${invoiceNumber}`)
          } catch (error) {
            const errorMessage = `Error in invoice #${invoiceNumber}: ${error.message}`
            console.error(errorMessage, error)
            if (error.response?.data?.message) {
              console.error('API Error details:', error.response.data)
            }
            importResults.value.errors.push(errorMessage)
          }
        }

        if (importResults.value.success > 0) {
          console.log(`Import completed. Successfully imported ${importResults.value.success} invoices`)
          notificationStore.showNotification({
            type: 'success',
            message: t('import.import_completed', {
              count: importResults.value.success
            })
          })
        }

        if (importResults.value.errors.length > 0) {
          console.log(`Import completed with ${importResults.value.errors.length} errors`)
          notificationStore.showNotification({
            type: 'error',
            message: t('import.import_failed')
          })
        }
      } catch (error) {
        console.error('Error during import process:', error)
        notificationStore.showNotification({
          type: 'error',
          message: t('import.import_failed')
        })
      } finally {
        isUploading.value = false
      }
    },
    error: function(error) {
      const errorMessage = `CSV parsing failed: ${error.message}`
      console.error(errorMessage, error)
      importResults.value.errors.push(errorMessage)
      isUploading.value = false
      notificationStore.showNotification({
        type: 'error',
        message: t('import.csv_parse_error')
      })
    }
  })
}

// Process a single invoice group
async function processInvoiceGroup(groupData, invoiceNumber) {
  console.log(`Processing invoice group #${invoiceNumber}:`, groupData)
  
  // 1. Validate required fields
  if (!groupData.customer_email) {
    throw new Error('Customer email is required')
  }
  
  if (!groupData.invoice_date) {
    throw new Error('Invoice date is required')
  }
  
  if (!groupData.due_date) {
    throw new Error('Due date is required')
  }
  
  if (!groupData.items || groupData.items.length === 0) {
    throw new Error('At least one item is required')
  }
  
  // 2. Find customer by email
  const customer = customerStore.customers.find(c => 
    c.email.trim().toLowerCase() === groupData.customer_email.trim().toLowerCase()
  )
  
  if (!customer) {
    console.error(`Customer not found for email: ${groupData.customer_email}`)
    throw new Error(`Customer with email "${groupData.customer_email}" not found`)
  }
  console.log(`Found customer:`, customer)

  // Get next invoice number from API
  console.log('Getting next invoice number...')
  let nextInvoiceNumber
  try {
    const response = await invoiceStore.getNextNumber({ userId: customer.id })
    nextInvoiceNumber = response.data.nextNumber
    console.log('Got next invoice number:', nextInvoiceNumber)
  } catch (error) {
    console.error('Error getting next invoice number:', error)
    throw new Error('Failed to get next invoice number')
  }
  
  // 3. Create invoice items
  const invoiceItems = []
  let subtotal = 0
  
  for (const item of groupData.items) {
    console.log(`Processing item:`, item)
    
    // Find tax type if specified
    let taxTypeId = null
    let taxRate = 0
    if (item.tax_type) {
      const taxType = taxTypeStore.taxTypes.find(tt => 
        tt.name.trim().toLowerCase() === item.tax_type.trim().toLowerCase()
      )
      if (taxType) {
        taxTypeId = taxType.id
        taxRate = parseFloat(taxType.percent) || 0
        console.log(`Found tax type:`, taxType, `with rate:`, taxRate)
      } else {
        console.warn(`Tax type not found: ${item.tax_type}`)
      }
    }
    
    // Convert price from pounds to pence (multiply by 100)
    const quantity = parseFloat(item.quantity) || 1
    const priceInPounds = parseFloat(item.price) || 0
    const priceInPence = Math.round(priceInPounds * 100)
    const amount = quantity * priceInPence
    
    // Calculate tax amount based on the tax rate
    const taxAmount = Math.round((amount * taxRate) / 100)
    
    subtotal += amount
    
    console.log(`Item calculations:`, {
      priceInPounds,
      priceInPence,
      quantity,
      amount,
      taxRate,
      taxAmount
    })
    
    // Calculate item discount value
    const itemDiscountVal = 0 // No discount by default
    const exchangeRate = 1 // Default exchange rate
    
    invoiceItems.push({
      name: item.name,
      description: item.name,
      quantity: quantity,
      price: priceInPence,
      total: amount,
      tax: taxAmount,
      tax_type_id: taxTypeId,
      discount: 0,
      discount_val: itemDiscountVal,
      discount_type: 'fixed',
      base_price: priceInPence * exchangeRate,
      base_discount_val: itemDiscountVal * exchangeRate,
      base_tax: taxAmount * exchangeRate,
      base_total: amount * exchangeRate,
      company_id: customer.company_id,
      exchange_rate: exchangeRate
    })
  }
  
  // Calculate total tax
  const totalTax = invoiceItems.reduce((sum, item) => sum + (item.tax || 0), 0)
  const exchangeRate = 1 // Default exchange rate
  
  // 4. Create invoice data
  const newInvoiceData = {
    invoice_number: nextInvoiceNumber,
    customer_id: customer.id,
    company_id: customer.company_id,
    invoice_date: groupData.invoice_date,
    due_date: groupData.due_date,
    notes: groupData.notes || '',
    items: invoiceItems,
    sub_total: subtotal,
    total: subtotal + totalTax,
    tax: totalTax,
    tax_per_item: 'YES',
    discount_per_item: 'NO',
    discount: 0,
    discount_type: 'fixed',
    discount_val: 0,
    template_name: 'invoice1',
    status: 'DRAFT',
    exchange_rate: exchangeRate,
    base_discount_val: 0,
    base_sub_total: subtotal * exchangeRate,
    base_total: (subtotal + totalTax) * exchangeRate,
    base_tax: totalTax * exchangeRate,
    currency_id: customer.currency_id || 1
  }
  
  console.log(`Sending invoice data to API:`, newInvoiceData)
  
  try {
    // 5. Send to API
    await invoiceStore.addInvoice(newInvoiceData)
    console.log(`Successfully created invoice #${invoiceNumber}`)
  } catch (error) {
    console.error(`API error while creating invoice #${invoiceNumber}:`, error)
    if (error.response?.data) {
      console.error('API error details:', error.response.data)
    }
    throw error
  }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style> 