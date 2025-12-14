/**
 * Simple API client for the FlightPHP backend.
 * - Stores token in localStorage: authToken
 * - Base URL in localStorage: apiBaseUrl (optional)
 */
(function (window) {
  const App = (window.App = window.App || {});

  App.getApiBaseUrl = function () {
    return localStorage.getItem('apiBaseUrl') || 'http://localhost:8000';
  };

  App.getToken = function () {
    return localStorage.getItem('authToken');
  };

  App.setToken = function (token) {
    if (!token) localStorage.removeItem('authToken');
    else localStorage.setItem('authToken', token);
  };

  App.apiFetch = async function (path, options) {
    const base = App.getApiBaseUrl().replace(/\/+$/, '');
    const url = base + path;

    const opts = options || {};
    opts.headers = opts.headers || {};

    // Always accept JSON
    opts.headers['Accept'] = 'application/json';

    // If we send a body object, set JSON headers
    if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(opts.body);
    }

    // Auth header if token exists
    const token = App.getToken();
    if (token) {
      opts.headers['Authorization'] = 'Bearer ' + token;
    }

    const res = await fetch(url, opts);

    // Try parse JSON, but don’t fail hard if body is empty
    let data = null;
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json')) {
      data = await res.json().catch(() => null);
    } else {
      data = await res.text().catch(() => null);
    }

    if (!res.ok) {
      const msg =
        (data && data.message) ||
        (data && data.error) ||
        (typeof data === 'string' && data) ||
        ('Request failed: ' + res.status);
      const err = new Error(msg);
      err.status = res.status;
      err.data = data;
      throw err;
    }

    return data;
  };
})(window);