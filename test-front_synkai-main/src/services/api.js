import axios from "axios";

/**
 * Base URL del API Laravel (`routes/api.php` → prefijo /api).
 * - Desarrollo: proxy en vue.config.js → `VUE_APP_API_URL` = `/api` o vacío
 * - Producción: `VUE_APP_API_URL=https://app.tudominio.com/api` (ver .env.production)
 */
const baseURL = process.env.VUE_APP_API_URL || "/api";

const api = axios.create({
  baseURL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  timeout: 60000,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;
    const url = String(error.config?.url || "");
    const isAuthRoute = url.includes("/login") || url.includes("/register");
    if (status === 401 && !isAuthRoute && typeof window !== "undefined") {
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      const path = window.location.pathname || "/";
      if (!path.includes("signin") && !path.includes("signup")) {
        const q = new URLSearchParams({ redirect: path + (window.location.search || "") });
        window.location.replace(`/signin?${q.toString()}`);
      }
    }
    return Promise.reject(error);
  }
);

export default api;
