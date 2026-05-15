<template>
  <Modal
    :model-value="showDialog"
    :title="title"
    size="md"
    :close-on-backdrop="false"
    :close-on-escape="false"
    @update:model-value="onDialogUpdate"
    @close="phoneVerification.handleUserCloseVerificationDialog"
  >
    <div dir="rtl" class="relative">
      <div
        v-if="isVerifying"
        class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-slate-900/60 backdrop-blur-sm"
      >
        <Spinner size="lg" />
      </div>
      <div :class="{ 'opacity-50 pointer-events-none': isVerifying }">
        <VerificationForm
          :ref="setFormRef"
          :auto-start="false"
          @verified="phoneVerification.handleVerificationVerified"
        />
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import { Modal, Spinner } from './ui'
import VerificationForm from './VerificationForm.vue'

const props = defineProps({
  phoneVerification: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    default: 'تایید نهایی'
  }
})

const pv = props.phoneVerification

const showDialog = computed({
  get: () => pv.showVerificationDialog.value,
  set: (value) => {
    pv.showVerificationDialog.value = value
  }
})

const isVerifying = computed(() => pv.verifyingCode.value)

const setFormRef = (el) => {
  if (pv.verificationFormRef) {
    pv.verificationFormRef.value = el
  }
}

const onDialogUpdate = (value) => {
  if (!value) {
    pv.showVerificationDialog.value = false
    pv.handleUserCloseVerificationDialog()
    return
  }
  pv.showVerificationDialog.value = true
}
</script>
