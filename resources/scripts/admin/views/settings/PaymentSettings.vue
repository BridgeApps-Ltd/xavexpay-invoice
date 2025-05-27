<template>
  <form action="" class="relative" @submit.prevent="updatePaymentSettings">
    <BaseSettingCard
      :title="$t('settings.menu_title.payment_settings')"
      :description="$t('settings.payment_settings.description')"
    >
      <BaseInputGrid class="mt-5">
        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_manager')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_manager.$error && v$.payment_manager.$errors[0].$message"
          required
        >
          <BaseMultiselect
            v-model="settingsForm.payment_manager"
            :content-loading="isFetchingInitialData"
            :options="paymentManagers"
            label="name"
            value-prop="value"
            :searchable="true"
            track-by="name"
            :invalid="v$.payment_manager.$error"
            class="w-full"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_domain_url')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_domain_url.$error && v$.payment_domain_url.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="settingsForm.payment_domain_url"
            :content-loading="isFetchingInitialData"
            :invalid="v$.payment_domain_url.$error"
            type="text"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_api_key')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_api_key.$error && v$.payment_api_key.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="settingsForm.payment_api_key"
            :content-loading="isFetchingInitialData"
            :invalid="v$.payment_api_key.$error"
            type="text"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_tenant_id')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_tenant_id.$error && v$.payment_tenant_id.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="settingsForm.payment_tenant_id"
            :content-loading="isFetchingInitialData"
            :invalid="v$.payment_tenant_id.$error"
            type="text"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_context')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_context.$error && v$.payment_context.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="settingsForm.payment_context"
            :content-loading="isFetchingInitialData"
            :invalid="v$.payment_context.$error"
            type="text"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.payment_settings.payment_status')"
          :content-loading="isFetchingInitialData"
          :error="v$.payment_status.$error && v$.payment_status.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="settingsForm.payment_status"
            :content-loading="isFetchingInitialData"
            :invalid="v$.payment_status.$error"
            type="text"
          />
        </BaseInputGroup>
      </BaseInputGrid>

      <BaseButton
        :content-loading="isFetchingInitialData"
        :disabled="isSaving"
        :loading="isSaving"
        type="submit"
        class="mt-6"
      >
        <template #left="slotProps">
          <BaseIcon name="SaveIcon" :class="slotProps.class" />
        </template>
        {{ $tc('settings.company_info.save') }}
      </BaseButton>
    </BaseSettingCard>
  </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { required, url } from '@vuelidate/validators'
import useVuelidate from '@vuelidate/core'
import axios from 'axios'
import { useNotificationStore } from '@/scripts/stores/notification'

const { t } = useI18n()
const notificationStore = useNotificationStore()

let isSaving = ref(false)
let isFetchingInitialData = ref(false)

const paymentManagers = [
  { name: 'XavexPay', value: 'XavexPay' }
]

const settingsForm = reactive({
  payment_manager: 'XavexPay',
  payment_domain_url: '',
  payment_tenant_id: '',
  payment_context: 'Invoice',
  payment_status: 'CREATED',
  payment_api_key: ''
})

const rules = {
  payment_manager: { required },
  payment_domain_url: { 
    required,
    url: (value) => {
      if (!value) return true
      // Allow IP:port format
      const ipPortPattern = /^https?:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+$/
      const urlPattern = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/
      return ipPortPattern.test(value) || urlPattern.test(value)
    }
  },
  payment_tenant_id: { required },
  payment_context: { required },
  payment_status: { required },
  payment_api_key: { required }
}

const v$ = useVuelidate(rules, settingsForm)

async function fetchSettings() {
  isFetchingInitialData.value = true
  try {
    const response = await axios.get('/api/v1/settings/payment-settings')
    if (response.data.settings) {
      Object.assign(settingsForm, response.data.settings)
    }
  } catch (error) {
    console.error('Error fetching payment settings:', error)
  }
  isFetchingInitialData.value = false
}

async function updatePaymentSettings() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    return
  }

  isSaving.value = true
  try {
    await axios.post('/api/v1/settings/payment-settings', settingsForm)
    notificationStore.showNotification({
      type: 'success',
      message: t('settings.payment_settings.updated')
    })
  } catch (error) {
    console.error('Error updating payment settings:', error)
    notificationStore.showNotification({
      type: 'error',
      message: error.response?.data?.message || t('general.error')
    })
  }
  isSaving.value = false
}

onMounted(() => {
  fetchSettings()
})
</script> 