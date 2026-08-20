import { ref } from 'vue'

const MONTHS = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
]
const DAYS_OF_WEEK = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

export function useDatepicker(initialValue = '') {
  const today = new Date()
  let initMonth = today.getMonth()
  let initYear = today.getFullYear()
  let initDisplay = ''

  if (initialValue) {
    const d = new Date(initialValue + 'T00:00:00')
    if (!Number.isNaN(d.getTime())) {
      initMonth = d.getMonth()
      initYear = d.getFullYear()
      initDisplay = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`
    }
  }

  const open = ref(false)
  const selectedValue = ref(initialValue)
  const displayText = ref(initDisplay)
  const currentMonth = ref(initMonth)
  const currentYear = ref(initYear)

  const months = MONTHS
  const daysOfWeek = DAYS_OF_WEEK

  function openCalendar() {
    open.value = true
  }

  function closeCalendar() {
    open.value = false
  }

  function prevMonth() {
    if (currentMonth.value === 0) {
      currentMonth.value = 11
      currentYear.value--
    } else {
      currentMonth.value--
    }
  }

  function nextMonth() {
    if (currentMonth.value === 11) {
      currentMonth.value = 0
      currentYear.value++
    } else {
      currentMonth.value++
    }
  }

  function selectDate(day) {
    const month = String(currentMonth.value + 1).padStart(2, '0')
    const dayStr = String(day).padStart(2, '0')
    selectedValue.value = `${currentYear.value}-${month}-${dayStr}`
    displayText.value = `${day} ${months[currentMonth.value]} ${currentYear.value}`
    open.value = false
  }

  function isSelected(day) {
    if (!selectedValue.value) return false
    const d = new Date(selectedValue.value + 'T00:00:00')
    return (
      d.getDate() === day &&
      d.getMonth() === currentMonth.value &&
      d.getFullYear() === currentYear.value
    )
  }

  function isToday(day) {
    const t = new Date()
    return (
      t.getDate() === day &&
      t.getMonth() === currentMonth.value &&
      t.getFullYear() === currentYear.value
    )
  }

  function calendarDays() {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1).getDay()
    const daysInMonth = new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
    const days = []
    for (let i = 0; i < firstDay; i++) days.push(null)
    for (let i = 1; i <= daysInMonth; i++) days.push(i)
    return days
  }

  return {
    open,
    selectedValue,
    displayText,
    currentMonth,
    currentYear,
    months,
    daysOfWeek,
    openCalendar,
    closeCalendar,
    prevMonth,
    nextMonth,
    selectDate,
    isSelected,
    isToday,
    calendarDays,
  }
}
