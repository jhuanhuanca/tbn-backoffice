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

export function fetchOrders(params) {
  return api.get("/orders", { params }).then((r) => r.data);
}

export function fetchWalletTransactions() {
  return api.get("/wallet/transactions").then((r) => r.data);
}

export function fetchPackages() {
  return api.get("/packages").then((r) => r.data);
}

export function fetchBinaryTree() {
  return api.get("/me/binary-tree").then((r) => r.data);
}

export function fetchProductsCatalog() {
  return api.get("/products").then((r) => r.data);
}

export function createOrder(payload) {
  return api.post("/orders", payload).then((r) => r.data);
}

export function postBinaryPlacement(leg) {
  return api.post("/me/binary-placement", { leg }).then((r) => r.data);
}
