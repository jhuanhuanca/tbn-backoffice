<script setup>
/**
 * Panel reutilizable de medios de pago MLM (UI).
 */
import { ref, computed, watch } from "vue";
import { SHIPPING_NOTICE_TEXT } from "@/constants/shippingNotice";
import { REGISTRATION_PAYMENT_METHODS } from "@/constants/registrationPayments";
import qrImageUrl from "@/assets/img/QRimage.jpeg";

const qrSrc = computed(() => (typeof qrImageUrl === "string" ? qrImageUrl : qrImageUrl?.default || ""));

const props = defineProps({
  /** Valor del método seleccionado (mismo esquema que REGISTRATION_PAYMENT_METHODS). */
  modelValue: {
    type: String,
    default: "transferencia",
  },
  /** Si true, muestra el desplegable de método; si false, solo el detalle del método actual. */
  showMethodSelect: {
    type: Boolean,
    default: true,
  },
  /** Etiqueta del bloque */
  title: {
    type: String,
    default: "Medios de pago y entrega",
  },
});

const emit = defineEmits(["update:modelValue"]);

const method = computed({
  get: () => props.modelValue,
  set: (v) => emit("update:modelValue", v),
});

const receiptFileName = ref("");
const cardNumber = ref("");
const cardName = ref("");
const cardExpiry = ref("");
const cardSimulated = ref(false);

watch(
  () => props.modelValue,
  () => {
    cardSimulated.value = false;
  }
);

function onReceiptChange(e) {
  const f = e.target?.files?.[0];
  receiptFileName.value = f ? f.name : "";
}

function simulateCardOk() {
  if (!cardNumber.value.trim() || !cardName.value.trim() || !cardExpiry.value.trim()) {
    return;
  }
  cardSimulated.value = true;
}

const paymentOptions = REGISTRATION_PAYMENT_METHODS;
</script>

<template>
  <div class="mlm-pay-panel card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
      <h6 class="text-dark font-weight-bolder mb-1">{{ title }}</h6>
      <p class="text-xs text-secondary mb-3">
        Completa según el método elegido.
      </p>

      <div v-if="showMethodSelect" class="mb-3">
        <label class="form-label text-sm mb-1">Método de pago</label>
        <select v-model="method" class="form-select">
          <option v-for="opt in paymentOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <div class="alert alert-secondary border-0 text-dark text-xs mb-3 py-2 mlm-pay-panel__notice">
        <i class="ni ni-delivery-fast me-1 text-success" aria-hidden="true"></i>
        <strong>Entrega y envío:</strong> {{ SHIPPING_NOTICE_TEXT }}
      </div>

      <!-- Transferencia -->
      <div v-if="method === 'transferencia'" class="mlm-pay-section rounded-3 p-3 bg-light">
        <p class="text-sm font-weight-bold mb-2">Transferencia bancaria</p>
        <ul class="text-sm mb-3 ps-3 mb-0">
          <li><strong>Banco:</strong> Banco Unión</li>
          <li><strong>N° cuenta:</strong> 1234567890</li>
          <li><strong>Titular:</strong> Empresa</li>
        </ul>
        <p class="text-xs text-muted mb-2">
          Después de transferir, sube el comprobante (captura o PDF).
        </p>
        <label class="form-label text-xs">Comprobante</label>
        <input type="file" class="form-control form-control-sm" accept="image/*,.pdf" @change="onReceiptChange" />
        <p v-if="receiptFileName" class="text-success text-xs mt-1 mb-0">Archivo: {{ receiptFileName }}</p>
      </div>

      <!-- QR -->
      <div v-else-if="method === 'qr'" class="mlm-pay-section rounded-3 p-3 bg-light text-center">
        <p class="text-sm font-weight-bold mb-2">Pago QR</p>
        <div class="d-inline-block p-2 bg-white rounded shadow-sm mb-3">
          <img :src="qrSrc" alt="QR de pago" style="width: 180px; height: 180px; object-fit: contain" />
        </div>
        <label class="form-label text-xs d-block text-start">Adjuntar comprobante de escaneo</label>
        <input type="file" class="form-control form-control-sm" accept="image/*" @change="onReceiptChange" />
        <p v-if="receiptFileName" class="text-success text-xs mt-1 mb-0">Archivo: {{ receiptFileName }}</p>
      </div>

      <!-- Tarjeta -->
      <div v-else-if="method === 'tarjeta'" class="mlm-pay-section rounded-3 p-3 bg-light">
        <p class="text-sm font-weight-bold mb-2">Tarjeta débito / crédito</p>
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label text-xs">Número de tarjeta</label>
            <input
              v-model="cardNumber"
              type="text"
              class="form-control form-control-sm"
              maxlength="19"
              placeholder="0000 0000 0000 0000"
              autocomplete="off"
            />
          </div>
          <div class="col-md-8">
            <label class="form-label text-xs">Nombre en la tarjeta</label>
            <input v-model="cardName" type="text" class="form-control form-control-sm" placeholder="Como figura" />
          </div>
          <div class="col-md-4">
            <label class="form-label text-xs">Vencimiento (MM/AA)</label>
            <input v-model="cardExpiry" type="text" class="form-control form-control-sm" placeholder="MM/AA" />
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-success mt-3" @click="simulateCardOk">Confirmar pago</button>
        <p v-if="cardSimulated" class="text-success text-xs mt-2 mb-0">
          Pago marcado como confirmado.
        </p>
      </div>

      <!-- Efectivo -->
      <div v-else-if="method === 'efectivo'" class="mlm-pay-section rounded-3 p-3 bg-light">
        <p class="text-sm font-weight-bold mb-2">Pago en efectivo</p>
        <p class="text-sm mb-0">
          <strong>Pago contra entrega</strong> o coordinación con tu asesor. El pedido puede quedar pendiente hasta
          confirmación en panel empresa.
        </p>
      </div>

      <!-- Cripto / otro -->
      <div v-else class="mlm-pay-section rounded-3 p-3 bg-light">
        <p class="text-sm font-weight-bold mb-2">{{ paymentOptions.find((o) => o.value === method)?.label }}</p>
        <p class="text-sm text-muted mb-2">
          Contacta a soporte con tu número de pedido. Puedes adjuntar comprobante aquí.
        </p>
        <input type="file" class="form-control form-control-sm" @change="onReceiptChange" />
        <p v-if="receiptFileName" class="text-success text-xs mt-1 mb-0">Archivo: {{ receiptFileName }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.mlm-pay-panel__notice {
  line-height: 1.45;
}
.mlm-pay-section {
  border: 1px solid rgba(0, 0, 0, 0.06);
}
</style>
