<template>
  <div class="max-w-5xl mx-auto">
    <BaseCard>
      <BaseCardHeader :title="$t('import.import_recurring_invoices.title')">
        <p class="text-sm text-gray-500 mb-3">
          {{ $t('import.import_recurring_invoices.description') }}
        </p>
      </BaseCardHeader>

      <BaseCardContent>
        <div class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-md">
          <div class="text-center">
            <BaseIcon name="DocumentDuplicateIcon" class="w-10 h-10 mx-auto text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              Import Recurring Invoices
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Functionality will be added soon
            </p>
          </div>
        </div>
      </BaseCardContent>
    </BaseCard>
  </div>
</template>

<script setup>
import { ref } from 'vue'
</script> 