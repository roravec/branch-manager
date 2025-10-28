import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE,
});

api.interceptors.request.use((config) => {
  const accessToken = sessionStorage.getItem("access_token");
  if (accessToken) {
    config.headers["Authorization"] = `Bearer ${accessToken}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      const refreshToken = sessionStorage.getItem("refresh_token");
      if (refreshToken) {
        try {
          const res = await axios.post(import.meta.env.VITE_API_BASE + "/authrefresh", null, {
            headers: { "X-Refresh-Token": refreshToken },
          });
          console.log(res);
          sessionStorage.setItem("access_token", res.data.access_token);
          sessionStorage.setItem("refresh_token", res.data.refresh_token);
          originalRequest.headers["Authorization"] = `Bearer ${res.data.access_token}`;
          return api(originalRequest);
        } catch (err) {
          sessionStorage.removeItem("access_token");
          sessionStorage.removeItem("refresh_token");
          window.location.reload();
        }
      }
    }
    return Promise.reject(error);
  }
);

export default api;
