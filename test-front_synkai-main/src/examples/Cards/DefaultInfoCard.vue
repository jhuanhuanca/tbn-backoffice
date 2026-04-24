<template>
  <div class="card h-100">
    <div class="card-body p-3">
      <div class="d-flex align-items-center gap-3">
        <div
          class="icon icon-shape shadow text-center rounded-circle d-flex align-items-center justify-content-center"
          :class="icon?.background || 'bg-gradient-success'"
          style="width: 44px; height: 44px"
        >
          <i :class="icon?.component || 'ni ni-credit-card'" aria-hidden="true" />
        </div>
        <div class="flex-grow-1">
          <p class="text-xs text-uppercase text-muted font-weight-bold mb-1">{{ title }}</p>
          <h6 class="mb-0 text-dark font-weight-bolder">{{ value }}</h6>
          <p v-if="description" class="text-xxs text-muted mb-0">{{ description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: Object, default: () => ({}) },
  title: { type: String, default: "" },
  description: { type: String, default: "" },
  value: { type: [String, Number], default: "" },
});
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
</style>

