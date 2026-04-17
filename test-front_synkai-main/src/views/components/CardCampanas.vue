<script setup>
import { computed } from "vue";
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";

const stats = computed(() => ({
  active: 12,
  segmented: 1240,
  conversion: 28,
  sent: 5890,
}));

const blocks = [
  {
    title: "Onboarding 72h",
    desc: "Secuencia automática para nuevos referidos: bienvenida, pasos de activación y primer pedido.",
    icon: "ni ni-collection",
    grad: "bg-gradient-primary",
    cta: "Configurar",
  },
  {
    title: "Reactivación 30 días",
    desc: "Detecta socios sin compra reciente y envía recordatorios + contenido de valor.",
    icon: "ni ni-time-alarm",
    grad: "bg-gradient-warning",
    cta: "Crear campaña",
  },
  {
    title: "WhatsApp follow-up",
    desc: "Plantillas para seguimiento de prospectos (sin spam) con tareas y etiquetas.",
    icon: "ni ni-chat-round",
    grad: "bg-gradient-success",
    cta: "Ver plantillas",
  },
  {
    title: "Segmentación de leads",
    desc: "Segmenta por fuente, país, interés, estado (activo/pendiente) y PV mensual.",
    icon: "ni ni-bullet-list-67",
    grad: "bg-gradient-info",
    cta: "Segmentar",
  },
  {
    title: "Campañas por rango",
    desc: "Mensajes y recursos distintos para cada rango (activación, liderazgo, retención).",
    icon: "ni ni-badge",
    grad: "bg-gradient-danger",
    cta: "Definir reglas",
  },
  {
    title: "Calendario de contenidos",
    desc: "Agenda publicaciones y materiales para tu equipo (guiones, flyers, videos).",
    icon: "ni ni-calendar-grid-58",
    grad: "bg-gradient-dark",
    cta: "Abrir calendario",
  },
];
</script>

<template>
  <div class="py-4 container-fluid">
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow">
          <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
              <h3 class="mb-1 text-dark font-weight-bolder">Campañas automáticas</h3>
              <p class="text-sm text-secondary mb-0">
                Automatiza el seguimiento de prospectos y socios, con segmentación y métricas.
              </p>
            </div>
            <button type="button" class="btn btn-success btn-sm shadow-sm">
              <i class="ni ni-fat-add me-2"></i>
              Nueva campaña
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Campañas activas"
          :value="String(stats.active)"
          description="<span class='text-sm font-weight-bolder text-success'>Automatizadas</span> en ejecución"
          :icon="{ component: 'ni ni-send', background: 'bg-gradient-success', shape: 'rounded-circle' }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Leads segmentados"
          :value="String(stats.segmented)"
          description="<span class='text-sm font-weight-bolder text-info'>Audiencias</span> listas"
          :icon="{ component: 'ni ni-tag', background: 'bg-gradient-info', shape: 'rounded-circle' }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Conversión"
          :value="`${stats.conversion}%`"
          description="<span class='text-sm font-weight-bolder text-warning'>Eficiencia</span> (demo)"
          :icon="{ component: 'ni ni-chart-bar-32', background: 'bg-gradient-warning', shape: 'rounded-circle' }"
        />
      </div>
      <div class="col-lg-3 col-md-6 col-12">
        <mini-statistics-card
          title="Mensajes enviados"
          :value="String(stats.sent)"
          description="<span class='text-sm font-weight-bolder text-secondary'>Últimos</span> 30 días (demo)"
          :icon="{ component: 'ni ni-email-83', background: 'bg-gradient-primary', shape: 'rounded-circle' }"
        />
      </div>
    </div>

    <div class="row g-3">
      <div v-for="b in blocks" :key="b.title" class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 campaign-card">
          <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
              <div class="icon icon-shape icon-md shadow text-center" :class="`${b.grad} rounded-circle`">
                <i class="ni text-white opacity-10 text-lg" :class="b.icon" aria-hidden="true"></i>
              </div>
              <div class="min-w-0">
                <div class="text-dark font-weight-bolder mb-1">{{ b.title }}</div>
                <div class="text-sm text-secondary mb-3">{{ b.desc }}</div>
                <button type="button" class="btn btn-sm btn-outline-primary w-100">
                  {{ b.cta }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.campaign-card {
  border-radius: 1rem;
  transition: transform 0.12s ease, box-shadow 0.2s ease;
}
.campaign-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.55rem 1.5rem rgba(0, 0, 0, 0.08) !important;
}
</style>