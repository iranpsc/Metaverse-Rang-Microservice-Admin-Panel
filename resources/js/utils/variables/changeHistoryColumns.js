import { formatDisplayDate, formatDisplayTime } from '../dateFormatter'

export const systemVariableHistoryColumns = [
  { key: 'changer_name', label: 'تغییر دهنده' },
  { key: 'previous_value', label: 'مقدار قبلی' },
  { key: 'current_value', label: 'مقدار فعلی' },
  { key: 'note', label: 'یادداشت', defaultValue: '-' },
  { key: 'created_at', label: 'زمان تغییر', textSecondary: true }
]

/** Colors price logs (table uses created_at for both date and time columns). */
export const priceChangeHistoryColumns = [
  { key: 'changer_name', label: 'تغییر دهنده' },
  { key: 'previous_value', label: 'وضعیت گذشته' },
  { key: 'current_value', label: 'وضعیت حال' },
  { key: 'note', label: 'توضیحات', defaultValue: '-' },
  {
    key: 'created_at',
    label: 'تاریخ تغییر',
    formatter: formatDisplayDate
  },
  {
    key: 'created_at',
    label: 'ساعت تغییر',
    formatter: (value) => formatDisplayTime(value, { includeSeconds: true })
  }
]

export const optionChangeHistoryColumns = [
  { key: 'changer_name', label: 'تغییر دهنده' },
  { key: 'previous_value', label: 'وضعیت گذشته' },
  { key: 'current_value', label: 'وضعیت حال' },
  { key: 'note', label: 'توضیحات', defaultValue: '-' },
  {
    key: 'created_at',
    label: 'تاریخ تغییر',
    formatter: formatDisplayDate
  },
  {
    key: 'created_time',
    label: 'ساعت تغییر',
    formatter: (value) => formatDisplayTime(value, { includeSeconds: true })
  }
]
