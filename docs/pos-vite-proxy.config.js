/**
 * Copy the entire `posDevServerProxy` object into your POS app's vite.config.* under:
 *   export default defineConfig({ server: { proxy: { ...posDevServerProxy } }, ... })
 *
 * If you skip `/sanctum`, the browser will request `http://localhost:5173/sanctum/csrf-cookie`
 * and get **404** — Vite is not Laravel. Same-origin POS flows need `/api`, `/pos`, and `/sanctum`
 * all proxied to the backend.
 *
 * Run Laravel with: php artisan serve (default http://127.0.0.1:8000). On Laragon, set `target`
 * to your vhost, e.g. http://bgs-api-blade.test
 *
 * POS .env (dev, with proxy):
 *   # Leave unset so calls use /api/pos/…, /pos/…, /sanctum/… on :5173 → forwarded to Laravel
 *   ; VITE_API_URL=
 *
 * No proxy (call Laravel directly):
 *   VITE_API_URL=http://127.0.0.1:8000/api
 *   VITE_APP_URL=http://127.0.0.1:8000
 */
export const posDevServerProxy = {
  '/api': {
    target: 'http://127.0.0.1:8000',
    changeOrigin: true,
  },
  // Laravel web.php mirrors these (same controllers as routes/api.php)
  '/pos': {
    target: 'http://127.0.0.1:8000',
    changeOrigin: true,
  },
  '/sanctum': {
    target: 'http://127.0.0.1:8000',
    changeOrigin: true,
  },
};
