import api from "./api";

export function registerPreferredCustomer(body) {
  return api.post("/register/preferred-customer", body);
}
