import api from "./api";

export function registerPreferredCustomer(body) {
  return api.post("/register/preferred-customer", body);
}

export function resendVerificationEmail(email) {
  return api.post("/email/resend-verification", { email });
}
