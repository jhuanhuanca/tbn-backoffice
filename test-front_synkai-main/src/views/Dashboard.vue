<script setup>
import MiniStatisticsCard from "@/examples/Cards/MiniStatisticsCard.vue";
import GradientLineChart from "@/examples/Charts/GradientLineChart.vue";
import Carousel from "./components/Carousel.vue";
import CategoriesList from "./components/CategoriesList.vue";

const kpis = {
  availableBalance: "$1,250.00",
  dailyEarnings: "$42.80",
  totalEarnings: "$8,930.15",
  leftVolume: "12,450 PV",
  rightVolume: "10,980 PV",
  currentRank: "Bronce",
  directAffiliates: "7",
  activityStatus: "Activo",
};

const binaryLegs = [
  {
    side: "Izquierda",
    volume: "12,450 PV",
    carryOver: "1,120 PV",
    matched: "10,000 PV",
    status: "Calificada",
  },
  {
    side: "Derecha",
    volume: "10,980 PV",
    carryOver: "860 PV",
    matched: "10,000 PV",
    status: "Calificada",
  },
];
</script>
<template>
  <div class="py-4 container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Saldo Disponible"
              :value="kpis.availableBalance"
              description="<span
                class='text-sm font-weight-bolder text-success'
                >Listo</span> para retirar"
              :icon="{
                component: 'ni ni-money-coins',
                background: 'bg-gradient-primary',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Ganancias del Día"
              :value="kpis.dailyEarnings"
              description="<span
                class='text-sm font-weight-bolder text-success'
                >+8%</span> vs ayer"
              :icon="{
                component: 'ni ni-chart-bar-32',
                background: 'bg-gradient-danger',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Ganancias Totales"
              :value="kpis.totalEarnings"
              description="<span
                class='text-sm font-weight-bolder text-success'
                >Acumulado</span> en el plan"
              :icon="{
                component: 'ni ni-trophy',
                background: 'bg-gradient-success',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Rango Actual"
              :value="kpis.currentRank"
              description="<span
                class='text-sm font-weight-bolder text-info'
                >Binario híbrido</span> activo"
              :icon="{
                component: 'ni ni-badge',
                background: 'bg-gradient-warning',
                shape: 'rounded-circle',
              }"
            />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Volumen Izquierdo"
              :value="kpis.leftVolume"
              description="<span class='text-sm font-weight-bolder text-primary'>Pierna</span> fuerte"
              :icon="{
                component: 'ni ni-bold-left',
                background: 'bg-gradient-primary',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Volumen Derecho"
              :value="kpis.rightVolume"
              description="<span class='text-sm font-weight-bolder text-primary'>Pierna</span> en crecimiento"
              :icon="{
                component: 'ni ni-bold-right',
                background: 'bg-gradient-info',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Afiliados Directos"
              :value="kpis.directAffiliates"
              description="<span class='text-sm font-weight-bolder text-success'>+2</span> esta semana"
              :icon="{
                component: 'ni ni-single-02',
                background: 'bg-gradient-success',
                shape: 'rounded-circle',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-12">
            <mini-statistics-card
              title="Estado de Actividad"
              :value="kpis.activityStatus"
              description="<span class='text-sm font-weight-bolder text-success'>Calificado</span> hoy"
              :icon="{
                component: 'ni ni-check-bold',
                background: 'bg-gradient-danger',
                shape: 'rounded-circle',
              }"
            />
          </div>
        </div>
        <div class="row">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <!-- line chart -->
            <div class="card z-index-2">
              <gradient-line-chart
                id="chart-line"
                title="Volumen Binario (últimos 9 períodos)"
                description="<i class='fa fa-arrow-up text-success'></i>
      <span class='font-weight-bold'>Piernas</span> izquierda vs derecha"
                :chart="{
                  labels: [
                    'P1',
                    'P2',
                    'P3',
                    'P4',
                    'P5',
                    'P6',
                    'P7',
                    'P8',
                    'P9',
                  ],
                  datasets: [
                    {
                      label: 'Volumen Izq.',
                      data: [120, 160, 210, 190, 260, 240, 310, 280, 330],
                    },
                    {
                      label: 'Volumen Der.',
                      data: [90, 140, 180, 170, 230, 220, 290, 250, 300],
                    },
                  ],
                }"
              />
            </div>
          </div>
          <div class="col-lg-5 col-12 d-none d-lg-block">
            <carousel />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-7 col-12 mb-lg-0 mb-4">
            <div class="card">
              <div class="p-3 pb-0 card-header">
                <div class="d-flex justify-content-between">
                  <h6 class="mb-2">Resumen de Piernas (Binario)</h6>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-items-center">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                        Pierna
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Volumen
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Arrastre
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Matcheado
                      </th>
                      <th
                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center"
                      >
                        Estado
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(leg, index) in binaryLegs" :key="index">
                      <td class="w-30">
                        <div class="px-2 py-1 d-flex align-items-center">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">{{ leg.side }}</h6>
                            <p class="mb-0 text-xs text-secondary">
                              Volumen y arrastre por pierna
                            </p>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center">
                        <h6 class="mb-0 text-sm">{{ leg.volume }}</h6>
                      </td>
                      <td class="align-middle text-center">
                        <h6 class="mb-0 text-sm">{{ leg.carryOver }}</h6>
                      </td>
                      <td class="align-middle text-center">
                        <h6 class="mb-0 text-sm">{{ leg.matched }}</h6>
                      </td>
                      <td class="align-middle text-center">
                        <span
                          class="badge badge-sm bg-gradient-success"
                          v-if="leg.status === 'Calificada'"
                          >{{ leg.status }}</span
                        >
                        <span class="badge badge-sm bg-gradient-secondary" v-else
                          >{{ leg.status }}</span
                        >
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-5 col-12">
            <categories-list
              :categories="[
                {
                  icon: {
                    component: 'ni ni-credit-card',
                    background: 'dark',
                  },
                  label: 'Saldo y Retiros',
                  description:
                    'Disponible <strong>' + kpis.availableBalance + '</strong>',
                },
                {
                  icon: {
                    component: 'ni ni-chart-bar-32',
                    background: 'dark',
                  },
                  label: 'Ganancia del Día',
                  description: 'Hoy <strong>' + kpis.dailyEarnings + '</strong>',
                },
                {
                  icon: { component: 'ni ni-box-2', background: 'dark' },
                  label: 'Volumen Binario',
                  description:
                    'Izq <strong>' +
                    kpis.leftVolume +
                    '</strong> · Der <strong>' +
                    kpis.rightVolume +
                    '</strong>',
                },
                {
                  icon: { component: 'ni ni-badge', background: 'dark' },
                  label: 'Rango y Actividad',
                  description:
                    'Rango <strong>' +
                    kpis.currentRank +
                    '</strong> · Estado <strong>' +
                    kpis.activityStatus +
                    '</strong>',
                },
              ]"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
