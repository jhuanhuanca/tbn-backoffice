<script setup>
import BinaryTreeBranch from "./BinaryTreeBranch.vue";

defineProps({
  branch: {
    type: Object,
    default: null,
  },
  sideClass: {
    type: String,
    default: "bg-gradient-primary",
  },
  small: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div v-if="branch" class="branch-wrap">
    <div class="tree-node" :class="{ small }">
      <span class="node-badge" :class="sideClass">{{ branch.user?.initials || "?" }}</span>
      <span
        class="node-label text-xs text-dark d-block text-center mt-1 text-truncate"
        style="max-width: 92px"
        >{{ branch.user?.name || "—" }}</span
      >
      <span class="text-xxs text-muted d-block text-center">{{ branch.user?.member_code || "" }}</span>
    </div>
    <div v-if="branch.left || branch.right" class="tree-grandchildren mt-2">
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <BinaryTreeBranch
          v-if="branch.left"
          :branch="branch.left"
          side-class="bg-light text-dark"
          small
        />
        <BinaryTreeBranch
          v-if="branch.right"
          :branch="branch.right"
          side-class="bg-light text-dark"
          small
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.branch-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.tree-node.small .node-badge {
  font-size: 0.6rem;
  padding: 0.2rem 0.35rem;
  min-width: 1.75rem;
}
.node-badge {
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  padding: 0.25rem 0.4rem;
  border-radius: 50%;
  min-width: 2rem;
  display: inline-block;
  text-align: center;
}
.text-xxs {
  font-size: 0.6rem;
}
</style>
