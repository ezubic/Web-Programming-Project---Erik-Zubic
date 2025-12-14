(function (window, $) {
  const App = (window.App = window.App || {});

  function setBusy($btn, busy) {
    if (!$btn || !$btn.length) return;
    $btn.prop('disabled', !!busy);
    const txt = $btn.data('txt') || $btn.text();
    if (!busy) $btn.text(txt);
    else {
      $btn.data('txt', txt);
      $btn.text('Please wait...');
    }
  }

  App.mountLogin = function () {
    // If already logged in, go to dashboard
    if (App.isAuthed && App.isAuthed()) {
      window.location.hash = '#dashboard';
      return;
    }

    $('#login-form').off('submit').on('submit', async function (e) {
      e.preventDefault();
      const email = String($('#login-email').val() || '').trim();
      const password = String($('#login-password').val() || '');

      if (!email || !password) return App.flash('warning', 'Email and password are required.');

      const $btn = $('#btn-login');
      try {
        setBusy($btn, true);
        await App.login(email, password);
        App.updateNav();
        App.flash('success', 'Login successful.');
        window.location.hash = '#dashboard';
      } catch (err) {
        App.flash('danger', err.message || 'Login failed.');
      } finally {
        setBusy($btn, false);
      }
    });

    $('#register-form').off('submit').on('submit', async function (e) {
      e.preventDefault();
      const email = String($('#reg-email').val() || '').trim();
      const password = String($('#reg-password').val() || '');
      const confirm = String($('#reg-confirm').val() || '');

      if (!email || !password) return App.flash('warning', 'Email and password are required.');
      if (password.length < 6) return App.flash('warning', 'Password must be at least 6 characters.');
      if (password !== confirm) return App.flash('warning', 'Passwords do not match.');

      const $btn = $('#btn-register');
      try {
        setBusy($btn, true);
        await App.register(email, password);
        App.updateNav();
        App.flash('success', 'Registration successful.');
        window.location.hash = '#dashboard';
      } catch (err) {
        App.flash('danger', err.message || 'Registration failed.');
      } finally {
        setBusy($btn, false);
      }
    });
  };

  $(window).on('hashchange', function () {
    const h = (window.location.hash || '').replace('#', '');
    if (h === 'login') App.mountLogin();
  });

  $(function () {
    const h = (window.location.hash || '#home').replace('#', '');
    if (h === 'login') App.mountLogin();
  });
})(window, window.jQuery);