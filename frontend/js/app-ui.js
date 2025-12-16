(function (window, $) {
  const App = (window.App = window.App || {});

  App.isAdmin = function () {
    const u = App.getUser ? App.getUser() : null;
    return u && u.role === 'admin';
  };

  App.isAuthed = function () {
    return !!(App.getToken && App.getToken());
  };

  App.updateNav = function () {
    const user = App.getUser ? App.getUser() : null;

    const $navLogin = $('#nav-login');
    const $navDashboard = $('#nav-dashboard');
    const $navAdmin = $('#nav-admin');
    const $navLogout = $('#nav-logout');
    const $navUserLabel = $('#nav-user-label');

    if (user) {
      $navLogin.hide();
      $navDashboard.show();
      $navLogout.show();
      $navUserLabel.text(user.email).show();

      if (user.role === 'admin') $navAdmin.show();
      else $navAdmin.hide();
    } else {
      $navLogin.show();
      $navDashboard.hide();
      $navAdmin.hide();
      $navLogout.hide();
      $navUserLabel.hide().text('');
    }
  };

  App.requireFrontendAuth = function () {
    if (!App.isAuthed()) {
      // Redirect to login view
      if (window.location.hash !== '#login') window.location.hash = '#login';
      return false;
    }
    return true;
  };

  App.flash = function (type, msg) {
    const $box = $('#app-flash');
    if (!$box.length) return alert(msg);
    $box.removeClass('alert-success alert-danger alert-info alert-warning').addClass('alert-' + type);
    $box.text(msg).show();
    setTimeout(() => $box.fadeOut(250), 3500);
  };

  // Boot
  $(function () {
    if (App.refreshSessionFromStorage) App.refreshSessionFromStorage();
    App.updateNav();

    // Logout click
    $('#nav-logout').on('click', function (e) {
      e.preventDefault();
      App.logout();
      App.updateNav();
      App.flash('info', 'You have been logged out.');
      window.location.hash = '#login';
    });
  });
})(window, window.jQuery);

// Frontend route guard for admin view
$(window).on('hashchange', function(){

    const h = (window.location.hash || '').replace('#','');
    if (h === 'admin') {
      if (!App.isAuthed()) { window.location.hash = '#login'; return; }
      if (!App.isAdmin()) { App.flash('danger','Admin access required.'); window.location.hash = '#dashboard'; return; }
    }
});
