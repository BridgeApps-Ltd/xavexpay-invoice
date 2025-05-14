<template>
  <BasePage>
    <BasePageHeader :title="$t('import.title')" class="mb-6">
      <BaseBreadcrumb>
        <BaseBreadcrumbItem :title="$t('general.home')" to="/admin/dashboard" />
        <BaseBreadcrumbItem
          :title="$t('import.title')"
          to="/admin/import/invoices"
          active
        />
      </BaseBreadcrumb>
    </BasePageHeader>

    <div class="w-full mb-6 select-wrapper xl:hidden">
      <BaseMultiselect
        v-model="currentImport"
        :options="dropdownMenuItems"
        :can-deselect="false"
        value-prop="title"
        track-by="title"
        label="title"
        object
        @update:modelValue="navigateToImport"
      />
    </div>

    <div class="flex">
      <div class="hidden mt-1 xl:block min-w-[240px]">
        <BaseList>
          <BaseListItem
            v-for="(menuItem, index) in globalStore.importMenu"
            :key="index"
            :title="$t(menuItem.title)"
            :to="menuItem.link"
            :active="hasActiveUrl(menuItem.link)"
            :index="index"
            class="py-3"
          >
            <template #icon>
              <BaseIcon :name="menuItem.icon"></BaseIcon>
            </template>
          </BaseListItem>
        </BaseList>
      </div>

      <div class="w-full overflow-hidden">
        <RouterView />
      </div>
    </div>
  </BasePage>
</template>

<script setup>
import { ref, reactive, watchEffect, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGlobalStore } from '@/scripts/admin/stores/global'
import BaseList from '@/scripts/components/list/BaseList.vue'
import BaseListItem from '@/scripts/components/list/BaseListItem.vue'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

let currentImport = ref({})

const globalStore = useGlobalStore()
const route = useRoute()
const router = useRouter()

const dropdownMenuItems = computed(() => {
  return globalStore.importMenu.map((item) => {
    return Object.assign({}, item, {
      title: t(item.title),
    })
  })
})

watchEffect(() => {
  if (route.path === '/admin/import') {
    router.push('/admin/import/invoices')
  }

  const item = dropdownMenuItems.value.find((item) => {
    return item.link === route.path
  })

  currentImport.value = item
})

function hasActiveUrl(url) {
  return route.path.indexOf(url) > -1
}

function navigateToImport(setting) {
  return router.push(setting.link)
}
</script> 