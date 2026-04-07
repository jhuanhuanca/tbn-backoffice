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

async function elegir(leg) {
  err.value = "";
  loading.value = true;
  try {
    await postBinaryPlacement(leg);
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
              Tu activación está registrada. Elige si entras en la pierna
              <strong>izquierda</strong> o <strong>derecha</strong> de tu patrocinador
              <span v-if="sponsor">({{ sponsor.name }})</span>.
            </p>
            <p v-if="err" class="text-danger text-sm">{{ err }}</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
              <argon-button
                color="success"
                variant="gradient"
                size="lg"
                :disabled="loading"
                class="px-5"
                @click="elegir('left')"
              >
                Pierna izquierda
              </argon-button>
              <argon-button
                color="info"
                variant="gradient"
                size="lg"
                :disabled="loading"
                class="px-5"
                @click="elegir('right')"
              >
                Pierna derecha
              </argon-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
