<template>
  <div class="relative" ref="containerRef">
    <Button
      v-if="!showInput"
      ref="buttonRef"
      variant="danger"
      size="sm"
      :disabled="disabled"
      @click="handleOpen"
      data-error-button="true"
      class="!p-2 !min-w-[2.5rem]"
      :title="`وارد کردن اشکال - ${fieldLabel}`"
    >
      <svg
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2.5"
          d="M12 4v16m8-8H4"
        />
      </svg>
    </Button>

    <Teleport to="body">
      <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 scale-95 translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-2"
      >
        <div
          v-if="showInput"
          ref="popupRef"
          class="fixed z-[60] p-6 bg-[var(--theme-bg-elevated)] border-2 border-red-500/50 rounded-xl shadow-2xl min-w-[400px] max-w-[500px]"
          :style="popupStyle"
          @click.stop
        >
      <div class="space-y-4">
        <div class="text-base font-semibold text-red-400 mb-2">دلیل اشکال - {{ fieldLabel }}</div>
        <textarea
          v-model="errorMessage"
          class="w-full px-4 py-3 bg-[var(--theme-bg-base)] border-2 border-red-500/30 rounded-lg text-base text-[var(--theme-text-primary)] focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/70 transition-all"
          cols="30"
          rows="5"
          placeholder="دلیل اشکال را وارد کنید"
        />
        <div class="flex gap-3 justify-end pt-2">
          <Button
            variant="primary"
            size="md"
            @click="handleSave"
          >
            ثبت
          </Button>
          <Button
            variant="danger"
            size="md"
            @click="handleClose"
          >
            بستن
          </Button>
        </div>
      </div>
    </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { Button } from '../ui'

const props = defineProps({
  fieldName: {
    type: String,
    required: true
  },
  existingError: {
    type: String,
    default: null
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['save-error'])

// Field name to label mapping
const fieldLabels = {
  'bank_name_err': 'نام بانک',
  'shaba_num_err': 'شماره شبا',
  'card_num_err': 'شماره کارت',
  'fname_err': 'نام',
  'lname_err': 'نام خانوادگی',
  'melli_code_err': 'کد ملی',
  'birthdate_err': 'تاریخ تولد',
  'province_err': 'استان',
  'melli_card_err': 'تصویر کارت ملی',
  'video_err': 'فیلم احراز مستند',
  'gender_err': 'جنسیت'
}

const fieldLabel = computed(() => {
  return fieldLabels[props.fieldName] || props.fieldName.replace('_err', '').replace('_', ' ')
})

const showInput = ref(false)
const errorMessage = ref(props.existingError || '')
const buttonRef = ref(null)
const containerRef = ref(null)
const popupRef = ref(null)
const popupPosition = ref({ top: 0, left: 0 })

const popupStyle = computed(() => {
  return {
    top: `${popupPosition.value.top}px`,
    left: `${popupPosition.value.left}px`,
    boxShadow: '0 10px 40px rgba(239, 68, 68, 0.3), 0 0 20px rgba(239, 68, 68, 0.2)'
  }
})

const getModalElement = () => {
  // Find the modal content container - it's the element with 'relative z-10' classes
  // Look for the modal container that contains our button
  const allModals = document.querySelectorAll('.fixed.inset-0.z-50')

  // Find the modal that contains our button/container
  let modalContent = null

  if (containerRef.value) {
    // Walk up the DOM tree to find the modal
    let parent = containerRef.value.parentElement
    while (parent && parent !== document.body) {
      // Check if this is the modal content div (has relative z-10 and rounded-xl)
      if (parent.classList.contains('relative') &&
          (parent.classList.contains('z-10') || parent.style.zIndex === '10') &&
          parent.classList.contains('rounded-xl')) {
        modalContent = parent
        break
      }
      // Or check if parent has the modal structure
      if (parent.classList.contains('fixed') && parent.classList.contains('inset-0')) {
        // This is the modal wrapper, find the content inside
        modalContent = parent.querySelector('.relative.z-10, [class*="relative"][class*="z-10"]')
        if (!modalContent) {
          // Fallback: get any child that looks like modal content
          modalContent = Array.from(parent.children).find(el =>
            el.classList.contains('relative') && el.classList.contains('rounded-xl')
          )
        }
        break
      }
      parent = parent.parentElement
    }
  }

  // Fallback: find the most recent/open modal
  if (!modalContent && allModals.length > 0) {
    // Get the last modal (most likely the current one)
    const lastModal = allModals[allModals.length - 1]
    modalContent = lastModal.querySelector('.relative.z-10, [class*="relative"][class*="z-10"]')
    if (!modalContent) {
      modalContent = Array.from(lastModal.children).find(el =>
        el.classList.contains('relative') && el.classList.contains('rounded-xl')
      )
    }
  }

  return modalContent
}

const calculatePosition = () => {
  // Use multiple attempts to ensure DOM is ready
  const attemptPosition = (attempt = 0) => {
    const modalContent = getModalElement()

    if (!modalContent || !(modalContent instanceof HTMLElement)) {
      if (attempt < 5) {
        setTimeout(() => attemptPosition(attempt + 1), 100)
        return
      }
      // Final fallback: center on screen
      const viewportWidth = window.innerWidth
      const viewportHeight = window.innerHeight
      popupPosition.value = {
        top: Math.max(100, (viewportHeight / 2) - 150),
        left: Math.max(20, (viewportWidth / 2) - 250)
      }
      return
    }

    const modalRect = modalContent.getBoundingClientRect()
    const popupHeight = popupRef.value?.offsetHeight || 300
    const popupWidth = popupRef.value?.offsetWidth || 400

    // Calculate center position of modal
    const modalCenterX = modalRect.left + (modalRect.width / 2)
    const modalCenterY = modalRect.top + (modalRect.height / 2)

    // Position popup at center of modal
    let top = modalCenterY - (popupHeight / 2)
    let left = modalCenterX - (popupWidth / 2)

    // Ensure popup stays within viewport
    const viewportWidth = window.innerWidth
    const viewportHeight = window.innerHeight

    if (left + popupWidth > viewportWidth - 20) {
      left = viewportWidth - popupWidth - 20
    }
    if (left < 20) {
      left = 20
    }

    if (top + popupHeight > viewportHeight - 20) {
      top = viewportHeight - popupHeight - 20
    }
    if (top < 20) {
      top = 20
    }

    popupPosition.value = { top, left }
  }

  nextTick(() => {
    attemptPosition()
    // Also try after delays to ensure modal and popup are rendered
    setTimeout(() => attemptPosition(), 100)
    setTimeout(() => attemptPosition(), 200)
  })
}

const handleClickOutside = (event) => {
  if (!showInput.value || !popupRef.value) return

  const popupEl = popupRef.value
  const buttonEl = buttonRef.value?.$el || buttonRef.value

  // Check if click is outside both popup and button
  if (!popupEl.contains(event.target) &&
      !(buttonEl instanceof HTMLElement && buttonEl.contains(event.target)) &&
      !(buttonEl?.$el && buttonEl.$el.contains(event.target))) {
    handleClose()
  }
}

const handleOpen = () => {
  showInput.value = true
  calculatePosition()
}

watch(() => props.existingError, (newVal) => {
  if (newVal) {
    errorMessage.value = newVal
    showInput.value = true
    calculatePosition()
  }
})

watch(() => showInput.value, (newVal) => {
  if (newVal) {
    calculatePosition()
    window.addEventListener('resize', calculatePosition)
    window.addEventListener('scroll', calculatePosition, true)
    document.addEventListener('click', handleClickOutside)
    // Recalculate after a small delay to ensure popup is rendered
    setTimeout(calculatePosition, 50)
  } else {
    window.removeEventListener('resize', calculatePosition)
    window.removeEventListener('scroll', calculatePosition, true)
    document.removeEventListener('click', handleClickOutside)
  }
})

const handleSave = () => {
  // Only emit if there's a message (error fields are optional)
  emit('save-error', props.fieldName, errorMessage.value || '')
  showInput.value = false
}

const handleClose = () => {
  errorMessage.value = props.existingError || ''
  showInput.value = false
}

onMounted(() => {
  if (showInput.value) {
    calculatePosition()
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', calculatePosition)
  window.removeEventListener('scroll', calculatePosition, true)
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* Additional styles if needed */
</style>

