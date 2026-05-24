<template>
    <div class="p-6 space-y-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[var(--theme-text-primary)] mb-2">{{ pageTitle }}</h1>
            <p class="text-[var(--theme-text-secondary)]">مدیریت و مشاهده تیکت‌های پشتیبانی</p>
        </div>

        <!-- Search Box -->
        <div class="mb-6">
            <SearchBox v-model="searchTerm" placeholder="جستجو بر اساس کد، عنوان یا فرستنده..." :debounce-ms="500"
                container-class="max-w-md" @search="handleSearch" @clear="handleClear" />
        </div>

        <!-- Loading State -->
        <LoadingState v-if="loading" />

        <!-- Error State -->
        <ErrorState v-else-if="error" :message="error" variant="error" />

        <!-- Tickets Content -->
        <div v-else-if="tickets.length > 0" class="tickets-content">
            <!-- Desktop Table View -->
            <div class="hidden lg:block">
                <Table :columns="tableColumns" :data="tickets" :pagination="pagination" :show-row-number="true">
                    <!-- Code Cell -->
                    <template #cell-code="{ row }">
                        <span class="font-mono font-semibold text-[#7C3AED]">{{ row.code }}</span>
                    </template>

                    <!-- Date Cell -->
                    <template #cell-date="{ row }">
                        <span class="text-[var(--theme-text-secondary)]">{{ formatDate(row.created_at) }}</span>
                    </template>

                    <!-- Sender Cell -->
                    <template #cell-sender="{ row }">
                        <span class="font-medium text-[var(--theme-text-primary)]">{{ row.sender?.name }}</span>
                    </template>

                    <!-- Email Cell -->
                    <template #cell-email="{ row }">
                        <span class="text-truncate inline-block max-w-[150px]" :title="row.sender?.email">
                            {{ row.sender?.email || '-' }}
                        </span>
                    </template>

                    <!-- Phone Cell -->
                    <template #cell-phone="{ row }">
                        {{ row.sender?.phone || '-' }}
                    </template>

                    <!-- Title Cell -->
                    <template #cell-title="{ row }">
                        <span class="text-truncate inline-block max-w-[200px]" :title="row.title">
                            {{ row.title }}
                        </span>
                    </template>

                    <!-- Priority Cell -->
                    <template #cell-priority="{ row }">
                        <Badge :variant="getPriorityVariant(row.importance)" size="sm">
                            {{ row.priority_title }}
                        </Badge>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-status="{ row }">
                        <Badge :variant="getStatusVariant(row.status)" size="sm">
                            {{ row.status_label }}
                        </Badge>
                    </template>

                    <!-- Responser Cell -->
                    <template #cell-responser="{ row }">
                        <span class="text-[var(--theme-text-secondary)]">{{ row.responser_name || '-' }}</span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ row }">
                        <div class="flex gap-2 flex-wrap">
                            <Button size="sm" variant="primary" rounded="full" class="!p-2 !gap-0 min-w-[2.25rem]"
                                title="مشاهده" aria-label="مشاهده" @click="openTicketModal(row)">
                                <template #icon-left>
                                    <TableActionIcon name="view" />
                                </template>
                            </Button>
                            <Button v-if="row.status != 1" size="sm" variant="outline" rounded="full"
                                @click="openTransferModal(row)">
                                ارجاع
                            </Button>
                        </div>
                    </template>
                </Table>
            </div>

            <!-- Mobile/Tablet Card View -->
            <div class="lg:hidden space-y-4">
                <Card v-for="(ticket, index) in tickets" :key="ticket.id" variant="elevated" rounded="lg" padding="none"
                    hover-glow>
                    <template #header>
                        <div
                            class="flex items-center justify-between gap-2 p-4 bg-[var(--theme-bg-glass)] border-b border-[var(--theme-border)]">
                            <div class="flex items-center gap-2">
                                <div class="font-bold text-[var(--theme-text-primary)]">#{{ (pagination.current_page -
                                    1) * pagination.per_page + index + 1 }}</div>
                                <div class="font-mono font-semibold text-[#7C3AED]">{{ ticket.code }}</div>
                            </div>
                            <Badge :variant="getStatusVariant(ticket.status)" size="sm">
                                {{ ticket.status_label }}
                            </Badge>
                        </div>
                    </template>

                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-2">
                            <span
                                class="font-semibold text-[var(--theme-text-secondary)] min-w-[80px] flex-shrink-0">عنوان:</span>
                            <span class="text-[var(--theme-text-primary)] flex-1">{{ ticket.title }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span
                                class="font-semibold text-[var(--theme-text-secondary)] min-w-[80px] flex-shrink-0">فرستنده:</span>
                            <span class="text-[var(--theme-text-primary)] flex-1">{{ ticket.sender?.name }}</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span
                                class="font-semibold text-[var(--theme-text-secondary)] min-w-[80px] flex-shrink-0">تاریخ:</span>
                            <span class="text-[var(--theme-text-primary)] flex-1">{{ formatDate(ticket.created_at)
                                }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="font-semibold text-[var(--theme-text-secondary)] min-w-[80px] flex-shrink-0">اهمیت:</span>
                            <Badge :variant="getPriorityVariant(ticket.importance)" size="sm">
                                {{ ticket.priority_title }}
                            </Badge>
                        </div>
                        <div v-if="ticket.responser_name" class="flex items-start gap-2">
                            <span
                                class="font-semibold text-[var(--theme-text-secondary)] min-w-[80px] flex-shrink-0">پاسخ‌دهنده:</span>
                            <span class="text-[var(--theme-text-primary)] flex-1">{{ ticket.responser_name }}</span>
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex gap-2 p-4 border-t border-[var(--theme-border)] bg-[var(--theme-bg-glass)]">
                            <Button variant="primary" rounded="full" full-width class="!gap-0 justify-center !py-2.5"
                                title="مشاهده" aria-label="مشاهده" @click="openTicketModal(ticket)">
                                <template #icon-left>
                                    <TableActionIcon name="view" />
                                </template>
                            </Button>
                            <Button v-if="ticket.status != 1" variant="outline" rounded="full" full-width
                                @click="openTransferModal(ticket)">
                                ارجاع
                            </Button>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Pagination -->
            <Pagination v-if="pagination && pagination.last_page > 1" :pagination="pagination" :disabled="loading"
                @page-change="goToPage" />
        </div>

        <!-- Empty State -->
        <ErrorState v-else title="تیکتی یافت نشد!" message="در حال حاضر هیچ تیکتی در این بخش وجود ندارد."
            variant="info">
            <template #icon>
                <i class="fas fa-inbox text-6xl text-[var(--theme-text-muted)]"></i>
            </template>
        </ErrorState>

        <!-- Ticket Details Modal -->
        <Modal v-model="showTicketModal" title="جزئیات تیکت" size="lg" :close-on-backdrop="true">
            <div v-if="selectedTicket" class="space-y-4">
                <div>
                    <span class="text-[var(--theme-text-secondary)]">
                        شماره تیکت: <strong class="text-[#7C3AED]">{{ selectedTicket.code }}</strong>
                    </span>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-[var(--theme-text-primary)] mb-2">
                        عنوان: {{ selectedTicket.title }}
                    </h5>
                    <p class="text-[var(--theme-text-secondary)] whitespace-pre-wrap">
                        {{ selectedTicket.content }}
                    </p>
                    <div v-if="selectedTicket.attachment" class="mt-4">
                        <span class="text-sm font-semibold text-[var(--theme-text-primary)] mr-1">
                            پیوست:
                        </span>
                        <a :href="selectedTicket.attachment" target="_blank" rel="noopener"
                            class="text-primary underline hover:text-primaryLight transition-colors">
                            مشاهده و دانلود فایل
                        </a>
                    </div>
                <div v-if="selectedTicket.responses && selectedTicket.responses.length > 0" class="mt-6">
                    <div class="mb-2 text-[var(--theme-text-secondary)] font-bold flex items-center gap-2">
                        <i class="fas fa-comments text-primary"></i>
                        پاسخ‌ها
                    </div>
                    <div class="space-y-4">
                        <div v-for="(response, idx) in selectedTicket.responses" :key="response.id"
                            class="bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] rounded-md p-4 shadow-sm">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-[var(--theme-text-primary)]">
                                    پاسخ‌دهنده:
                                    <span class="text-primary">{{ response.responser_name || (response.responser && response.responser.name) || '----' }}</span>
                                </span>
                                <span class="text-xs ml-auto text-[var(--theme-text-muted)]">
                                    {{ response.created_at ? new Date(response.created_at).toLocaleString('fa-IR') : '' }}
                                </span>
                            </div>
                            <div class="text-[var(--theme-text-secondary)] whitespace-pre-wrap mb-2">
                                {{ response.response }}
                            </div>
                            <div v-if="response.attachment" class="mt-2">
                                <a :href="response.attachment" target="_blank" rel="noopener" class="text-primary underline hover:text-primaryLight transition-colors text-sm">
                                    مشاهده پیوست
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
                <hr class="border-[var(--theme-border)]" />

                <div v-if="selectedTicket.status != 1" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[var(--theme-text-primary)] mb-2">
                            متن پاسخ:
                        </label>
                        <textarea v-model="responseText"
                            class="w-full px-4 py-2.5 text-sm rounded-lg bg-[var(--theme-bg-elevated)] border border-[var(--theme-border)] text-[var(--theme-text-primary)] focus:outline-none focus:ring-2 focus:ring-[#7C3AED]/50 focus:border-[#7C3AED] transition-all"
                            rows="4" placeholder="متن پاسخ را تایپ کنید..."></textarea>
                        <span v-if="errors.response" class="text-red-500 text-xs mt-1 block">{{ errors.response
                            }}</span>
                    </div>

                    <FileInput v-model="responseAttachment" label="پیوست" accept=".pdf,.png,.jpeg,.jpg"
                        placeholder="فایل پیوست (اختیاری)" helper-text="PDF یا تصویر"
                        :error="fieldError(errors.attachment)" :clearable="true" @change="onResponseAttachmentChange" />
                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end">
                    <Button v-if="selectedTicket && selectedTicket.status != 1" variant="primary"
                        :loading="responseLoading" @click="handleSendResponse">
                        ارسال پاسخ
                    </Button>
                    <Button variant="outline" @click="closeTicketModal">
                        بستن
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Transfer Ticket Modal -->
        <Modal v-model="showTransferModal" title="ارجاع به بخش دیگر" size="md" :close-on-backdrop="true">
            <div v-if="selectedTicket" class="space-y-4">
                <Alert variant="info" :dismissible="false">
                    در صورتی که این تیکت به حوزه شما مربوط نمی‌باشد می‌توانید به بخش مربوطه ارجاع دهید.
                </Alert>

                <Select v-model="transferDepartment" label="بخش مقصد" :options="availableDepartments"
                    option-value="value" option-label="label" placeholder="انتخاب کنید" :error="errors.department"
                    required />

                <Select v-model="transferImportance" label="درجه اهمیت" :options="importanceOptions"
                    option-value="value" option-label="label" placeholder="انتخاب کنید" :error="errors.importance"
                    required />
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end">
                    <Button variant="primary" :loading="transferLoading" @click="handleTransfer">
                        ارجاع
                    </Button>
                    <Button variant="outline" @click="closeTransferModal">
                        بستن
                    </Button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import apiClient from '../../utils/api'
import { useTickets } from '../../composables/useTickets'
import { useToast } from '../../composables/useToast'
import { Table, Pagination, SearchBox, LoadingState, ErrorState, Badge, Card, Modal, Button, Input, Select, Alert, FileInput } from '../../components/ui'
import TableActionIcon from '../../components/icons/TableActionIcon.vue'

const props = defineProps({
    department: {
        type: [String, Array],
        required: true
    },
    pageTitle: {
        type: String,
        required: true
    }
})

const { showToast } = useToast()
const {
    sendResponse: sendTicketResponse,
    transferTicket: transferTicketToDepartment,
    getDepartments,
    formatDate
} = useTickets()

const loading = ref(true)
const error = ref(null)
const tickets = ref([])
const pagination = ref(null)
const searchTerm = ref('')
const currentPage = ref(1)
const departments = ref([])

// Modal states
const showTicketModal = ref(false)
const showTransferModal = ref(false)
const selectedTicket = ref(null)
const responseText = ref('')
const responseAttachment = ref(null)
const responseLoading = ref(false)
const transferLoading = ref(false)
const transferDepartment = ref('')
const transferImportance = ref('')
const errors = ref({})

const fieldError = (val) => {
    if (val == null || val === '') return ''
    return Array.isArray(val) ? val[0] : String(val)
}

// Table columns configuration
const tableColumns = computed(() => [
    {
        key: 'code',
        label: 'کد',
        headerClass: 'min-w-[80px]',
        cellClass: 'min-w-[80px]'
    },
    {
        key: 'date',
        label: 'تاریخ',
        headerClass: 'min-w-[100px]',
        cellClass: 'min-w-[100px]'
    },
    {
        key: 'sender',
        label: 'فرستنده',
        headerClass: 'min-w-[120px]',
        cellClass: 'min-w-[120px]'
    },
    {
        key: 'email',
        label: 'ایمیل',
        headerClass: 'hidden xl:table-cell min-w-[150px]',
        cellClass: 'hidden xl:table-cell min-w-[150px]',
        textSecondary: true
    },
    {
        key: 'phone',
        label: 'تلفن',
        headerClass: 'hidden xl:table-cell min-w-[100px]',
        cellClass: 'hidden xl:table-cell min-w-[100px]',
        textSecondary: true
    },
    {
        key: 'title',
        label: 'عنوان',
        headerClass: 'min-w-[150px] max-w-[250px]',
        cellClass: 'min-w-[150px] max-w-[250px]'
    },
    {
        key: 'priority',
        label: 'اهمیت',
        headerClass: 'min-w-[100px]',
        cellClass: 'min-w-[100px]'
    },
    {
        key: 'status',
        label: 'وضعیت',
        headerClass: 'min-w-[100px]',
        cellClass: 'min-w-[100px]'
    },
    {
        key: 'responser',
        label: 'پاسخ‌دهنده',
        headerClass: 'hidden xl:table-cell min-w-[120px]',
        cellClass: 'hidden xl:table-cell min-w-[120px]',
        textSecondary: true
    },
    {
        key: 'actions',
        label: 'عملیات',
        headerClass: 'min-w-[140px] xl:min-w-[180px]',
        cellClass: 'min-w-[140px] xl:min-w-[180px]'
    }
])

// Search handler (called by SearchBox with debounce)
const handleSearch = () => {
    currentPage.value = 1
    fetchTickets()
}

const handleClear = () => {
    currentPage.value = 1
    fetchTickets()
}

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value?.last_page) {
        currentPage.value = page
        fetchTickets()
    }
}

const fetchTickets = async () => {
    try {
        loading.value = true
        error.value = null

        const params = {
            department: props.department,
            page: currentPage.value,
            per_page: 10
        }

        if (searchTerm.value && searchTerm.value.trim()) {
            params.search = searchTerm.value.trim()
        }

        const response = await apiClient.get('/tickets', { params })

        if (response.data.success) {
            tickets.value = response.data.data.tickets
            pagination.value = response.data.data.pagination
        } else {
            error.value = 'خطا در دریافت تیکت‌ها'
        }
    } catch (err) {
        console.error('Tickets fetch error:', err)

        // If 401/403, don't set error message - axios interceptor will handle redirect
        if (err.response && (err.response.status === 401 || err.response.status === 403)) {
            // Auth failed - let the interceptor handle redirect
            // Don't set error message as redirect will happen
            tickets.value = []
            pagination.value = null
            loading.value = false
            return
        }

        error.value = err.response?.data?.message || 'خطا در بارگذاری تیکت‌ها'
        tickets.value = []
        pagination.value = null
    } finally {
        loading.value = false
    }
}


const loadDepartments = async () => {
    try {
        departments.value = await getDepartments()
    } catch (error) {
        console.error('Error loading departments:', error)
    }
}

onMounted(async () => {
    await fetchTickets()
    await loadDepartments()
})

watch(
    () => props.department,
    async () => {
        // Route param changes keep this component instance alive;
        // reset list state so each department loads via SPA navigation.
        currentPage.value = 1
        searchTerm.value = ''
        tickets.value = []
        pagination.value = null
        error.value = null
        await fetchTickets()
    }
)

// Helper methods for badge variants
const getPriorityVariant = (importance) => {
    if (importance === 1) return 'danger'
    if (importance === 0) return 'warning'
    return 'default'
}

const getStatusVariant = (status) => {
    // Map status to badge variant
    // You may need to adjust these based on your actual status values
    if (status === 1) return 'primary' // New/Open
    if (status === 2) return 'info' // In Progress
    if (status === 3) return 'success' // Closed
    return 'default'
}

// Modal handlers
const openTicketModal = (ticket) => {
    selectedTicket.value = ticket
    responseText.value = ''
    responseAttachment.value = null
    errors.value = {}
    showTicketModal.value = true
}

const onResponseAttachmentChange = () => {
    if (errors.value.attachment) {
        delete errors.value.attachment
    }
}

const closeTicketModal = () => {
    showTicketModal.value = false
    selectedTicket.value = null
    responseText.value = ''
    responseAttachment.value = null
    errors.value = {}
}

const openTransferModal = (ticket) => {
    selectedTicket.value = ticket
    transferDepartment.value = ''
    transferImportance.value = ''
    errors.value = {}
    showTransferModal.value = true
}

const closeTransferModal = () => {
    showTransferModal.value = false
    selectedTicket.value = null
    transferDepartment.value = ''
    transferImportance.value = ''
    errors.value = {}
}

const handleSendResponse = async () => {
    errors.value = {}

    // Validation
    if (!responseText.value.trim()) {
        errors.value.response = 'متن پاسخ الزامی است'
        return
    }

    responseLoading.value = true

    try {
        const result = await sendTicketResponse(selectedTicket.value.id, {
            response: responseText.value,
            attachment: responseAttachment.value
        })

        if (result.success) {
            showToast('پاسخ تیکت با موفقیت ارسال شد', 'success')
            closeTicketModal()
            await fetchTickets()
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            showToast('خطا در ارسال پاسخ', 'error')
        }
    } finally {
        responseLoading.value = false
    }
}

const handleTransfer = async () => {
    errors.value = {}

    // Validation
    if (!transferDepartment.value) {
        errors.value.department = 'انتخاب بخش الزامی است'
        return
    }

    if (transferImportance.value === '') {
        errors.value.importance = 'انتخاب درجه اهمیت الزامی است'
        return
    }

    transferLoading.value = true

    try {
        const result = await transferTicketToDepartment(selectedTicket.value.id, {
            department: transferDepartment.value,
            importance: parseInt(transferImportance.value)
        })

        if (result.success) {
            showToast('تیکت با موفقیت ارجاع داده شد', 'success')
            closeTransferModal()
            await fetchTickets()
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            showToast('خطا در ارجاع تیکت', 'error')
        }
    } finally {
        transferLoading.value = false
    }
}

// Computed properties for modals
const availableDepartments = computed(() => {
    if (!selectedTicket.value || !departments.value.length) return []
    return departments.value.filter(dept => dept.value !== selectedTicket.value.department)
})

const importanceOptions = computed(() => [
    { value: -1, label: 'کم' },
    { value: 0, label: 'متوسط' },
    { value: 1, label: 'زیاد' }
])
</script>

<style scoped>
/* Styles are now handled by the Table component */
</style>
