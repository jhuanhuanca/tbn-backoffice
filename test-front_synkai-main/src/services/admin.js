import api from "./api";

export function fetchAdminDashboard() {
  return api.get("/admin/dashboard");
}

export function fetchAdminWithdrawals(params) {
  return api.get("/admin/withdrawals", { params });
}

export function approveWithdrawal(id) {
  return api.post(`/admin/withdrawals/${id}/approve`);
}

export function rejectWithdrawal(id, payload) {
  return api.post(`/admin/withdrawals/${id}/reject`, payload);
}

export function fetchPeriodClosures(params) {
  return api.get("/admin/reconciliation/period-closures", { params });
}

export function fetchCommissionSummary(params) {
  return api.get("/admin/reconciliation/commission-summary", { params });
}

export function fetchLeadershipMonth(monthKey) {
  return api.get(`/admin/leadership/${monthKey}`);
}

export function fetchAdminCategories() {
  return api.get("/admin/categories");
}

export function fetchAdminProducts() {
  return api.get("/admin/products");
}

export function createAdminProduct(body) {
  return api.post("/admin/products", body);
}

export function updateAdminProduct(id, body) {
  return api.put(`/admin/products/${id}`, body);
}

export function deleteAdminProduct(id) {
  return api.delete(`/admin/products/${id}`);
}

export function fetchAdminPackages() {
  return api.get("/admin/packages");
}

export function createAdminPackage(body) {
  return api.post("/admin/packages", body);
}

export function updateAdminPackage(id, body) {
  return api.put(`/admin/packages/${id}`, body);
}

export function deleteAdminPackage(id) {
  return api.delete(`/admin/packages/${id}`);
}

export function fetchAdminOrders(params) {
  return api.get("/admin/orders", { params });
}

export function confirmOrderPayment(orderId, payload) {
  return api.post(`/admin/orders/${orderId}/confirm-payment`, payload);
}
