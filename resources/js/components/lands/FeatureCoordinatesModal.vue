<template>
  <Modal
    :model-value="modelValue"
    title="ویرایش مختصات ملک"
    size="lg"
    :close-on-backdrop="false"
    @update:model-value="handleClose"
  >
    <div class="space-y-6 max-h-96 overflow-y-auto">
      <div
        v-for="(coordinate, index) in formData.coordinates"
        :key="index"
        class="grid grid-cols-2 gap-4"
      >
        <div>
          <label
            :for="`x-${index}`"
            class="block text-sm font-medium mb-2 text-[var(--theme-text-primary)]"
          >
            X
          </label>
          <Input
            :id="`x-${index}`"
            v-model.number="coordinate.x"
            type="number"
            step="any"
            :error="errors[`coordinates.${index}.x`]"
          />
        </div>
        <div>
          <label
            :for="`y-${index}`"
            class="block text-sm font-medium mb-2 text-[var(--theme-text-primary)]"
          >
            Y
          </label>
          <Input
            :id="`y-${index}`"
            v-model.number="coordinate.y"
            type="number"
            step="any"
            :error="errors[`coordinates.${index}.y`]"
          />
        </div>
      </div>
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
  coordinates: []
})

const submitButtonLabel = computed(() => {
  return 'ثبت'
})

watch(() => props.feature, (newFeature) => {
  if (newFeature?.geometry?.coordinates) {
    formData.value.coordinates = newFeature.geometry.coordinates.map((coord, index) => ({
      id: coord.id || index,
      x: coord.x || 0,
      y: coord.y || 0
    }))
  }
}, { immediate: true })

const handleClose = () => {
  emit('update:modelValue', false)
  errors.value = {}
}

const validateCoordinates = () => {
  errors.value = {}

  for (let i = 0; i < formData.value.coordinates.length; i++) {
    const coord = formData.value.coordinates[i]
    if (coord.x === null || coord.x === undefined || Number.isNaN(coord.x)) {
      errors.value[`coordinates.${i}.x`] = 'مقدار X الزامی است'
      return false
    }
    if (coord.y === null || coord.y === undefined || Number.isNaN(coord.y)) {
      errors.value[`coordinates.${i}.y`] = 'مقدار Y الزامی است'
      return false
    }
  }

  return true
}

const persistCoordinates = async () => {
  const featureId = props.feature?.id

  if (!featureId) {
    await notifyError('شناسه ملک معتبر نیست')
    return
  }

  try {
    saving.value = true

    const payload = {
        coordinates: formData.value.coordinates.map((coord) => ({
          x: parseFloat(coord.x),
          y: parseFloat(coord.y)
        }))
      }

    const response = await apiClient.put(`/lands/features/${featureId}/coordinates`, payload)

    if (response.data.success) {
      await notifySuccess('اطلاعات با موفقیت ثبت شد')
      emit('saved')
      handleClose()
    }
  } catch (err) {
    console.error('Save feature coordinates error:', err)

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
  if (!validateCoordinates()) {
    return
  }

  await persistCoordinates()
}
</script>
