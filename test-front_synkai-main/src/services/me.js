import api from "./api";

export function fetchDashboard() {
  return api.get("/me/dashboard").then((r) => r.data);
}

export function fetchReferrals() {
  return api.get("/me/referrals").then((r) => r.data);
}

export function fetchProfile() {
  return api.get("/me").then((r) => r.data);
}

export function fetchCommissions() {
  return api.get("/me/commissions").then((r) => r.data);
}

export function fetchNotifications() {
  return api.get("/me/notifications").then((r) => r.data);
}

export function clearNotificationsAll() {
  return api.delete("/me/notifications", { data: { all: true } }).then((r) => r.data);
}

export function dismissNotificationsByIds(ids = []) {
  return api.delete("/me/notifications", { data: { ids } }).then((r) => r.data);
}

export function fetchFounderStatus() {
  return api.get("/me/founder").then((r) => r.data);
}

export function postFounderPurchase(payload = {}) {
  return api.post("/me/founder/purchase", payload).then((r) => r.data);
}

export function fetchOrders(params) {
  return api.get("/orders", { params }).then((r) => r.data);
}

export function fetchWalletTransactions() {
  return api.get("/wallet/transactions").then((r) => r.data);
}

export function updateMyProfile(payload) {
  return api.put("/me/profile", payload).then((r) => r.data);
}

export function changeMyPassword(payload) {
  return api.put("/me/password", payload).then((r) => r.data);
}

export function fetchMyLanding() {
  return api.get("/me/landing").then((r) => r.data);
}

export function updateMyLanding(payload) {
  return api.put("/me/landing", payload).then((r) => r.data);
}

export function fetchPublicLanding(memberCode) {
  return api.get(`/public/landing/${encodeURIComponent(memberCode)}`).then((r) => r.data);
}

export function fetchWalletSettings() {
  return api.get("/me/wallet-settings").then((r) => r.data);
}

export function updateWalletSettings(payload) {
  return api.put("/me/wallet-settings", payload).then((r) => r.data);
}

export function fetchSupportTickets() {
  return api.get("/support/tickets").then((r) => r.data);
}

export function createSupportTicket(payload) {
  return api.post("/support/tickets", payload).then((r) => r.data);
}

export function fetchPackages() {
  return api.get("/packages").then((r) => r.data);
}

export function fetchBinaryTree() {
  return api.get("/me/binary-tree").then((r) => r.data);
}

export function fetchBinaryChildren(parentId = null) {
  const params = {};
  if (parentId !== null && parentId !== undefined) params.parent_id = parentId;
  return api.get("/me/binary-tree/children", { params }).then((r) => r.data);
}

export function searchBinaryTree(q) {
  return api.get("/me/binary-tree/search", { params: { q } }).then((r) => r.data);
}

export function fetchUnilevelTree(depth = 3) {
  return api.get("/me/unilevel-tree", { params: { depth } }).then((r) => r.data);
}

export function fetchProductsCatalog() {
  return api.get("/products").then((r) => r.data);
}

export function createOrder(payload) {
  return api.post("/orders", payload).then((r) => r.data);
}

export function postBinaryPlacement(payload = {}) {
  return api.post("/me/binary-placement", payload).then((r) => r.data);
}
