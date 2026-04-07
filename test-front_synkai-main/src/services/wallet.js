import api from "./api";

/**
 * @returns {Promise<{ available: string }>}
 */
export function fetchWalletBalance() {
  return api.get("/wallet/balance").then((res) => res.data);
}
