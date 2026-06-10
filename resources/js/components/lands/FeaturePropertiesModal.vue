<template>
  <Modal
    :model-value="modelValue"
    title="ویرایش اطلاعات ملک"
    size="md"
    @update:model-value="handleClose"
  >
    <div class="space-y-6">
      <Input
        v-model="formData.properties_id"
        label="کد زمین"
        readonly
      />

      <Input
        v-model="formData.density"
        label="تراکم"
        type="number"
        required
        :error="errors.density"
      />

      <Input
        v-model="formData.karbari"
        label="نوع کاربری"
        required
        :error="errors.karbari"
      />

      <Input
        v-model="formData.address"
        label="آدرس"
        required
        :error="errors.address"
      />

      <Input
        v-model="formData.rgb"
        label="قیمت گذاری"
        required
        :error="errors.rgb"
      />
    </div>

    <template #footer>
      <Button
        variant="primary"
        :loading="saving"
        @click="handleSave"
      >
        {{ submitButtonLabel }}
      </Button>
      <Button variant="ghost" @click="handleClose">
        بستن
      </Button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import apiClient from '../../utils/api'
import { Modal, Input, Button } from '../ui'
import { notifySuccess, notifyError } from '../../utils/notifications'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  feature: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'saved'])

const saving = ref(false)
const errors = ref({})

const formData = ref({
  properties_id: '',
  area: '',
  density: '',
  karbari: '',
  address: '',
  rgb: ''
})

const submitButtonLabel = computed(() => {
  return 'ثبت'
})

watch(() => props.feature, (newFeature) => {
  if (newFeature?.properties) {
    formData.value = {
      properties_id: newFeature.properties.id || '',
      area: newFeature.properties.area || '',
      density: newFeature.properties.density || '',
      karbari: newFeature.properties.karbari || '',
      address: newFeature.properties.address || '',
      rgb: newFeature.properties.rgb || ''
    }
  }
}, { immediate: true })

const handleClose = () => {
  emit('update:modelValue', false)
  errors.value = {}
}

const persistProperties = async () => {
  const featureId = props.feature?.id

  if (!featureId) {
    await notifyError('شناسه ملک معتبر نیست')
    return
  }

  try {
    saving.value = true

    const payload = {
        area: parseFloat(formData.value.area),
        density: parseFloat(formData.value.density),
        karbari: formData.value.karbari,
        address: formData.value.address,
        rgb: formData.value.rgb
      }

    const response = await apiClient.put(`/lands/features/${featureId}/properties`, payload)

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('saved')
      handleClose()
    }
  } catch (err) {
    console.error('Save feature properties error:', err)

    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      await notifyError(err.response?.data?.message || 'خطا در ثبت اطلاعات')
    }
  } finally {
    saving.value = false
  }
}

const handleSave = async () => {
  errors.value = {}

  await persistProperties()
}
</script>
