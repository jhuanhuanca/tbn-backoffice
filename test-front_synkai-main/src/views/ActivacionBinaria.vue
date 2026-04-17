<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import ArgonButton from "@/components/ArgonButton.vue";
import { postBinaryPlacement } from "@/services/me";
import { fetchProfile } from "@/services/me";

const store = useStore();
const router = useRouter();
const loading = ref(false);
const err = ref("");
const sponsor = computed(() => store.state.auth.user?.sponsor);
const placement = ref(store.state.auth.user?.preferred_binary_leg || "auto");

onMounted(async () => {
  try {
    const u = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: u,
      token: localStorage.getItem("token"),
    });
  } catch {
    /* */
  }
});

async function confirmarColocacion() {
  err.value = "";
  loading.value = true;
  try {
    await postBinaryPlacement({ placement: placement.value });
    const u = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: u,
      token: localStorage.getItem("token"),
    });
    router.push("/dashboard-default");
  } catch (e) {
    err.value = e.response?.data?.message || "No se pudo registrar la pierna.";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="py-5 container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow">
          <div class="card-body p-4 text-center">
            <h4 class="mb-2">Colocación en el binario</h4>
            <p class="text-sm text-secondary mb-4">
              Tu activación está registrada. Elige cómo quieres colocarte bajo tu patrocinador. En producción, esto
              evita confusiones y ayuda a planificar tu red.
              <span v-if="sponsor">Patrocinador: {{ sponsor.name }}.</span>
            </p>
            <p v-if="err" class="text-danger text-sm">{{ err }}</p>
            <div class="d-flex justify-content-center mt-3">
              <div class="btn-group btn-group-sm" role="group" aria-label="Preferencia de colocación">
                <button
                  type="button"
                  class="btn"
                  :class="placement === 'left' ? 'btn-success' : 'btn-outline-success'"
                  @click="placement = 'left'"
                >
                  Pierna izquierda
                </button>
                <button
                  type="button"
                  class="btn"
                  :class="placement === 'right' ? 'btn-success' : 'btn-outline-success'"
                  @click="placement = 'right'"
                >
                  Pierna derecha
                </button>
                <button
                  type="button"
                  class="btn"
                  :class="placement === 'auto' ? 'btn-success' : 'btn-outline-success'"
                  @click="placement = 'auto'"
                >
                  Automático
                </button>
              </div>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
              <argon-button
                color="success"
                variant="gradient"
                size="lg"
                :disabled="loading"
                class="px-5"
                @click="confirmarColocacion"
              >
                {{ loading ? "Procesando…" : "Confirmar colocación" }}
              </argon-button>
            </div>
            <p class="text-xs text-secondary mt-3 mb-0">
              Si eliges izquierda/derecha y el slot está ocupado, el sistema te avisará para que elijas otra opción.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
