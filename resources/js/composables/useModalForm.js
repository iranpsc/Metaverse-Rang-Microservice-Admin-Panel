import { ref } from 'vue'

export function useModalForm(initialFormFactory) {
  const loading = ref(false)
  const saving = ref(false)
  const error = ref(null)
  const errors = ref({})
  const form = ref(initialFormFactory())

  const resetForm = () => {
    form.value = initialFormFactory()
    errors.value = {}
    error.value = null
  }

  const runFetch = async (fetcher) => {
    try {
      loading.value = true
      error.value = null
      return await fetcher()
    } catch (err) {
      error.value = err?.response?.data?.message || 'خطا در بارگذاری اطلاعات'
      throw err
    } finally {
      loading.value = false
    }
  }

  const runSave = async (saver) => {
    try {
      saving.value = true
      error.value = null
      errors.value = {}
      return await saver()
    } catch (err) {
      if (err?.response?.data?.errors) {
        errors.value = err.response.data.errors
      } else {
        error.value = err?.response?.data?.message || 'خطا در ثبت اطلاعات'
      }
      throw err
    } finally {
      saving.value = false
    }
  }

  const setFieldError = (field, message) => {
    errors.value = { ...errors.value, [field]: message }
  }

  return {
    loading,
    saving,
    error,
    errors,
    form,
    resetForm,
    runFetch,
    runSave,
    setFieldError
  }
}
