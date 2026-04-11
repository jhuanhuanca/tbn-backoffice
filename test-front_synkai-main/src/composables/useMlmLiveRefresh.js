import { onMounted, onUnmounted } from "vue";

/**
 * Refresco periódico para datos MLM (comisiones BIR, PV, wallet) mientras la pestaña está visible.
 * Tras pedidos o altas de referidos (cola asíncrona), el patrocinador ve cambios sin recargar.
 */
export function useMlmLiveRefresh(callback, intervalMs = 15000) {
  let timer = null;

  function runIfVisible() {
    if (typeof document !== "undefined" && document.visibilityState === "hidden") {
      return;
    }
    callback();
  }

  function start() {
    stop();
    timer = setInterval(runIfVisible, intervalMs);
  }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function onVisibility() {
    if (document.visibilityState === "visible") {
      runIfVisible();
    }
  }

  onMounted(() => {
    start();
    document.addEventListener("visibilitychange", onVisibility);
  });

  onUnmounted(() => {
    stop();
    document.removeEventListener("visibilitychange", onVisibility);
  });

  return { runNow: runIfVisible, start, stop };
}
