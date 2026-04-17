<script setup>
import { computed } from "vue";
import { useStore } from "vuex";

const store = useStore();
const visible = computed(() => store.state.appLoading);
</script>

<template>
  <div v-show="visible" class="app-loading" role="status" aria-live="polite">
    <div class="app-loading__card">
      <div class="app-loading__brand">
        <div class="app-loading__logo">TBN</div>
        <div>
          <div class="app-loading__title">Cargando…</div>
          <div class="app-loading__sub">Optimizando tu experiencia (conexión lenta detectada)</div>
        </div>
      </div>
      <div class="app-loading__bar" aria-hidden="true">
        <div class="app-loading__barFill" />
      </div>
      <div class="app-loading__hint">Si tarda, verifica tu internet o intenta nuevamente.</div>
    </div>
  </div>
</template>

<style scoped>
.app-loading {
  position: fixed;
  inset: 0;
  z-index: 20000;
  display: grid;
  place-items: center;
  padding: 1.25rem;
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(2px);
}
.app-loading__card {
  width: min(560px, 92vw);
  border-radius: 1rem;
  padding: 1.25rem 1.25rem 1rem;
  background: #ffffff;
  box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.18);
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.app-loading__brand {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  margin-bottom: 0.9rem;
}
.app-loading__logo {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 800;
  letter-spacing: 0.05em;
  background: linear-gradient(135deg, #54b144 0%, #222d25 100%);
}
.app-loading__title {
  font-weight: 800;
  color: #344767;
}
.app-loading__sub {
  font-size: 0.85rem;
  color: #6c757d;
}
.app-loading__bar {
  height: 10px;
  border-radius: 999px;
  background: #eef2f0;
  overflow: hidden;
}
.app-loading__barFill {
  height: 100%;
  width: 45%;
  border-radius: 999px;
  background: linear-gradient(90deg, #54b144, #11cdef, #5e72e4);
  animation: loadingMove 1.15s ease-in-out infinite;
}
.app-loading__hint {
  margin-top: 0.75rem;
  font-size: 0.8rem;
  color: #6c757d;
}
@keyframes loadingMove {
  0% {
    transform: translateX(-60%);
  }
  50% {
    transform: translateX(60%);
  }
  100% {
    transform: translateX(-60%);
  }
}
</style>

