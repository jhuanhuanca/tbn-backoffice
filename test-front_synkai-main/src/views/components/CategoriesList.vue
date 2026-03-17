<script setup>
defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="card">
    <div class="p-3 pb-0 card-header">
      <h6 class="mb-0">Indicadores</h6>
    </div>
    <div class="p-3 card-body">
      <ul class="list-group">
        <li
          v-for="(item, idx) in categories"
          :key="idx"
          class="mb-2 border-0 list-group-item d-flex justify-content-between ps-0"
        >
          <div class="d-flex align-items-center">
            <div
              class="text-center icon icon-shape icon-sm me-3 border-radius-md"
              :class="`bg-gradient-${item?.icon?.background || 'dark'}`"
            >
              <i class="opacity-10" :class="item?.icon?.component"></i>
            </div>
            <div class="d-flex flex-column">
              <h6 class="mb-1 text-sm text-dark">{{ item?.label }}</h6>
              <span class="text-xs" v-html="item?.description" />
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

