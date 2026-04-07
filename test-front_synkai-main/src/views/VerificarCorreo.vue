<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import ArgonButton from "@/components/ArgonButton.vue";
import ArgonInput from "@/components/ArgonInput.vue";
import { resendVerificationEmail } from "@/services/auth";

const route = useRoute();
const router = useRouter();
const email = ref("");
const msg = ref("");
const err = ref("");
const loading = ref(false);

onMounted(() => {
  const q = route.query.email;
  if (q) email.value = String(q);
});

async function reenviar() {
  err.value = "";
  msg.value = "";
  if (!email.value.trim()) {
    err.value = "Indica el correo con el que te registraste.";
    return;
  }
  loading.value = true;
  try {
    await resendVerificationEmail(email.value.trim());
    msg.value = "Si el correo es correcto, revisa tu bandeja (y spam) en unos minutos.";
  } catch {
    err.value = "No se pudo enviar. Inténtalo más tarde.";
  } finally {
    loading.value = false;
  }
}

function irLogin() {
  router.push("/signin");
}
</script>

<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow border-0">
          <div class="card-body p-5 text-center">
            <i class="ni ni-email-83 text-primary display-4 mb-3"></i>
            <h4 class="mb-2">Confirma tu correo</h4>
            <p class="text-sm text-secondary mb-4">
              Te enviamos un enlace de verificación. Ábrelo para activar la cuenta y luego podrás iniciar sesión.
            </p>
            <div class="text-start mb-3">
              <label class="form-label text-sm">Correo</label>
              <argon-input v-model="email" type="email" placeholder="correo@ejemplo.com" />
            </div>
            <argon-button color="primary" variant="gradient" class="w-100 mb-2" :disabled="loading" @click="reenviar">
              {{ loading ? "Enviando…" : "Reenviar enlace" }}
            </argon-button>
            <p v-if="msg" class="text-success text-sm mb-2">{{ msg }}</p>
            <p v-if="err" class="text-danger text-sm mb-2">{{ err }}</p>
            <button type="button" class="btn btn-link text-sm" @click="irLogin">Ir a iniciar sesión</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
