import api from "./api";

export function fetchSponsorByCode(code) {
  return api
    .get(`/public/sponsors/${encodeURIComponent(String(code).trim())}`)
    .then((r) => r.data);
}
