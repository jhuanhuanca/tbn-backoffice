<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import { useStore } from "vuex";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import ArgonButton from "@/components/ArgonButton.vue";
import { fetchProductsCatalog, createOrder, fetchProfile, fetchFounderStatus } from "@/services/me";
import { useMlmLiveRefresh } from "@/composables/useMlmLiveRefresh";
import MlmPaymentMethodPanel from "@/components/MlmPaymentMethodPanel.vue";

/**
 * Imágenes locales fallback (cuando el producto no trae image_url).
 * Carga automáticamente todas las imágenes en `/assets/img/productos/` (1..41 o las que existan).
 */
function loadFallbackImages() {
  try {
    // Webpack (Vue CLI): require.context
    // eslint-disable-next-line no-undef
    const ctx = require.context("@/assets/img/productos", false, /\.(png|jpe?g|webp)$/i);
    const list = ctx.keys().map((k) => ({ key: k, src: ctx(k) }));
    const num = (k) => {
      const m = String(k).match(/(\d+)\.(png|jpe?g|webp)$/i);
      return m ? parseInt(m[1], 10) : Number.POSITIVE_INFINITY;
    };
    return list
      .sort((a, b) => num(a.key) - num(b.key))
      .map((x) => (typeof x.src === "string" ? x.src : x.src?.default || x.src));
  } catch {
    return [];
  }
}

const FALLBACK_IMAGES = loadFallbackImages();

const store = useStore();

async function syncProfilePv() {
  if (!localStorage.getItem("token")) return;
  try {
    const profile = await fetchProfile();
    await store.dispatch("auth/setAuth", {
      user: profile,
      token: localStorage.getItem("token"),
    });
  } catch {
    /* mantiene último PV en store */
  }
}

useMlmLiveRefresh(syncProfilePv, 12000);

const loading = ref(true);
const loadError = ref("");
const productos = ref([]);
const carrito = ref([]);
const checkoutLoading = ref(false);
const checkoutMsg = ref("");
const checkoutErr = ref("");
const paymentSettlement = ref("immediate");
const paymentMethodOffline = ref("efectivo");
const cartJumpPulse = ref(false);

// Paquete Fundador (progreso a 1200 PV)
const founderLoading = ref(false);
const founderErr = ref("");
const founderModalOpen = ref(false);
const founderPvActual = ref(0);
const founderPvCredited = ref(0);
const founderPvInscripcionPendiente = ref(0);
const founderTargetPv = ref(1200);
const founderCompleted = ref(false);
const founderBuyLoading = ref(false);
const founderBuyMsg = ref("");

const founderPackages = [
  { key: "basico", name: "Básico", priceBob: 1050, pv: 100 },
  { key: "avanzado", name: "Avanzado", priceBob: 2700, pv: 300 },
  { key: "profesional", name: "Profesional", priceBob: 5400, pv: 600 },
  { key: "fundador", name: "Fundador", priceBob: 10800, pv: 1200 },
];

const founderMissingPv = computed(() => Math.max(0, Number(founderTargetPv.value) - Number(founderPvActual.value)));
const founderProgressPct = computed(() => {
  const t = Number(founderTargetPv.value) || 1200;
  const v = Number(founderPvActual.value) || 0; // total (acreditado + inscripción pendiente)
  return Math.max(0, Math.min(100, (v / t) * 100));
});

/** Segmento: PV acreditados (sin inscripción pendiente). */
const founderCreditedPct = computed(() => {
  const t = Number(founderTargetPv.value) || 1200;
  const v = Number(founderPvCredited.value) || 0;
  return Math.max(0, Math.min(100, (v / t) * 100));
});

/** Segmento: PV de inscripción iniciada (pendiente), sin solapar acreditado. */
const founderRegBarPct = computed(() => {
  const t = Number(founderTargetPv.value) || 1200;
  const reg = Number(founderPvInscripcionPendiente.value) || 0;
  if (t <= 0 || reg <= 0) return 0;
  const creditedPct = founderCreditedPct.value;
  const raw = (reg / t) * 100;
  return Math.max(0, Math.min(raw, 100 - creditedPct));
});

/** PV de paquetes fundador ya añadidos al carrito (aún no pagados). */
const founderPvEnCarrito = computed(() =>
  carrito.value.reduce(
    (sum, it) => sum + (it.founder_package ? Number(it.pv_points || 0) * Number(it.cantidad || 0) : 0),
    0
  )
);

/** PV acreditados + lo que llevas en el carrito (proyección). */
const founderProyectadoPv = computed(() => Number(founderPvActual.value) + founderPvEnCarrito.value);

/** Faltantes para 1200 PV considerando también el carrito actual. */
const founderFaltantesProyectados = computed(() =>
  Math.max(0, Number(founderTargetPv.value) - founderProyectadoPv.value)
);

/** Ancho % de la franja “en carrito” dentro de la barra (no solapa lo ya acreditado). */
const founderCartBarPct = computed(() => {
  const t = Number(founderTargetPv.value) || 1200;
  const cart = founderPvEnCarrito.value;
  if (t <= 0 || cart <= 0) return 0;
  const actualPct = founderCreditedPct.value + founderRegBarPct.value;
  const raw = (cart / t) * 100;
  return Math.max(0, Math.min(raw, 100 - actualPct));
});

/** % de la meta ya cubierto con acreditado + carrito (para etiqueta final). */
const founderProyectadoPct = computed(() => {
  const t = Number(founderTargetPv.value) || 1200;
  if (t <= 0) return 0;
  return Math.max(0, Math.min(100, (founderProyectadoPv.value / t) * 100));
});

// Nuevas propiedades para el modal de imagen
const modalOpen = ref(false);
const selectedProducto = ref(null);
const cardFlipped = ref(false);
/** Evita flip automático al abrir con el cursor ya encima (bug cara en blanco / solo overlay). */
const modalHoverFlipReady = ref(false);

const cartStorageKey = computed(() => {
  const id = store.state.auth.user?.id;
  return id ? `mlm_cart_${id}` : "mlm_cart_guest";
});

const userPv = computed(() => {
  const u = store.state.auth.user;
  if (u?.monthly_qualifying_pv != null) return Number(u.monthly_qualifying_pv);
  return null;
});

const pvPedido = computed(() =>
  carrito.value.reduce((sum, it) => sum + Number(it.pv_points || 0) * Number(it.cantidad || 0), 0)
);

const totalPedidoPuntos = computed(() => `${pvPedido.value.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`);

const pvCatalogo = computed(() =>
  productos.value.reduce((sum, p) => sum + Number(p.puntosValor || 0), 0)
);

const totalProductosCatalogo = computed(() => productos.value.length);

function imagenFor(product, index) {
  if (product.image_url) return product.image_url;
  if (!FALLBACK_IMAGES.length) return product.image_url || "";
  return FALLBACK_IMAGES[index % FALLBACK_IMAGES.length];
}

function mapApiProduct(p, index) {
  const precioSocio = Number(p.price);
  const precioClienteRaw = p.price_cliente_preferente != null ? Number(p.price_cliente_preferente) : precioSocio * 1.2;
  const precioCliente = Math.round(precioClienteRaw * 100) / 100;
  return {
    id: p.id,
    product_id: p.id,
    nombre: p.name,
    descripcion: p.description || "—",
    imagen: imagenFor(p, index),
    precioCliente,
    precioSocio,
    puntosValor: Number(p.pv_points || 0),
    categoria: p.category?.name || "General",
    esEstatico: false,
    keyCatalogo: `api-${p.id}`,
  };
}

async function cargarProductos() {
  loading.value = true;
  loadError.value = "";
  try {
    const res = await fetchProductsCatalog();
    const rows = (res.data || []).slice().sort((a, b) => {
      const ida = Number(a.id) || 0;
      const idb = Number(b.id) || 0;
      return ida - idb;
    });
    productos.value = rows.map((p, i) => mapApiProduct(p, i));
  } catch {
    loadError.value = "No se pudo cargar el catálogo. ¿Sesión iniciada y API disponible?";
    productos.value = [];
  } finally {
    loading.value = false;
  }
}

function cartKeyForLine(item) {
  if (item.key) return item.key;
  if (item.founder_package) return `founder-${item.founder_package}`;
  if (item.product_id != null && item.product_id > 0) return `api-${item.product_id}`;
  if (item.esEstatico && item.localId != null) return `static-${item.localId}`;
  return `legacy-${item.product_id ?? "x"}`;
}

function loadCartFromStorage() {
  try {
    const raw = localStorage.getItem(cartStorageKey.value);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) {
        carrito.value = parsed.map((row) => ({
          ...row,
          key: cartKeyForLine(row),
        }));
      }
    }
  } catch {
    carrito.value = [];
  }
}

function cerrarModal() {
  modalOpen.value = false;
  cardFlipped.value = false;
  selectedProducto.value = null;
  modalHoverFlipReady.value = false;
}

// Nueva función para abrir modal con producto
function abrirModalProducto(producto) {
  selectedProducto.value = producto;
  modalOpen.value = true;
  cardFlipped.value = false;
  modalHoverFlipReady.value = false;
}

// Nueva función para girar la tarjeta
function girarTarjeta() {
  cardFlipped.value = !cardFlipped.value;
}

function formatearPrecio(value) {
  const n = Number(value);
  if (Number.isNaN(n)) return "—";
  return new Intl.NumberFormat("es-BO", { style: "currency", currency: "BOB", minimumFractionDigits: 2 }).format(n);
}

function añadirAlCarrito(producto) {
  const key = producto.keyCatalogo || `api-${producto.product_id || producto.id}`;
  const ex = carrito.value.find((x) => x.key === key);
  if (ex) {
    ex.cantidad += 1;
    return;
  }
  carrito.value.push({
    key,
    product_id: producto.product_id || producto.id,
    nombre: producto.nombre,
    imagen: producto.imagen,
    precioSocio: Number(producto.precioSocio) || 0,
    precioCliente: Number(producto.precioCliente) || 0,
    pv_points: Number(producto.puntosValor) || 0,
    cantidad: 1,
    esEstatico: false,
  });
}

function mas(key) {
  const ex = carrito.value.find((x) => x.key === key);
  if (ex) ex.cantidad += 1;
}

function menos(key) {
  const ex = carrito.value.find((x) => x.key === key);
  if (!ex) return;
  ex.cantidad -= 1;
  if (ex.cantidad <= 0) carrito.value = carrito.value.filter((x) => x.key !== key);
}

function quitar(key) {
  carrito.value = carrito.value.filter((x) => x.key !== key);
}

function goToCart() {
  const el = document.getElementById("cart-section");
  if (!el) return;
  el.scrollIntoView({ behavior: "smooth", block: "start" });
  cartJumpPulse.value = true;
  setTimeout(() => {
    cartJumpPulse.value = false;
  }, 1200);
}

const totalPedido = computed(() =>
  carrito.value.reduce((sum, it) => sum + Number(it.precioSocio || 0) * Number(it.cantidad || 0), 0)
);

watch(
  carrito,
  () => {
    try {
      localStorage.setItem(cartStorageKey.value, JSON.stringify(carrito.value));
    } catch {
      /* ignore */
    }
  },
  { deep: true }
);

watch(
  cartStorageKey,
  () => {
    loadCartFromStorage();
  },
  { immediate: true }
);

watch(paymentSettlement, (v) => {
  if (v !== "manual") return;
  if (!paymentMethodOffline.value) paymentMethodOffline.value = "efectivo";
});

function inferTipoPedido() {
  const hasFounder = carrito.value.some((it) => it.founder_package);
  const hasProduct = carrito.value.some((it) => it.product_id && !it.founder_package);
  const hasPackage = carrito.value.some((it) => it.package_id);
  if (hasFounder && (hasProduct || hasPackage)) return "mixto";
  if (hasFounder) return "fundador";
  if (hasPackage && hasProduct) return "mixto";
  if (hasPackage) return "paquete";
  return "producto";
}

async function confirmarPedido() {
  checkoutErr.value = "";
  checkoutMsg.value = "";
  if (!carrito.value.length) return;
  checkoutLoading.value = true;
  try {
    const items = carrito.value.map((it) => {
      const cantidad = Number(it.cantidad);
      if (it.founder_package) {
        return { founder_package: it.founder_package, cantidad };
      }
      if (it.package_id) {
        return { package_id: Number(it.package_id), cantidad };
      }
      return { product_id: Number(it.product_id), cantidad };
    });
    const payload = {
      tipo: inferTipoPedido(),
      payment_settlement: paymentSettlement.value,
      payment_method: paymentSettlement.value === "manual" ? paymentMethodOffline.value : "online",
      items,
    };
    const order = await createOrder(payload);
    carrito.value = [];
    checkoutMsg.value =
      order?.estado === "pendiente_pago"
        ? "Pedido registrado como pendiente de pago. La empresa confirmará tu pago (QR/transferencia/efectivo)."
        : "Pedido registrado correctamente.";
    await cargarProductos();
    await syncProfilePv();
    await loadFounderStatus();
  } catch (e) {
    checkoutErr.value = e.response?.data?.message || "No se pudo crear el pedido.";
  } finally {
    checkoutLoading.value = false;
  }
}

function onFlipEnter() {
  if (!modalHoverFlipReady.value) return;
  cardFlipped.value = true;
}

function onFlipLeave() {
  cardFlipped.value = false;
}

watch(modalOpen, async (open) => {
  if (!open) return;
  modalHoverFlipReady.value = false;
  await nextTick();
  requestAnimationFrame(() => {
    setTimeout(() => {
      modalHoverFlipReady.value = true;
    }, 200);
  });
});

onMounted(async () => {
  loadCartFromStorage();
  await cargarProductos();
  await loadFounderStatus();
});

async function loadFounderStatus() {
  if (!localStorage.getItem("token")) return;
  founderLoading.value = true;
  founderErr.value = "";
  try {
    const r = await fetchFounderStatus();
    const credited = Number(r.pv_actual_credited ?? r.pv_actual ?? 0);
    const regPending = Number(r.pv_registration_pending ?? 0);
    const total = Number(r.pv_actual_total ?? r.pv_actual ?? 0);

    // Fuente “real” de PV personales: store/auth (viene de /me en syncProfilePv).
    // Si el store trae un valor mayor que el endpoint (por latencia), preferimos el store.
    const storePv = Number(store.state.auth.user?.monthly_qualifying_pv ?? NaN);
    const creditedVal = Number.isFinite(storePv) ? storePv : credited;

    founderPvCredited.value = Number.isFinite(creditedVal) ? creditedVal : 0;
    founderPvInscripcionPendiente.value = Number.isFinite(regPending) ? regPending : 0;
    // El endpoint ya trae total; pero si credited fue sustituido por storePv, recalculamos total coherente.
    const totalVal = Number.isFinite(total) ? total : founderPvCredited.value + founderPvInscripcionPendiente.value;
    founderPvActual.value = Number.isFinite(storePv) ? founderPvCredited.value + founderPvInscripcionPendiente.value : totalVal;
    founderTargetPv.value = Number(r.target_pv || 1200);
    founderCompleted.value = !!r.paquete_fundador_completado;
    // Abrir/cerrar en base a PV actuales reales.
    founderModalOpen.value = !founderCompleted.value && founderPvActual.value < founderTargetPv.value;
  } catch {
    founderErr.value = "No se pudo cargar tu progreso del Paquete Fundador.";
  } finally {
    founderLoading.value = false;
  }
}

async function añadirFundadorAlCarrito(pkgKey) {
  if (!localStorage.getItem("token")) return;
  const p = founderPackages.find((x) => x.key === pkgKey);
  if (!p) return;
  founderBuyLoading.value = true;
  founderBuyMsg.value = "";
  founderErr.value = "";
  try {
    const key = `founder-${pkgKey}`;
    const ex = carrito.value.find((x) => x.key === key);
    if (ex) {
      ex.cantidad += 1;
    } else {
      carrito.value.push({
        key,
        founder_package: pkgKey,
        nombre: `Paquete Fundador — ${p.name}`,
        imagen: "",
        precioSocio: p.priceBob,
        precioCliente: p.priceBob,
        pv_points: p.pv,
        cantidad: 1,
        esEstatico: true,
        product_id: null,
      });
    }
    founderBuyMsg.value = "Añadido al carrito. Completa el pago en la sección inferior.";
    await nextTick();
    goToCart();
    setTimeout(() => {
      founderModalOpen.value = false;
      founderBuyMsg.value = "";
    }, 1400);
  } finally {
    founderBuyLoading.value = false;
  }
}
</script>

<template>
  <div class="py-4 container-fluid">
    <div v-if="founderModalOpen" class="modal-backdrop fade show"></div>
    <div
      v-if="founderModalOpen"
      class="modal fade show d-block"
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      @click.self="founderModalOpen = false"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title">Completa tu Paquete Fundador (1200 PV)</h6>
            <button type="button" class="btn-close" aria-label="Cerrar" @click="founderModalOpen = false" />
          </div>
          <div class="modal-body">
            <div class="founder-progress-card border rounded-3 p-3 mb-3 bg-light">
              <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-2">
                <div>
                  <span class="text-xs text-uppercase text-muted d-block">Tienes ahora</span>
                  <span class="h5 mb-0 text-success font-weight-bolder">
                    {{ Number(founderPvActual).toLocaleString("es-BO", { maximumFractionDigits: 2 }) }}
                    <small class="text-sm font-weight-bold">PV</small>
                  </span>
                  <div v-if="founderPvInscripcionPendiente > 0" class="text-xxs text-muted mt-1">
                    Incluye {{ founderPvInscripcionPendiente.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV de tu inscripción iniciada.
                  </div>
                </div>
                <div class="text-end">
                  <span class="text-xs text-uppercase text-muted d-block">Necesitas para completar</span>
                  <span class="h5 mb-0 text-dark font-weight-bolder">
                    {{ founderMissingPv.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }}
                    <small class="text-sm font-weight-bold">PV</small>
                  </span>
                  <span class="text-xxs text-muted d-block">hasta {{ founderTargetPv.toLocaleString("es-BO") }} PV meta</span>
                </div>
              </div>

              <div class="founder-bar-labels d-flex justify-content-between text-xxs text-muted mb-1">
                <span>0 PV</span>
                <span>{{ founderTargetPv.toLocaleString("es-BO") }} PV (meta)</span>
              </div>
              <div
                class="founder-bar-track rounded-pill overflow-hidden d-flex mb-1"
                role="progressbar"
                :aria-valuenow="Math.round(founderProgressPct)"
                aria-valuemin="0"
                aria-valuemax="100"
                :aria-label="`Progreso fundador: ${founderProgressPct.toFixed(0)} por ciento`"
              >
                <div
                  class="founder-bar-segment founder-bar-segment--done"
                  :style="{ width: `${founderCreditedPct}%` }"
                  :title="`${Number(founderPvCredited).toLocaleString('es-BO')} PV acreditados`"
                />
                <div
                  v-if="founderPvInscripcionPendiente > 0"
                  class="founder-bar-segment founder-bar-segment--reg"
                  :style="{ width: `${founderRegBarPct}%` }"
                  :title="`${Number(founderPvInscripcionPendiente).toLocaleString('es-BO')} PV de inscripción iniciada (pendiente)`"
                />
                <div
                  v-if="founderCartBarPct > 0"
                  class="founder-bar-segment founder-bar-segment--cart"
                  :style="{ width: `${founderCartBarPct}%` }"
                  :title="`${founderPvEnCarrito.toLocaleString('es-BO')} PV en carrito (pendiente de pago)`"
                />
                <div class="founder-bar-segment founder-bar-segment--rest flex-grow-1" />
              </div>
              <div class="d-flex flex-wrap gap-3 text-xxs text-muted mb-0">
                <span class="d-flex align-items-center gap-1">
                  <span class="founder-legend founder-legend--done" aria-hidden="true" />
                  Acreditado ({{ founderCreditedPct.toFixed(0) }}%)
                </span>
                <span v-if="founderPvInscripcionPendiente > 0" class="d-flex align-items-center gap-1">
                  <span class="founder-legend founder-legend--reg" aria-hidden="true" />
                  Inscripción iniciada ({{ founderRegBarPct.toFixed(0) }}%)
                </span>
                <span v-if="founderPvEnCarrito > 0" class="d-flex align-items-center gap-1">
                  <span class="founder-legend founder-legend--cart" aria-hidden="true" />
                  En carrito ({{ founderCartBarPct.toFixed(0) }}%)
                </span>
                <span class="d-flex align-items-center gap-1">
                  <span class="founder-legend founder-legend--rest" aria-hidden="true" />
                  Falta: {{ founderMissingPv.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV
                </span>
              </div>
              <p v-if="founderPvEnCarrito > 0" class="text-xs text-primary mt-2 mb-0">
                Con el carrito llegarías a
                <strong>{{ founderProyectadoPv.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV</strong>
                ({{ founderProyectadoPct.toFixed(0) }}% de la meta). Aún faltarían
                <strong>{{ founderFaltantesProyectados.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV</strong>
                tras pagar.
              </p>
            </div>

            <p class="text-xs text-muted mb-3">
              Cada paquete se <strong>suma al carrito</strong>; al confirmar el pedido y completarse el pago, esos PV se
              acreditan a tu progreso fundador.
            </p>

            <div v-if="founderErr" class="alert alert-warning text-white mb-3">{{ founderErr }}</div>
            <div v-if="founderBuyMsg" class="alert alert-success text-white mb-3">{{ founderBuyMsg }}</div>

            <div class="row g-3">
              <div v-for="p in founderPackages" :key="p.key" class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1 text-dark">{{ p.name }}</h6>
                        <div class="text-sm text-secondary mb-1">
                          <strong>{{ p.pv }} PV</strong>
                        </div>
                      </div>
                      <div class="text-end">
                        <div class="text-sm text-dark font-weight-bolder">
                          {{ formatearPrecio(p.priceBob) }}
                        </div>
                      </div>
                    </div>
                    <argon-button
                      color="success"
                      variant="gradient"
                      size="sm"
                      class="w-100 mt-2"
                      :disabled="founderBuyLoading"
                      @click="añadirFundadorAlCarrito(p.key)"
                    >
                      {{ founderBuyLoading ? "Añadiendo…" : "Añadir al carrito y pagar" }}
                    </argon-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="founderModalOpen = false">
              Continuar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="p-4 card-body">
            <h4 class="mb-2 text-dark font-weight-bolder">Catálogo de productos</h4>
            <p class="mb-1 text-sm text-secondary">
              Solo productos reales del catálogo. Pago inmediato (simulado) o pendiente según método; la empresa confirma
              transferencias / QR en administración.
            </p>
            <p v-if="userPv != null" class="mb-0 text-sm">
              <strong>Tu PV mensual (calificación):</strong>
              {{ userPv.toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV
            </p>
            <p v-if="carrito.length" class="mb-0 text-sm text-primary mt-1">
              <strong>PV del carrito actual:</strong> {{ pvPedido.toLocaleString("es-BO") }} PV
            </p>
            <p v-if="loadError" class="text-danger text-sm mt-2 mb-0">{{ loadError }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Total productos"
          :value="String(totalProductosCatalogo)"
          description="<span class='text-sm font-weight-bolder text-primary'>Catálogo API</span>"
          :icon="{
            component: 'ni ni-box-2',
            background: 'bg-gradient-primary',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="PV catálogo"
          :value="String(pvCatalogo.toLocaleString('es-BO'))"
          description="<span class='text-sm font-weight-bolder text-info'>Suma PV</span>"
          :icon="{
            component: 'ni ni-chart-bar-32',
            background: 'bg-gradient-info',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="PV carrito"
          :value="String(pvPedido.toLocaleString('es-BO'))"
          description="<span class='text-sm font-weight-bolder text-success'>Acumulado</span>"
          :icon="{
            component: 'ni ni-money-coins',
            background: 'bg-gradient-success',
            shape: 'rounded-circle',
          }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="En carrito"
          :value="String(carrito.length)"
          description="<span class='text-sm font-weight-bolder text-warning'>Referencias</span>"
          :icon="{
            component: 'ni ni-cart',
            background: 'bg-gradient-warning',
            shape: 'rounded-circle',
          }"
        />
      </div>
    </div>

    <div v-if="loading" class="text-center text-muted py-4">Cargando catálogo…</div>

    <div v-else class="row mt-2">
      <div class="col-12 mb-3">
        <h5 class="text-dark font-weight-bolder mb-1">Catálogo de productos</h5>
        <p v-if="!productos.length" class="text-xs text-secondary mb-0">
          No hay productos activos en el servidor. Carga el catálogo desde administración.
        </p>
      </div>
      <div
        v-for="producto in productos"
        :key="producto.keyCatalogo"
        class="col-lg-4 col-md-6 col-12 mb-4"
      >
        <div class="card border-0 shadow-sm h-100 overflow-hidden card-producto">
          <div class="position-relative cursor-pointer" @click="abrirModalProducto(producto)">
            <img
              :src="producto.imagen"
              :alt="producto.nombre"
              class="card-img-top w-100"
              style="height: 200px; object-fit: cover; cursor: pointer;"
              loading="lazy"
            />
            <span class="badge badge-sm bg-gradient-warning position-absolute top-0 end-0 m-2">
              {{ producto.puntosValor }} PV
            </span>
            <span class="badge badge-sm bg-gradient-dark position-absolute top-0 start-0 m-2 opacity-90">
              {{ producto.categoria }}
            </span>
          </div>
          <div class="card-body p-3 d-flex flex-column">
            <h6 class="mb-2 text-dark font-weight-bolder text-sm">{{ producto.nombre }}</h6>
            <p class="text-xs text-secondary mb-3 flex-grow-1" style="min-height: 2.5rem">
              {{ producto.descripcion }}
            </p>
            <div class="mb-2">
              <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-1">
                <div>
                  <span class="text-xxs text-secondary d-block">Precio público</span>
                  <span class="text-sm text-secondary text-decoration-line-through">{{
                    formatearPrecio(producto.precioCliente)
                  }}</span>
                </div>
                <div class="text-end">
                  <span class="text-xxs text-primary font-weight-bolder d-block">Precio socio</span>
                  <span class="text-sm font-weight-bolder text-primary">{{
                    formatearPrecio(producto.precioSocio)
                  }}</span>
                </div>
              </div>
            </div>
            <div class="mt-auto pt-2">
              <argon-button
                color="primary"
                variant="gradient"
                size="sm"
                class="w-100"
                @click="añadirAlCarrito(producto)"
              >
                <i class="ni ni-cart me-1" aria-hidden="true"></i>
                Añadir al carrito
              </argon-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="carrito.length > 0" id="cart-section" class="row mt-4">
      <div class="col-12">
        <div class="card border-0 shadow" :class="{ 'cart-pulse': cartJumpPulse }">
          <div class="p-3 pb-0 card-header">
            <h6 class="mb-0 font-weight-bolder">Carrito y checkout</h6>
            <p class="text-xs text-secondary mt-1 mb-0">
              Al confirmar se crea el pedido en el servidor; los PV se integran en tu historial MLM.
            </p>
          </div>
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    Cantidad
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    Subtotal
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                    PV
                  </th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in carrito" :key="item.key">
                  <td>
                    <div class="d-flex align-items-center px-2 py-1">
                      <img
                        v-if="item.imagen"
                        :src="item.imagen"
                        :alt="item.nombre"
                        class="avatar avatar-sm rounded me-2"
                        style="object-fit: cover"
                      />
                      <div
                        v-else
                        class="avatar avatar-sm rounded me-2 bg-light border d-flex align-items-center justify-content-center text-xxs text-muted font-weight-bolder"
                      >
                        PF
                      </div>
                      <span class="text-sm font-weight-bold">{{ item.nombre }}</span>
                    </div>
                  </td>
                  <td class="align-middle text-center">
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="menos(item.key)">
                        −
                      </button>
                      <span class="btn btn-sm btn-light disabled text-dark">{{ item.cantidad }}</span>
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="mas(item.key)">
                        +
                      </button>
                    </div>
                  </td>
                  <td class="align-middle text-center text-sm font-weight-bold">
                    {{ formatearPrecio(item.precioSocio * item.cantidad) }}
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-warning"
                      >{{ item.pv_points * item.cantidad }} PV</span
                    >
                  </td>
                  <td class="align-middle text-end">
                    <button type="button" class="btn btn-link text-danger text-sm p-0" @click="quitar(item.key)">
                      Quitar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3">
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label text-xs text-muted mb-1">Forma de pago</label>
                <select v-model="paymentSettlement" class="form-control form-control-sm">
                  <option value="immediate">Inmediato (simulado en sistema)</option>
                  <option value="manual">Efectivo / QR / transferencia (pendiente en empresa)</option>
                </select>
              </div>
              <div v-if="paymentSettlement === 'manual'" class="col-12">
                <label class="form-label text-xs text-muted mb-1">Método de pago</label>
                <select v-model="paymentMethodOffline" class="form-control form-control-sm mb-2">
                  <option value="efectivo">Efectivo</option>
                  <option value="qr">QR</option>
                  <option value="transferencia">Transferencia</option>
                  <option value="tarjeta">Tarjeta (referencia)</option>
                  <option value="otro">Otro</option>
                </select>
                <MlmPaymentMethodPanel
                  v-model="paymentMethodOffline"
                  :show-method-select="false"
                  title="Instrucciones de pago y entrega"
                />
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div class="text-sm">
                <span class="text-secondary">Total Bs.:</span>
                <strong class="text-dark ms-1">{{ formatearPrecio(totalPedido) }}</strong>
              </div>
               <div class="text-sm">
                <span class="text-secondary">Total PVs:</span>
                <strong class="text-dark ms-1">{{ totalPedidoPuntos }}</strong>
              </div>
              <argon-button
                color="success"
                variant="gradient"
                size="sm"
                :disabled="checkoutLoading"
                @click="confirmarPedido"
              >
                {{ checkoutLoading ? "Procesando…" : "Confirmar pedido" }}
              </argon-button>
            </div>
            <p v-if="checkoutErr" class="text-danger text-sm mt-2 mb-0">{{ checkoutErr }}</p>
            <p v-if="checkoutMsg" class="text-success text-sm mt-2 mb-0">{{ checkoutMsg }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Teleport a body: evita overflow/transform del layout y z-index bajo. -->
    <Teleport to="body">
      <div
        v-if="modalOpen && selectedProducto"
        class="product-modal-root"
        role="dialog"
        aria-modal="true"
        @click.self="cerrarModal"
      >
        <div class="product-modal-cardwrap" @click.stop>
          <div class="flip-container">
            <div class="flip-inner" :class="{ 'is-flipped': cardFlipped }">
            <!-- FRONT -->
            <div
              class="flip-face flip-front card border-0 shadow-lg overflow-hidden"
              @mouseenter="onFlipEnter"
              @mouseleave="onFlipLeave"
            >
              <div class="position-relative">
                <img
                  :src="selectedProducto.imagen"
                  :alt="selectedProducto.nombre"
                  class="w-100 modal-img"
                  @click.stop
                />
                <button type="button" class="btn-close modal-close" aria-label="Cerrar" @click="cerrarModal" />
                <span class="badge badge-sm bg-gradient-warning position-absolute top-0 end-0 m-2">
                  {{ selectedProducto.puntosValor }} PV
                </span>
                <span class="badge badge-sm bg-gradient-dark position-absolute top-0 start-0 m-2 opacity-90">
                  {{ selectedProducto.categoria }}
                </span>
              </div>
              <div class="card-body p-3">
                <h5 class="text-dark font-weight-bolder mb-1">{{ selectedProducto.nombre }}</h5>
                <p class="text-xs text-secondary mb-3">Pasa el cursor para ver descripción.</p>
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
                  <div>
                    <div class="text-xxs text-secondary">Precio público</div>
                    <div class="text-sm text-secondary text-decoration-line-through">
                      {{ formatearPrecio(selectedProducto.precioCliente) }}
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="text-xxs text-primary font-weight-bolder">Precio socio</div>
                    <div class="text-lg font-weight-bolder text-primary">
                      {{ formatearPrecio(selectedProducto.precioSocio) }}
                    </div>
                  </div>
                </div>
                <argon-button
                  color="primary"
                  variant="gradient"
                  size="sm"
                  class="w-100 mt-3"
                  @click="añadirAlCarrito(selectedProducto)"
                >
                  <i class="ni ni-cart me-1" aria-hidden="true" /> Añadir al carrito
                </argon-button>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" @click="girarTarjeta">
                  Ver descripción
                </button>
              </div>
            </div>

            <!-- BACK -->
            <div class="flip-face flip-back card border-0 shadow-lg overflow-hidden">
              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="text-dark font-weight-bolder mb-0">Descripción</h6>
                  <button type="button" class="btn-close" aria-label="Cerrar" @click="cerrarModal" />
                </div>
                <p class="text-sm text-secondary mb-0 flex-grow-1" style="white-space: pre-line">
                  {{ selectedProducto.descripcion }}
                </p>
                <div class="pt-3 mt-3 border-top">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-xs text-secondary">PV: {{ selectedProducto.puntosValor }}</span>
                    <span class="badge badge-sm bg-gradient-warning">{{ selectedProducto.categoria }}</span>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" @click="girarTarjeta">
                    Volver a la imagen
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </Teleport>

    <!-- Botón flotante: ir al carrito -->
    <Teleport to="body">
      <button
        v-if="carrito.length"
        type="button"
        class="cart-fab btn btn-success shadow"
        @click="goToCart"
        aria-label="Ir al carrito"
        title="Ir al carrito"
      >
        <i class="ni ni-cart me-2" aria-hidden="true" />
        <span class="me-2">Carrito</span>
        <span class="badge bg-white text-success cart-fab__badge">{{ carrito.length }}</span>
      </button>
    </Teleport>
  </div>
</template>

<style scoped>
.card-producto {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-producto:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
}
.text-xxs {
  font-size: 0.65rem;
}
.cursor-pointer {
  cursor: pointer;
}
.product-modal-root {
  position: fixed;
  inset: 0;
  z-index: 10050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(0, 0, 0, 0.55);
  overflow: auto;
}
.product-modal-cardwrap {
  position: relative;
  z-index: 1;
  width: min(560px, 92vw);
  max-width: 100%;
}
.modal-img {
  height: min(360px, 46vh);
  object-fit: cover;
  display: block;
}
.modal-close {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
  background-color: rgba(255, 255, 255, 0.85);
  border-radius: 999px;
  padding: 0.35rem;
}
.flip-container {
  perspective: 1000px;
  width: 100%;
}
.flip-inner {
  position: relative;
  width: 100%;
  min-height: 520px;
  transition: transform 0.6s;
  transform-style: preserve-3d;
}
.flip-inner.is-flipped {
  transform: rotateY(180deg);
}
.flip-face {
  position: absolute;
  inset: 0;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
}
.flip-back {
  transform: rotateY(180deg);
}

.cart-fab {
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 11000;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.65rem 0.9rem;
}
.cart-fab__badge {
  font-size: 0.75rem;
  border-radius: 999px;
  padding: 0.25rem 0.5rem;
}
.cart-pulse {
  animation: cartPulse 1.2s ease;
}
@keyframes cartPulse {
  0% {
    box-shadow: 0 0 0 0 rgba(84, 177, 68, 0.55);
  }
  70% {
    box-shadow: 0 0 0 16px rgba(84, 177, 68, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(84, 177, 68, 0);
  }
}

/* Barra de progreso Paquete Fundador (PV actuales vs meta) */
.founder-progress-card {
  border-color: rgba(0, 0, 0, 0.06) !important;
}
.founder-bar-track {
  height: 1.1rem;
  background: #e9ecef;
  border: 1px solid rgba(0, 0, 0, 0.06);
}
.founder-bar-segment {
  flex-shrink: 0;
  min-width: 0;
  transition: width 0.35s ease;
}
.founder-bar-segment--done {
  background: linear-gradient(90deg, #2dce89, #54b144);
}
.founder-bar-segment--reg {
  background: repeating-linear-gradient(
    45deg,
    rgba(245, 54, 92, 0.9),
    rgba(245, 54, 92, 0.9) 6px,
    rgba(255, 152, 0, 0.9) 6px,
    rgba(255, 152, 0, 0.9) 12px
  );
}
.founder-bar-segment--cart {
  background: repeating-linear-gradient(
    -45deg,
    #11cdef,
    #11cdef 6px,
    #5ee2ff 6px,
    #5ee2ff 12px
  );
}
.founder-bar-segment--rest {
  background: #dee2e6;
  min-width: 2px;
}
.founder-legend {
  width: 10px;
  height: 10px;
  border-radius: 2px;
  flex-shrink: 0;
}
.founder-legend--done {
  background: linear-gradient(135deg, #2dce89, #54b144);
}
.founder-legend--reg {
  background: #f5365c;
}
.founder-legend--cart {
  background: #11cdef;
}
.founder-legend--rest {
  background: #dee2e6;
  border: 1px solid #ced4da;
}
</style>
