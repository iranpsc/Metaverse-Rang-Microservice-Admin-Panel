<template>
  <Modal
    :model-value="store.showModal"
    title="تمدید جلسه تایید موبایل"
    size="md"
    :z-index="100"
    :close-on-backdrop="false"
    :close-on-escape="false"
    @update:model-value="onDialogUpdate"
  >
    <div dir="rtl" class="relative space-y-5">
      <div
        v-if="store.confirming"
        class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-slate-900/60 backdrop-blur-sm"
      >
        <Spinner size="lg" />
      </div>

      <Alert
        variant="warning"
        message="جلسه تایید شماره موبایل شما منقضی شده است. برای ادامه عملیات، مدت زمان جلسه را مشخص کرده و کد تایید را وارد کنید."
        :dismissible="false"
      />

      <template v-if="store.modalStep === 'duration'">
        <Input
          v-model.number="store.durationInput"
          label="مدت جلسه (دقیقه)"
          type="number"
          :min="5"
          :max="50"
          hint="بین ۵ تا ۵۰ دقیقه"
          required
        />

        <div class="flex justify-end gap-3">
          <Button variant="glass" rounded="full" @click="store.closeModal">
            انصراف
          </Button>
          <Button
            variant="primary"
            rounded="full"
            :loading="store.sendingCode"
            @click="handleSendCode"
          >
            ارسال کد
          </Button>
        </div>
      </template>

      <template v-else>
        <Alert
          variant="success"
          message="کد تایید به شماره شما ارسال شد."
          :dismissible="false"
        />

        <MaskedInput
          ref="maskedInputRef"
          v-model="verificationCode"
          placeholder="کد تایید را وارد کنید"
          :max-length="6"
          :error="store.codeError"
          :show-toggle="true"
          :auto-reveal-on-focus="true"
          @complete="handleCodeComplete"
        />

        <div class="flex justify-end gap-3">
          <Button variant="glass" rounded="full" @click="store.modalStep = 'duration'">
            بازگشت
          </Button>
          <Button
            variant="primary"
            rounded="full"
            :loading="store.confirming"
            @click="handleConfirm"
          >
            تایید و تمدید جلسه
          </Button>
        </div>
      </template>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import { Modal, Spinner, Alert, Input, Button, MaskedInput } from './ui'
import { usePhoneVerificationStore } from '../store/phoneVerificationStore'

const store = usePhoneVerificationStore()
const { showModal } = storeToRefs(store)

const verificationCode = ref('')
const maskedInputRef = ref(null)

watch(showModal, async (visible) => {
  if (visible) {
    verificationCode.value = ''
    await nextTick()
    if (store.modalStep === 'code') {
      maskedInputRef.value?.focusFirstInput?.()
    }
  }
})

watch(verificationCode, () => {
  if (store.codeError) {
    store.codeError = ''
  }
})

const handleSendCode = async () => {
  const duration = Number(store.durationInput)

  if (!duration || duration < 5 || duration > 50) {
    store.codeError = 'مدت جلسه باید بین ۵ تا ۵۰ دقیقه باشد'
    return
  }

  store.codeError = ''
  const sent = await store.sendCode()

  if (sent) {
    await nextTick()
    maskedInputRef.value?.focusFirstInput?.()
  }
}

const handleConfirm = async () => {
  await store.confirmCode(verificationCode.value)
}

const handleCodeComplete = async (value) => {
  verificationCode.value = value
  await store.confirmCode(value)
}

const onDialogUpdate = (value) => {
  if (!value) {
    store.closeModal()
  }
}
</script>
