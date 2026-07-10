<template>
  <div class="p-6 space-y-6">
    <div class="mb-4">
      <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">ارسال پیام به کاربران</h1>
      <p class="text-[var(--theme-text-secondary)]">ارسال گروهی ایمیل یا پیامک به کاربران</p>
    </div>

    <!-- Channel selector -->
    <div class="flex flex-wrap gap-4">
      <label
        v-for="option in channelOptions"
        :key="option.value"
        class="flex items-center gap-2 cursor-pointer rounded-full px-5 py-2.5 border transition-all"
        :class="channel === option.value
          ? 'border-[var(--theme-colors-primary)] bg-[var(--theme-colors-primary)]/10 text-[var(--theme-text-primary)] shadow-[0_0_12px_rgba(124,58,237,0.25)]'
          : 'border-[var(--theme-border)] text-[var(--theme-text-secondary)] hover:border-[var(--theme-colors-primary)]/50'"
      >
        <input
          v-model="channel"
          type="radio"
          name="bulk-channel"
          :value="option.value"
          class="accent-[var(--theme-colors-primary)]"
        />
        <span class="text-sm font-medium">{{ option.label }}</span>
      </label>
    </div>

    <Card variant="elevated" padding="lg" class="space-y-6">
      <!-- Targeting -->
      <div class="space-y-6">
        <h2 class="text-lg font-semibold text-[var(--theme-text-primary)]">گیرندگان</h2>

        <Select2
          v-model="sendForm.target_type"
          label="نوع ارسال"
          placeholder="انتخاب نوع ارسال"
          :options="targetTypeOptions"
          :error="sendErrors.target_type"
          required
        />

        <div v-if="sendForm.target_type === 'levels'" class="space-y-2">
          <label class="block text-sm font-medium text-[var(--theme-text-secondary)]">
            سطوح <span class="text-error">*</span>
          </label>
          <div class="max-h-48 overflow-y-auto rounded-xl border border-[var(--theme-border)] p-3 space-y-2">
            <label
              v-for="level in levelOptions"
              :key="level.value"
              class="flex items-center gap-2 cursor-pointer text-sm text-[var(--theme-text-primary)]"
            >
              <input
                v-model="sendForm.level_ids"
                type="checkbox"
                :value="level.value"
                class="rounded border-[var(--theme-border)]"
              />
              {{ level.label }}
            </label>
          </div>
          <p v-if="sendErrors.level_ids" class="text-xs text-error">{{ sendErrors.level_ids }}</p>
        </div>

        <div v-if="sendForm.target_type === 'selected_users'" class="space-y-2">
          <Select2
            :key="`bulk-user-select-${channel}`"
            v-model="sendForm.user_ids"
            label="کاربران"
            placeholder="جستجو با نام یا کد کاربر..."
            :remote-fetch="fetchUserOptions"
            :minimum-input-length="0"
            :error="sendErrors.user_ids"
            multiple
            required
            helper-text="نام یا کد کاربر را تایپ کنید تا جستجو شود و به لیست ارسال اضافه شود"
          />
        </div>

        <div v-if="sendForm.target_type === 'code_range'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[var(--theme-text-secondary)] mb-2">
              از کد <span class="text-error">*</span>
            </label>
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-[var(--theme-text-muted)]">HM</span>
              <Input
                v-model="sendForm.code_from"
                type="number"
                placeholder="2000001"
                :error="sendErrors.code_from"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-[var(--theme-text-secondary)] mb-2">
              تا کد <span class="text-error">*</span>
            </label>
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-[var(--theme-text-muted)]">HM</span>
              <Input
                v-model="sendForm.code_to"
                type="number"
                placeholder="2000200"
                :error="sendErrors.code_to"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Email content -->
      <div v-if="channel === 'email'" class="space-y-4 border-t border-[var(--theme-border)] pt-6">
        <h2 class="text-lg font-semibold text-[var(--theme-text-primary)]">متن ایمیل</h2>

        <Alert variant="info" title="راهنمای ارسال ایمیل">
          <ul class="list-disc list-inside space-y-1 text-sm mt-2">
            <li>دامنه فرستنده باید دارای رکوردهای SPF، DKIM و DMARC باشد</li>
            <li>حجم HTML از ۱۰۲ کیلوبایت تجاوز نکند</li>
            <li>ایمیل باید دارای نسخه متن ساده (plain text) نیز باشد</li>
            <li>لینک لغو اشتراک (Unsubscribe) را در متن ایمیل درج کنید</li>
            <li>از تصاویر سنگین بدون متن معادل پرهیز کنید</li>
            <li>از کلمات اسپم مانند «رایگان»، «فوری»، «اکنون اقدام کنید» اجتناب کنید</li>
            <li>پلیس‌هولدرهای مجاز: |name|، |email|، |code|</li>
          </ul>
        </Alert>

        <RichTextEditor
          v-model="emailContent"
          label="متن ایمیل"
          placeholder="متن ایمیل را وارد کنید..."
          :error="sendErrors.email_content"
          required
        />
      </div>

      <!-- SMS content -->
      <div v-if="channel === 'sms'" class="space-y-4 border-t border-[var(--theme-border)] pt-6">
        <h2 class="text-lg font-semibold text-[var(--theme-text-primary)]">متن پیامک</h2>

        <Alert variant="info" title="راهنمای ارسال پیامک (کاوه‌نگار)">
          <ul class="list-disc list-inside space-y-1 text-sm mt-2">
            <li>هیچ تصویری نباید در متن پیامک وجود داشته باشد</li>
            <li>حداکثر ۷۰ کاراکتر برای هر بخش پیامک یونیکد (فارسی) یا ۱۶۰ کاراکتر لاتین</li>
            <li>حداکثر ۱۰ بخش پیامک قابل ارسال است</li>
            <li>از لینک‌های کوتاه‌شده (bit.ly و غیره) پرهیز کنید</li>
            <li>متن پیامک نباید حاوی HTML یا تگ باشد</li>
            <li>از کلمات اسپم مانند «رایگان» یا «فوری» پرهیز کنید</li>
            <li>خطوط فرستنده باید از پنل کاوه‌نگار مجاز باشد</li>
            <li> عبارت لغو ۱۱ حتما باید انتهای متن درج شود</li>
            <li>پلیس‌هولدرهای مجاز: |name|، |code|</li>
          </ul>
        </Alert>

        <Textarea
          v-model="smsContent"
          label="متن پیامک"
          placeholder="متن پیامک را وارد کنید..."
          :rows="5"
          :error="sendErrors.sms_content"
          required
        />

        <div class="flex flex-wrap items-center gap-3 text-sm">
          <span
            class="rounded-full px-3 py-1"
            :class="smsStats.isOverLimit
              ? 'bg-error/10 text-error'
              : 'bg-[var(--theme-bg-elevated)] text-[var(--theme-text-secondary)]'"
          >
            {{ smsStats.charCount }} کاراکتر
          </span>
          <span class="text-[var(--theme-text-muted)]">
            نوع: {{ smsStats.encoding === 'unicode' ? 'یونیکد (فارسی)' : 'لاتین' }}
          </span>
          <span class="text-[var(--theme-text-muted)]">
            {{ smsStats.parts }} بخش پیامک
          </span>
          <span v-if="smsStats.isOverLimit" class="text-error text-xs">
            متن از حداکثر ۱۰ بخش کاوه‌نگار تجاوز کرده است
          </span>
        </div>
      </div>

      <div class="flex justify-end border-t border-[var(--theme-border)] pt-6">
        <Button variant="primary" :loading="sending" @click="handleSend">
          ارسال پیام
        </Button>
      </div>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  Card, Button, Textarea, Input, Select2, Alert
} from '../../components/ui'
import RichTextEditor from '../../components/ui/RichTextEditor.vue'
import { useToast } from '../../composables/useToast'
import apiClient from '../../utils/api'
import { sendBulkMessage, searchBulkMessageUsers } from '../../api/bulkMessage'

const { showToast } = useToast()

const channel = ref('email')
const sending = ref(false)
const emailContent = ref('')
const smsContent = ref('')

const sendForm = ref({
  target_type: '',
  level_ids: [],
  user_ids: [],
  code_from: '',
  code_to: ''
})
const sendErrors = ref({})

const levelOptions = ref([])

const channelOptions = [
  { value: 'email', label: 'ارسال ایمیل به کاربران' },
  { value: 'sms', label: 'ارسال پیامک به کاربران' }
]

const targetTypeOptions = [
  { value: 'all', label: 'ارسال به تمام کاربران' },
  { value: 'levels', label: 'ارسال به کاربران با سطح مشخص' },
  { value: 'selected_users', label: 'ارسال به کاربران انتخاب شده' },
  { value: 'code_range', label: 'ارسال به کاربران با محدوده کد' },
  { value: 'no_wallet', label: 'ارسال به کاربران بدون کیف پول رمزارز' }
]

const GSM_7_REGEX = /^[\x00-\x7F€£¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#$%&'()*+,\-./0-9:;<=>?@A-Z\[\\\]^_`a-z{|}~]*$/

function calculateSmsStats(text) {
  const charCount = [...text].length
  const isGsm7 = GSM_7_REGEX.test(text)
  const encoding = isGsm7 ? 'latin' : 'unicode'
  const singleLimit = isGsm7 ? 160 : 70
  const multiLimit = isGsm7 ? 153 : 67
  const maxParts = 10
  const maxChars = singleLimit + (maxParts - 1) * multiLimit

  let parts = 0
  if (charCount === 0) {
    parts = 0
  } else if (charCount <= singleLimit) {
    parts = 1
  } else {
    parts = Math.ceil((charCount - singleLimit) / multiLimit) + 1
  }

  return {
    charCount,
    encoding,
    parts,
    maxChars,
    isOverLimit: charCount > maxChars
  }
}

const smsStats = computed(() => calculateSmsStats(smsContent.value))

async function fetchLevels() {
  try {
    const response = await apiClient.get('/levels', { params: { per_page: 500 } })
    const levels = response.data?.data?.levels ?? []
    levelOptions.value = levels.map((level) => ({
      value: level.id,
      label: level.name || level.slug || `سطح ${level.id}`
    }))
  } catch {
    levelOptions.value = []
  }
}

const fetchUserOptions = ({ search, page }) => searchBulkMessageUsers({ search, page })

function validateSendForm() {
  sendErrors.value = {}
  let valid = true

  if (!sendForm.value.target_type) {
    sendErrors.value.target_type = 'نوع ارسال الزامی است'
    valid = false
  }

  if (sendForm.value.target_type === 'levels' && sendForm.value.level_ids.length === 0) {
    sendErrors.value.level_ids = 'حداقل یک سطح انتخاب کنید'
    valid = false
  }

  if (sendForm.value.target_type === 'selected_users' && sendForm.value.user_ids.length === 0) {
    sendErrors.value.user_ids = 'حداقل یک کاربر انتخاب کنید'
    valid = false
  }

  if (sendForm.value.target_type === 'code_range') {
    if (!sendForm.value.code_from) {
      sendErrors.value.code_from = 'کد شروع الزامی است'
      valid = false
    }
    if (!sendForm.value.code_to) {
      sendErrors.value.code_to = 'کد پایان الزامی است'
      valid = false
    }
  }

  if (channel.value === 'email') {
    if (!emailContent.value?.trim()) {
      sendErrors.value.email_content = 'متن ایمیل الزامی است'
      valid = false
    }
  }

  if (channel.value === 'sms') {
    if (!smsContent.value?.trim()) {
      sendErrors.value.sms_content = 'متن پیامک الزامی است'
      valid = false
    } else if (/<[^>]+>/.test(smsContent.value)) {
      sendErrors.value.sms_content = 'متن پیامک نباید حاوی HTML باشد'
      valid = false
    } else if (smsStats.value.isOverLimit) {
      sendErrors.value.sms_content = 'متن پیامک از حداکثر طول مجاز کاوه‌نگار تجاوز کرده است'
      valid = false
    }
  }

  return valid
}

async function handleSend() {
  if (!validateSendForm()) return

  try {
    sending.value = true

    const payload = {
      channel: channel.value,
      target_type: sendForm.value.target_type
    }

    if (channel.value === 'email') {
      payload.email_content = emailContent.value
    } else {
      payload.sms_content = smsContent.value
    }

    if (sendForm.value.target_type === 'levels') {
      payload.level_ids = sendForm.value.level_ids.map(Number)
    }
    if (sendForm.value.target_type === 'selected_users') {
      payload.user_ids = sendForm.value.user_ids.map(Number)
    }
    if (sendForm.value.target_type === 'code_range') {
      payload.code_from = sendForm.value.code_from
      payload.code_to = sendForm.value.code_to
    }

    const response = await sendBulkMessage(payload)

    if (response.status === 202 || response.data.success) {
      showToast('ارسال پیام در صف قرار گرفت', 'success')
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const serverErrors = err.response.data.errors
      sendErrors.value = Object.fromEntries(
        Object.entries(serverErrors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
    }
    showToast(err.response?.data?.message || 'خطا در ارسال پیام', 'error')
  } finally {
    sending.value = false
  }
}

onMounted(() => {
  fetchLevels()
})
</script>
