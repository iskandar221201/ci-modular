<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useDatepicker } from '@/composables/useDatepicker'

const props = defineProps({
  name: { type: String, default: '' },
  label: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Pilih tanggal' },
})
const emit = defineEmits(['update:modelValue'])

const dp = useDatepicker(props.modelValue)
const root = ref(null)
const fieldId = computed(() => props.name.replace(/\W/g, '_'))

function select(day) {
  dp.selectDate(day)
  emit('update:modelValue', dp.selectedValue.value)
}

function onClickOutside(e) {
  if (dp.open.value && root.value && !root.value.contains(e.target)) dp.closeCalendar()
}
onMounted(() => document.addEventListener('click', onClickOutside))
onUnmounted(() => document.removeEventListener('click', onClickOutside))
</script>

<template>
  <div ref="root" class="relative">
    <label
      v-if="label"
      :for="`${fieldId}_trigger`"
      class="block mb-1.5 text-sm font-medium text-gray-700"
    >
      {{ label }}
    </label>

    <input :id="fieldId" type="hidden" :name="name" :value="dp.selectedValue.value" />

    <div class="relative">
      <input
        :id="`${fieldId}_trigger`"
        type="text"
        readonly
        :value="dp.displayText.value"
        :placeholder="placeholder"
        class="w-full px-3.5 py-2.5 pr-10 text-sm text-gray-900 bg-white border rounded-lg outline-none focus:ring-1 transition placeholder-gray-400 cursor-pointer border-gray-300 focus:ring-gray-400"
        autocomplete="off"
        @click.stop="dp.open.value = !dp.open.value"
        @keydown.enter.prevent="dp.open.value = !dp.open.value"
        @keydown.space.prevent="dp.open.value = !dp.open.value"
      />

      <div
        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
        aria-hidden="true"
      >
        <svg
          class="w-4 h-4 text-gray-400"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"
          />
        </svg>
      </div>

      <div
        v-show="dp.open.value"
        class="absolute top-full left-0 mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl z-50 p-4 w-72 origin-top-left"
      >
        <div class="flex items-center justify-between mb-3">
          <button
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 transition-colors focus:outline-none focus:ring-1 focus:ring-gray-400"
            aria-label="Bulan sebelumnya"
            @click="dp.prevMonth()"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 19.5L8.25 12l7.5-7.5"
              />
            </svg>
          </button>
          <span class="text-sm font-semibold text-gray-900 select-none">
            {{ dp.months[dp.currentMonth.value] + ' ' + dp.currentYear.value }}
          </span>
          <button
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 transition-colors focus:outline-none focus:ring-1 focus:ring-gray-400"
            aria-label="Bulan berikutnya"
            @click="dp.nextMonth()"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-7 gap-0.5 mb-1">
          <div
            v-for="day in dp.daysOfWeek"
            :key="day"
            class="text-center text-xs font-medium text-gray-500 py-1.5 w-9 mx-auto"
          >
            {{ day }}
          </div>
        </div>

        <div class="grid grid-cols-7 gap-0.5">
          <template v-for="(day, index) in dp.calendarDays()" :key="index">
            <button
              v-if="day !== null"
              type="button"
              class="w-9 h-9 rounded-lg text-sm flex items-center justify-center transition-colors focus:outline-none focus:ring-1 focus:ring-gray-400"
              :class="{
                'bg-gray-900 text-white hover:bg-gray-800': dp.isSelected(day),
                'bg-gray-100': dp.isToday(day) && !dp.isSelected(day),
                'hover:bg-gray-100 text-gray-900': !dp.isSelected(day),
              }"
              @click="select(day)"
            >
              {{ day }}
            </button>
            <div v-else class="w-9 h-9"></div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>
