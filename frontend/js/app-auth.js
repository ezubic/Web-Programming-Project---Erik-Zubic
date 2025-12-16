(function (window) {
  const App = (window.App = window.App || {});

  function base64UrlDecode(str) {
    // Convert base64url to base64
    str = str.replace(/-/g, '+').replace(/_/g, '/');
    // Pad
    while (str.length % 4) str += '=';
    try {
      return atob(str);
    } catch (e) {
      return null;
    }
  }

  App.decodeJwtPayload = function (token) {
    if (!token) return null;
    const parts = token.split('.');
    if (parts.length < 2) return null;
    const json = base64UrlDecode(parts[1]);
    if (!json) return null;
    try {
      return JSON.parse(json);
    } catch {
      return null;
    }
  };

  App.getUser = function () {
    const raw = localStorage.getItem('authUser');
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch {
      return null;
    }
  };

  App.setUser = function (user) {
    if (!user) localStorage.removeItem('authUser');
    else localStorage.setItem('authUser', JSON.stringify(user));
  };

  App.refreshSessionFromStorage = function () {
    const token = App.getToken ? App.getToken() : localStorage.getItem('authToken');
    if (!token) {
      App.setUser(null);
      return null;
    }
    const payload = App.decodeJwtPayload(token);
    // Expected payload fields: id, email, role, exp
    if (!payload || !payload.email) {
      App.setToken(null);
      App.setUser(null);
      return null;
    }
    App.setUser({
      id: payload.id ?? null,
      email: payload.email,
      role: payload.role || 'user',
      exp: payload.exp ?? null
    });
    return App.getUser();
  };

  App.logout = function () {
    App.setToken(null);
    App.setUser(null);
    // Also clear cart to avoid cross-user confusion
    localStorage.removeItem('cart');
  };

  App.login = async function (email, password) {
    const data = await App.apiFetch('/auth/login', {
      method: 'POST',
      body: { email, password }
    });
    if (!data || !data.token) throw new Error('Login failed: missing token.');
    App.setToken(data.token);
    App.refreshSessionFromStorage();
    return data;
  };

  App.register = async function (email, password) {
    const data = await App.apiFetch('/auth/register', {
      method: 'POST',
      body: { email, password }
    });
    if (!data || !data.token) throw new Error('Register failed: missing token.');
    App.setToken(data.token);
    App.refreshSessionFromStorage();
    return data;
  };
})(window);