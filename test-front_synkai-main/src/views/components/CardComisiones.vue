<template>
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div
            class="p-4 card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3"
          >
            <div>
              <h2 class="mb-2 text-dark font-weight-bolder">Comisiones</h2>
              <p class="text-muted mb-0 text-sm">Movimientos registrados en tu cuenta (auditoría MLM).</p>
            </div>
            <button
              type="button"
              class="btn btn-sm btn-outline-primary shadow-sm"
              :disabled="loading"
              @click="cargar"
            >
              <i class="ni ni-refresh-02 me-2"></i>
              Actualizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="alert alert-warning text-white mb-3">{{ error }}</div>
    <div v-if="exchangeNote" class="alert alert-secondary border-0 text-dark text-sm mb-3">
      <strong>{{ exchangeNote }}</strong>
      <span class="text-muted ms-2">(Se muestran PV y Bs.)</span>
    </div>

    <!-- Menú rápido (tipo Dashboard) -->
    <div class="row g-2 g-md-3 mb-4">
      <div v-for="t in tabs" :key="t.key" class="col-6 col-md-4 col-xl-2">
        <button
          type="button"
          class="btn p-0 w-100 text-start"
          style="background: transparent"
          @click="activeTab = t.key"
        >
          <div
            class="card border-0 shadow-sm h-100 comi-quick-card text-center py-3 px-2"
            :class="[
              `border-start border-3 border-${t.grad}`,
              activeTab === t.key ? 'comi-quick-card--active' : '',
            ]"
          >
            <div
              class="icon icon-shape rounded-circle mx-auto mb-2 shadow text-white d-flex align-items-center justify-content-center"
              :class="`bg-gradient-${t.grad}`"
              style="width: 44px; height: 44px"
            >
              <i :class="t.icon" aria-hidden="true" />
            </div>
            <span class="text-dark text-xs font-weight-bolder d-block">{{ t.label }}</span>
          </div>
        </button>
      </div>
    </div>

    <!-- Panel general (actual) -->
    <template v-if="activeTab === 'general'">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100 bir-line">
            <div class="card-body py-3">
              <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 1</span>
              <h5 class="mt-1 mb-0 text-dark">{{ formatPvFromBob(birByLevel[1]) }}</h5>
              <p class="text-xs text-muted mb-0 mt-2">≈ {{ formatBs(birByLevel[1]) }}</p>
              <p class="text-xxs text-muted mb-0 mt-1">21 % sobre comisionable (1.ª generación)</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100 bir-line">
            <div class="card-body py-3">
              <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 2</span>
              <h5 class="mt-1 mb-0 text-dark">{{ formatPvFromBob(birByLevel[2]) }}</h5>
              <p class="text-xs text-muted mb-0 mt-2">≈ {{ formatBs(birByLevel[2]) }}</p>
              <p class="text-xxs text-muted mb-0 mt-1">15 % (2.ª generación)</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100 bir-line">
            <div class="card-body py-3">
              <span class="text-xs text-uppercase text-muted font-weight-bold">BIR línea 3</span>
              <h5 class="mt-1 mb-0 text-dark">{{ formatPvFromBob(birByLevel[3]) }}</h5>
              <p class="text-xs text-muted mb-0 mt-2">≈ {{ formatBs(birByLevel[3]) }}</p>
              <p class="text-xxs text-muted mb-0 mt-1">6 % (3.ª generación)</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <span class="text-xs text-uppercase text-muted font-weight-bold">Total histórico</span>
              <h4 class="mt-2 mb-0 text-dark">{{ formatBs(resumen.total_accrued) }}</h4>
              <p class="text-xs text-muted mb-0 mt-2">≈ {{ formatPvFromBob(resumen.total_accrued) }}</p>
              <p class="text-xs text-muted mb-0 mt-2">Suma de eventos de comisión acreditados.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <span class="text-xs text-uppercase text-muted font-weight-bold">Saldo disponible</span>
              <h4 class="mt-2 mb-0 text-dark">{{ formatBs(disponible) }}</h4>
              <p class="text-xs text-muted mb-0 mt-2">Desde billetera (ledger).</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-stretch">
          <div class="card shadow-sm border-0 w-100">
            <div class="card-body d-flex align-items-center justify-content-center">
              <router-link to="/billetera" class="btn btn-success w-100">Ir a billetera</router-link>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0">
          <h6 class="text-dark mb-1">Detalle</h6>
        </div>
        <div class="card-body pt-3">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-xs text-uppercase text-muted font-weight-bold">Tipo</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-center">Periodo</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">PV</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto (Bs)</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in items" :key="row.id">
                  <td class="text-sm">
                    <div class="fw-semibold text-dark">{{ row.type_label }}</div>
                    <div class="text-xs text-muted" v-if="row.level != null">Nivel {{ row.level }}</div>
                  </td>
                  <td class="text-center text-xs text-muted">
                    <div class="text-xs text-muted">{{ row.period_key || "—" }}</div>
                    <div class="text-xxs text-secondary">{{ row.period_display || "—" }}</div>
                  </td>
                  <td class="text-sm text-end">{{ formatPv(row.pv_amount) }}</td>
                  <td class="text-sm text-end">{{ formatBs(row.amount_bob ?? row.amount) }}</td>
                  <td class="text-end">
                    <span class="badge bg-gradient-success text-white text-xxs">{{ row.status }}</span>
                  </td>
                </tr>
                <tr v-if="!loading && items.length === 0">
                  <td colspan="5" class="text-center text-sm text-muted py-4">Sin comisiones registradas aún.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <!-- Panel por bono -->
    <template v-else>
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h6 class="mb-1 text-dark">{{ tabTitle }}</h6>
              <p class="text-sm text-muted mb-0">{{ tabHelp }}</p>
            </div>
            <span class="badge bg-gradient-success text-white text-xxs">Últimos {{ dailyDays }} días</span>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <span class="text-xs text-uppercase text-muted font-weight-bold">Total (rango)</span>
              <h4 class="mt-2 mb-0 text-dark">{{ formatBs(tabTotals.bob) }}</h4>
              <p class="text-xs text-muted mb-0 mt-2">≈ {{ formatPvFromBob(tabTotals.bob) }}</p>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header border-0 pb-0">
              <h6 class="text-dark mb-1">Listado diario</h6>
              <p class="text-xs text-muted mb-0">Si un día no hubo bono, se muestra 0.</p>
            </div>
            <div class="card-body pt-3">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-xs text-uppercase text-muted font-weight-bold">Día</th>
                      <th class="text-xs text-uppercase text-muted font-weight-bold text-end">PV</th>
                      <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto (Bs)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="r in dailyRows" :key="r.dayKey">
                      <td class="text-sm">
                        <div class="fw-semibold text-dark">{{ r.label }}</div>
                        <div class="text-xxs text-muted">{{ r.dayKey }}</div>
                      </td>
                      <td class="text-sm text-end">{{ formatPv(r.pv) }}</td>
                      <td class="text-sm text-end">{{ formatBs(r.bob) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0">
          <h6 class="text-dark mb-1">Eventos</h6>
          <p class="text-xs text-muted mb-0">Detalle de eventos del bono seleccionado.</p>
        </div>
        <div class="card-body pt-3">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-xs text-uppercase text-muted font-weight-bold">Fecha</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">PV</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Monto (Bs)</th>
                  <th class="text-xs text-uppercase text-muted font-weight-bold text-end">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in tabItems" :key="row.id">
                  <td class="text-sm text-muted">{{ formatFecha(row.created_at) }}</td>
                  <td class="text-sm text-end">{{ formatPv(row.pv_amount) }}</td>
                  <td class="text-sm text-end">{{ formatBs(row.amount_bob ?? row.amount) }}</td>
                  <td class="text-end">
                    <span class="badge bg-gradient-success text-white text-xxs">{{ row.status }}</span>
                  </td>
                </tr>
                <tr v-if="!loading && tabItems.length === 0">
                  <td colspan="4" class="text-center text-sm text-muted py-4">Sin eventos para este bono.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { fetchCommissions } from "@/services/me";
import { fetchWalletBalance } from "@/services/wallet";

export default {
  name: "CardComisiones",
  data() {
    return {
      loading: false,
      error: null,
      resumen: { total_accrued: "0" },
      birByLevel: { 1: "0", 2: "0", 3: "0" },
      items: [],
      binaryHybrid: null,
      disponible: "0",
      exchange: null,
      activeTab: "general",
      dailyDays: 14,
    };
  },
  mounted() {
    this.cargar();
    this._refreshTimer = setInterval(() => {
      if (typeof document !== "undefined" && document.visibilityState === "hidden") {
        return;
      }
      this.cargar(true);
    }, 15000);
    this._onVisibility = () => {
      if (document.visibilityState === "visible") {
        this.cargar(true);
      }
    };
    document.addEventListener("visibilitychange", this._onVisibility);
  },
  beforeUnmount() {
    if (this._onVisibility) {
      document.removeEventListener("visibilitychange", this._onVisibility);
    }
    if (this._refreshTimer) {
      clearInterval(this._refreshTimer);
      this._refreshTimer = null;
    }
  },
  methods: {
    formatBs(v) {
      const n = Number(v);
      if (Number.isNaN(n)) {
        return "—";
      }
      return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
      }).format(n);
    },
    formatPv(v) {
      if (v === null || v === undefined || v === "") return "—";
      const n = Number(v);
      if (Number.isNaN(n)) return "—";
      return `${n.toLocaleString("es-BO", { maximumFractionDigits: 2 })} PV`;
    },
    formatPvFromBob(bob) {
      const rate = Number(this.exchange?.bob_per_pv || 7);
      const n = Number(bob);
      if (Number.isNaN(n) || !rate) return "—";
      return this.formatPv(n / rate);
    },
    formatFecha(iso) {
      if (!iso) return "—";
      try {
        return new Date(iso).toLocaleString("es-BO", { year: "numeric", month: "2-digit", day: "2-digit" });
      } catch {
        return String(iso);
      }
    },
    dayKeyFromIso(iso) {
      try {
        const d = new Date(iso);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        return `${y}-${m}-${day}`;
      } catch {
        return null;
      }
    },
    isoMondayFromWeekKey(weekKey) {
      // weekKey esperado: "YYYY-Www" (ISO week). Retorna fecha ISO del lunes.
      const m = String(weekKey || "").match(/^(\d{4})-W(\d{2})$/);
      if (!m) return null;
      const y = Number(m[1]);
      const w = Number(m[2]);
      if (!y || !w) return null;

      // ISO week algorithm: Monday of week 1 is the Monday of the week containing Jan 4.
      const jan4 = new Date(Date.UTC(y, 0, 4, 12, 0, 0));
      const day = jan4.getUTCDay() || 7; // 1..7
      const mondayWeek1 = new Date(jan4);
      mondayWeek1.setUTCDate(jan4.getUTCDate() - (day - 1));

      const monday = new Date(mondayWeek1);
      monday.setUTCDate(mondayWeek1.getUTCDate() + (w - 1) * 7);

      const yy = monday.getUTCFullYear();
      const mm = String(monday.getUTCMonth() + 1).padStart(2, "0");
      const dd = String(monday.getUTCDate()).padStart(2, "0");
      return `${yy}-${mm}-${dd}`;
    },
    buildDailyRows(typeKey) {
      const mapType = this.tabTypeMap[typeKey] || null;
      const wanted = mapType?.types || [];
      // Importante: para Binario híbrido inyectamos filas informativas en `tabItems`,
      // así que el listado diario debe agrupar desde esas filas (no solo desde `items`).
      const base =
        typeKey === this.activeTab && Array.isArray(this.tabItems)
          ? this.tabItems
          : Array.isArray(this.items)
            ? this.items
            : [];
      const rows = base.filter((r) => wanted.includes(r.type));
      const byDay = {};
      for (const r of rows) {
        const k = this.dayKeyFromIso(r.created_at);
        if (!k) continue;
        if (!byDay[k]) byDay[k] = { bob: 0, pv: 0 };
        byDay[k].bob += Number(r.amount_bob ?? r.amount ?? 0) || 0;
        byDay[k].pv += Number(r.pv_amount ?? 0) || 0;
      }
      const out = [];
      const today = new Date();
      // Orden: primero hoy, luego hacia atrás (hoy, ayer, ...).
      for (let i = 0; i < this.dailyDays; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() - i);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        const k = `${y}-${m}-${day}`;
        const label = d.toLocaleDateString("es-BO", { weekday: "short", day: "2-digit", month: "short" });
        out.push({
          dayKey: k,
          label,
          bob: byDay[k]?.bob ?? 0,
          pv: byDay[k]?.pv ?? 0,
        });
      }
      return out;
    },
    async cargar(silent = false) {
      if (!localStorage.getItem("token")) {
        this.error = "Inicia sesión.";
        return;
      }
      if (!silent) {
        this.loading = true;
      }
      this.error = null;
      try {
        const [c, w] = await Promise.all([fetchCommissions(), fetchWalletBalance()]);
        this.resumen = c.summary || { total_accrued: "0" };
        this.exchange = c.exchange || null;
        const br = c.bir_by_level || {};
        this.birByLevel = {
          1: br[1] ?? br["1"] ?? "0",
          2: br[2] ?? br["2"] ?? "0",
          3: br[3] ?? br["3"] ?? "0",
        };
        this.items = c.items || [];
        this.binaryHybrid = c.binary_hybrid || null;
        this.disponible = w.available || "0";
      } catch {
        if (!silent) {
          this.error = "No se pudieron cargar las comisiones.";
        }
      } finally {
        if (!silent) {
          this.loading = false;
        }
      }
    },
  },
  computed: {
    exchangeNote() {
      const bpp = this.exchange?.bob_per_pv;
      if (!bpp) return "";
      return `Tipo de cambio: 1 PV = ${bpp} Bs`;
    },
    tabs() {
      return [
        { key: "general", label: "General", icon: "ni ni-archive-2", grad: "primary" },
        { key: "venta_directa", label: "Venta directa", icon: "ni ni-basket", grad: "success" },
        { key: "binary", label: "Binario", icon: "ni ni-chart-pie-35", grad: "warning" },
        { key: "leadership", label: "Liderazgo", icon: "ni ni-trophy", grad: "info" },
        { key: "bir", label: "Inicio rápido", icon: "ni ni-badge", grad: "danger" },
        { key: "residual", label: "Residual", icon: "ni ni-money-coins", grad: "dark" },
      ];
    },
    tabTypeMap() {
      return {
        venta_directa: {
          title: "Bono de venta directa",
          help:
            "Se genera cuando un cliente preferente compra productos. El sistema registra un evento de comisión 'venta_directa' para el patrocinador.",
          types: ["venta_directa"],
        },
        binary: {
          title: "Bono binario",
          help:
            "Modo híbrido (B): cada día se empareja pierna débil (min) con carry; el bono diario se guarda en la base de datos. El pago que ves en comisiones suele ser semanal, con tope semanal en BOB.",
          types: ["binary"],
        },
        leadership: {
          title: "Bono de liderazgo",
          help:
            "Se calcula por periodos (mensual). Requiere racha (meses consecutivos) y aplica una tasa sobre PV según las reglas de liderazgo.",
          types: ["leadership"],
        },
        bir: {
          title: "Bono de inicio rápido (BIR)",
          help:
            "Se acredita en el primer pedido completado del socio con paquete (inscripción): 21% línea 1, 15% línea 2, 6% línea 3 sobre la base configurada (PV o monto comisionable).",
          types: ["bir"],
        },
        residual: {
          title: "Bono residual",
          help:
            "Se calcula mensual y depende del rango efectivo. Aplica porcentajes por generación sobre PV comisionable de pedidos según la matriz configurada.",
          types: ["residual"],
        },
      };
    },
    tabTitle() {
      return this.tabTypeMap[this.activeTab]?.title || "Bono";
    },
    tabHelp() {
      return this.tabTypeMap[this.activeTab]?.help || "";
    },
    tabItems() {
      const mapType = this.tabTypeMap[this.activeTab];
      if (!mapType) return [];
      const wanted = mapType.types || [];
      // Binario híbrido: mostrar detalle diario (informativo) + pagos semanales (reales).
      if (this.activeTab === "binary" && this.binaryHybrid?.enabled) {
        const daily = Array.isArray(this.binaryHybrid.daily) ? this.binaryHybrid.daily : [];
        const weekly = Array.isArray(this.binaryHybrid.weekly) ? this.binaryHybrid.weekly : [];

        const dailyRows = daily.map((d) => ({
          id: `binary_daily:${d.day_key}`,
          type: "binary",
          type_label: "Bono binario (diario)",
          amount_bob: d.daily_bonus_bob,
          pv_amount: d.matched_pv,
          // Mediodía UTC para evitar que en algunas zonas horarias “cambie de día” al parsear.
          created_at: `${d.day_key}T12:00:00.000Z`,
          period_key: d.day_key,
          period_display: d.day_key,
          status: "info",
        }));

        const weeklyRows = weekly.map((w) => ({
          id: `binary_weekly:${w.week_key}`,
          type: "binary",
          type_label: "Bono binario (pago semanal)",
          amount_bob: w.paid_weekly_bonus_bob,
          pv_amount: null,
          created_at: `${this.isoMondayFromWeekKey(w.week_key) || "1970-01-01"}T12:00:00.000Z`,
          period_key: w.week_key,
          period_display: w.week_key,
          status: "accrued",
        }));

        const merged = [...dailyRows, ...weeklyRows];
        return merged;
      }

      return (this.items || []).filter((r) => wanted.includes(r.type));
    },
    tabTotals() {
      const rows = this.tabItems;
      let bob = 0;
      for (const r of rows) {
        bob += Number(r.amount_bob ?? r.amount ?? 0) || 0;
      }
      return { bob };
    },
    dailyRows() {
      return this.buildDailyRows(this.activeTab);
    },
  },
};
</script>

<style scoped>
.text-xxs {
  font-size: 0.65rem;
}
.bir-line {
  border-left: 3px solid #54b144;
}
.bg-gradient-success {
  background: linear-gradient(87deg, #54b144 0, #3d8b35 100%) !important;
}
.comi-quick-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 0.65rem;
}
.comi-quick-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1.15rem rgba(0, 0, 0, 0.12) !important;
}
.comi-quick-card--active {
  outline: 2px solid rgba(84, 177, 68, 0.35);
}
</style>
