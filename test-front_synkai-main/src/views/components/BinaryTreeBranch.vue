<script setup>
import { computed, ref } from "vue";
import { fetchBinaryChildren } from "@/services/me";
import BinaryTreeBranch from "./BinaryTreeBranch.vue";

const props = defineProps({
  node: { type: Object, required: true },
  level: { type: Number, default: 1 },
  maxDepth: { type: Number, default: 12 },
  compact: { type: Boolean, default: false },
});

const isExpanded = ref(false);
const isLoading = ref(false);
const loadError = ref("");

const left = ref(props.node.left ?? null);
const right = ref(props.node.right ?? null);
const childrenLoaded = ref(Boolean(props.node.left || props.node.right));

const infoOpen = ref(false);

const initials = computed(() => {
  const name = String(props.node?.name || "").trim();
  const parts = name.split(/\s+/).filter(Boolean);
  const a = parts[0]?.[0] || "U";
  const b = parts[1]?.[0] || "";
  return `${a}${b}`.toUpperCase();
});

const statusClass = computed(() => (props.node?.is_active ? "bt-node--active" : "bt-node--inactive"));

const canExpand = computed(() => {
  if (props.level >= props.maxDepth) return false;
  if (childrenLoaded.value) return Boolean(left.value || right.value);
  return Boolean(props.node?.has_children);
});

async function toggleExpand() {
  if (!canExpand.value) return;
  if (!isExpanded.value && !childrenLoaded.value) {
    await loadChildren();
  }
  isExpanded.value = !isExpanded.value;
}

async function loadChildren() {
  isLoading.value = true;
  loadError.value = "";
  try {
    const res = await fetchBinaryChildren(props.node.id);
    const n = res?.node || null;
    left.value = n?.left || null;
    right.value = n?.right || null;
    childrenLoaded.value = true;
  } catch {
    loadError.value = "No se pudieron cargar los hijos.";
  } finally {
    isLoading.value = false;
  }
}

function openInfo() {
  infoOpen.value = true;
}
function closeInfo() {
  infoOpen.value = false;
}
</script>

<template>
  <div class="bt-branch" :class="{ 'bt-branch--compact': compact }">
    <div class="bt-node" :class="statusClass">
      <div class="bt-node__avatar">
        <button type="button" class="bt-bubble bt-bubble--info" title="Información" @click.stop="openInfo">
          inf
        </button>
        <div class="bt-node__circle" role="button" tabindex="0" @click="openInfo" @keydown.enter="openInfo">
          <span class="bt-node__initials">{{ initials }}</span>
        </div>
        <button
          type="button"
          class="bt-bubble bt-bubble--expand"
          :class="{ 'bt-bubble--disabled': !canExpand }"
          :disabled="!canExpand || isLoading"
          title="Expandir"
          @click.stop="toggleExpand"
        >
          <span v-if="isLoading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
          <span v-else>+</span>
        </button>
      </div>

      <!--<div class="bt-node__meta">
        <div class="bt-node__name text-truncate">{{ node.name }}</div>
        <div class="bt-node__code text-truncate">{{ node.code }}</div>
      </div>-->
    </div>

    <div v-if="loadError" class="text-danger text-xxs mt-1">{{ loadError }}</div>

    <Transition name="bt-expand">
      <div v-if="isExpanded" class="bt-children">
        <div class="bt-children__row">
          <div class="bt-child">
            <BinaryTreeBranch
              v-if="left"
              :node="left"
              :level="level + 1"
              :max-depth="maxDepth"
              compact
            />
            <div v-else class="bt-empty">
              <div class="bt-empty__dot"></div>
            </div>
          </div>

          <div class="bt-child">
            <BinaryTreeBranch
              v-if="right"
              :node="right"
              :level="level + 1"
              :max-depth="maxDepth"
              compact
            />
            <div v-else class="bt-empty">
              <div class="bt-empty__dot"></div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <div v-if="infoOpen" class="bt-modal-backdrop" @click="closeInfo"></div>
    <div v-if="infoOpen" class="bt-modal" role="dialog" aria-modal="true" @click.self="closeInfo">
      <div class="bt-modal__card card border-0 shadow">
        <div class="card-body p-3">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="min-w-0">
              <div class="fw-bold text-dark text-truncate">{{ node.name }}</div>
              <div class="text-xxs text-muted text-truncate">{{ node.code }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="closeInfo">Cerrar</button>
          </div>

          <div class="mt-2 text-sm">
            <div class="d-flex justify-content-between">
              <span class="text-muted">Teléfono</span>
              <span class="text-dark">{{ node.phone || "—" }}</span>
            </div>
          <div class="d-flex justify-content-between mt-1">
            <span class="text-muted">Correo</span>
            <span class="text-dark text-truncate ms-2" style="max-width: 60%">{{ node.email || "—" }}</span>
          </div>
          <div class="d-flex justify-content-between mt-1">
            <span class="text-muted">Rango</span>
            <span class="text-dark fw-bold">{{ node.rank || "—" }}</span>
          </div>
          <div class="d-flex justify-content-between mt-1">
            <span class="text-muted">PV acumulados</span>
            <span class="text-dark fw-bold">{{ Number(node.pv_accumulated || 0).toLocaleString("es-BO", { maximumFractionDigits: 2 }) }} PV</span>
          </div>
            <div class="d-flex justify-content-between mt-1">
              <span class="text-muted">Estado</span>
              <span class="fw-bold" :class="node.is_active ? 'text-success' : 'text-danger'">
                {{ node.is_active ? "ACTIVO" : "INACTIVO" }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-xxs {
  font-size: 0.72rem;
}

.bt-branch {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.bt-node {
  position: relative;
  width: min(220px, 100%);
  display: grid;
  grid-template-columns: 76px 1fr;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.75rem;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 10px 30px rgba(34, 41, 47, 0.08);
  border: 1px solid rgba(34, 41, 47, 0.06);
}

.bt-node__avatar {
  position: relative;
  width: 70px;
  height: 46px;
  display: grid;
  place-items: center;
}

.bt-node__circle {
  width: 46px;
  height: 46px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  cursor: pointer;
  user-select: none;
  border: 3px solid rgba(255, 255, 255, 0.9);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.bt-node--active .bt-node__circle {
  background: linear-gradient(135deg, #2ecc71, #1e9f57);
}
.bt-node--inactive .bt-node__circle {
  background: linear-gradient(135deg, #ff4d4f, #c81d25);
}

.bt-node__initials {
  color: #fff;
  font-weight: 800;
  font-size: 0.85rem;
  letter-spacing: 0.02em;
}

.bt-node__meta {
  min-width: 0;
}
.bt-node__name {
  font-weight: 800;
  color: #111827;
  font-size: 0.92rem;
  line-height: 1.1rem;
}
.bt-node__code {
  font-size: 0.72rem;
  color: #6b7280;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

.bt-bubble {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: none;
  background: #2f7d32;
  color: #fff;
  display: grid;
  place-items: center;
  font-weight: 900;
  font-size: 0.7rem;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}
.bt-bubble:hover {
  filter: brightness(0.98);
}
.bt-bubble--info {
  left: -10px;
}
.bt-bubble--expand {
  right: -10px;
}
.bt-bubble--disabled {
  opacity: 0.55;
  background: rgba(47, 125, 50, 0.6);
}

.bt-children {
  width: 100%;
  margin-top: 14px;
  padding-top: 10px;
  position: relative;
}

.bt-children::before {
  content: "";
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 16px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.22);
}

.bt-children__row {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  align-items: start;
  position: relative;
  padding-top: 12px;
}

.bt-children__row::before {
  content: "";
  position: absolute;
  top: 10px;
  left: 14%;
  right: 14%;
  height: 2px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.22);
}

.bt-child {
  display: flex;
  justify-content: center;
  position: relative;
  padding-top: 10px;
}

.bt-child::before {
  content: "";
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 14px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.22);
}

.bt-empty {
  width: min(220px, 100%);
  display: grid;
  place-items: center;
  padding: 1.15rem 0;
}
.bt-empty__dot {
  width: 14px;
  height: 14px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.15);
}

.bt-expand-enter-active,
.bt-expand-leave-active {
  transition: all 180ms ease;
}
.bt-expand-enter-from,
.bt-expand-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.bt-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.4);
  z-index: 1090;
}
.bt-modal {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  z-index: 1100;
  padding: 1rem;
}
.bt-modal__card {
  width: min(420px, 100%);
  border-radius: 16px;
}

@media (max-width: 576px) {
  .bt-children__row {
    grid-template-columns: 1fr;
  }
}
</style>
