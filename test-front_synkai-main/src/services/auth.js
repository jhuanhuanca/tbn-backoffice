import api from "./api";

export function resendVerificationEmail(email) {
  return api.post("/email/resend-verification", { email });
}
