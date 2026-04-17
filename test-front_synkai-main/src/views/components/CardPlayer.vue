<template>
  <div class="card mt-4 move-on-hover">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h6 class="mb-1">Player</h6>
          <p class="text-sm text-muted mb-0">Widget demo (archivo recuperado).</p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-success mb-0" @click="toggle">
          {{ playing ? "Pausar" : "Reproducir" }}
        </button>
      </div>
      <div class="mt-3">
        <div class="progress" style="height: 8px">
          <div class="progress-bar bg-gradient-success" role="progressbar" :style="{ width: progress + '%' }" />
        </div>
        <div class="d-flex justify-content-between text-xs text-muted mt-2">
          <span>{{ playing ? "Reproduciendo…" : "En pausa" }}</span>
          <span>{{ progress }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CardPlayer",
  data() {
    return { playing: false, progress: 35 };
  },
  methods: {
    toggle() {
      this.playing = !this.playing;
    },
  },
};
</script>
