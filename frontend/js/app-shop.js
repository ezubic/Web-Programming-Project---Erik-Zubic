(function (window, $) {
  const App = (window.App = window.App || {});

  function getCart() {
    try {
      return JSON.parse(localStorage.getItem('cart') || '[]');
    } catch {
      return [];
    }
  }

  function setCart(items) {
    localStorage.setItem('cart', JSON.stringify(items || []));
  }

  function cartTotal(items) {
    return (items || []).reduce((sum, it) => sum + (it.qty * it.unit_price), 0);
  }

  App.cart = {
    get: getCart,
    set: setCart,
    clear: function () { setCart([]); },
    add: function (product, qty) {
      const items = getCart();
      const q = Math.max(1, parseInt(qty || 1, 10));
      const idx = items.findIndex(x => x.product_id === product.id);
      if (idx >= 0) items[idx].qty += q;
      else items.push({
        product_id: product.id,
        name: product.name,
        qty: q,
        unit_price: parseFloat(product.price || 0)
      });
      setCart(items);
      return items;
    },
    remove: function (productId) {
      const items = getCart().filter(x => x.product_id !== productId);
      setCart(items);
      return items;
    },
    total: function () { return cartTotal(getCart()); }
  };

  async function loadProducts() {
    // Backend requires auth for GET /products
    App.requireFrontendAuth();
    const list = await App.apiFetch('/products', { method: 'GET' });
    return Array.isArray(list) ? list : (list.items || []);
  }

  function renderProducts($container, products) {
    const isAdmin = App.isAdmin && App.isAdmin();

    const html = (products || []).map(p => {
      const price = (p.price ?? p.unit_price ?? 0);
      return `
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="single-product p-3" style="border:1px solid rgba(0,0,0,.08); border-radius:10px;">
            <div class="product-details">
              <h6 style="min-height:44px;">${escapeHtml(p.name || 'Product')}</h6>
              <div class="price">
                <h6>${escapeHtml(String(price))} KM</h6>
              </div>

              <div class="d-flex align-items-center" style="gap:10px;">
                ${isAdmin ? `
                  <button class="btn btn-sm btn-outline-primary js-edit-product" data-id="${p.id}">Edit</button>
                  <button class="btn btn-sm btn-outline-danger js-del-product" data-id="${p.id}">Delete</button>
                ` : `
                  <button class="btn btn-sm btn-primary js-add-cart" data-id="${p.id}">Add to cart</button>
                `}
                <a class="btn btn-sm btn-outline-secondary" href="#single-product" data-product-id="${p.id}">Details</a>
              </div>
            </div>
          </div>
        </div>`;
    }).join('');

    $container.html(`<div class="row">${html || '<div class="col-12"><p>No products found.</p></div>'}</div>`);
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  async function deleteProduct(id) {
    App.requireFrontendAuth();
    await App.apiFetch('/products/' + encodeURIComponent(id), { method: 'DELETE' });
  }

  async function updateProduct(id, body) {
    App.requireFrontendAuth();
    await App.apiFetch('/products/' + encodeURIComponent(id), { method: 'PUT', body });
  }

  async function createProduct(body) {
    App.requireFrontendAuth();
    await App.apiFetch('/products', { method: 'POST', body });
  }

  function bindCategoryHandlers(products) {
    const byId = new Map((products || []).map(p => [String(p.id), p]));

    $(document).off('click.jsAddCart').on('click.jsAddCart', '.js-add-cart', function () {
      const id = String($(this).data('id'));
      const p = byId.get(id);
      if (!p) return;
      App.cart.add(p, 1);
      App.flash('success', 'Added to cart.');
    });

    $(document).off('click.jsDelProduct').on('click.jsDelProduct', '.js-del-product', async function () {
      const id = String($(this).data('id'));
      if (!confirm('Delete this product?')) return;
      try {
        await deleteProduct(id);
        App.flash('success', 'Product deleted.');
        window.location.hash = '#category';
      } catch (e) {
        App.flash('danger', e.message || 'Delete failed.');
      }
    });

    $(document).off('click.jsEditProduct').on('click.jsEditProduct', '.js-edit-product', function () {
      const id = String($(this).data('id'));
      const p = byId.get(id);
      if (!p) return;

      // Simple prompt-based editor (acceptable for milestone; you can improve UI later)
      const name = prompt('Product name:', p.name || '');
      if (name === null) return;
      const price = prompt('Price:', String(p.price ?? 0));
      if (price === null) return;

      const payload = { name: name.trim(), price: parseFloat(price) };
      updateProduct(id, payload)
        .then(() => App.flash('success', 'Product updated.'))
        .catch(e => App.flash('danger', e.message || 'Update failed.'));
    });
  }

  App.mountCategory = async function () {
    // Called when category view is loaded
    if (!App.requireFrontendAuth()) return;

    const $container = $('#products-dynamic');
    if (!$container.length) return;

    try {
      const products = await loadProducts();

      renderProducts($container, products);
      bindCategoryHandlers(products);

      // Admin-only "Create product"
      if (App.isAdmin && App.isAdmin()) {
        $('#admin-create-product').show().off('click').on('click', function () {
          const name = prompt('New product name:');
          if (!name) return;
          const price = prompt('Price:');
          if (price === null) return;
          createProduct({ name: name.trim(), price: parseFloat(price) })
            .then(() => {
              App.flash('success', 'Product created.');
              window.location.hash = '#category';
            })
            .catch(e => App.flash('danger', e.message || 'Create failed.'));
        });
      } else {
        $('#admin-create-product').hide();
      }
    } catch (e) {
      App.flash('danger', e.message || 'Failed to load products.');
    }
  };

  App.mountCart = function () {
    if (!App.requireFrontendAuth()) return;

    const items = App.cart.get();
    const $box = $('#cart-dynamic');
    if (!$box.length) return;

    if (!items.length) {
      $box.html('<p>Your cart is empty.</p>');
      return;
    }

    const rows = items.map(it => `
      <tr>
        <td>${escapeHtml(it.name)}</td>
        <td>${it.qty}</td>
        <td>${it.unit_price} KM</td>
        <td><button class="btn btn-sm btn-outline-danger js-cart-remove" data-id="${it.product_id}">Remove</button></td>
      </tr>
    `).join('');

    $box.html(`
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <strong>Total: ${cartTotal(items).toFixed(2)} KM</strong>
        <a class="btn btn-primary" href="#checkout">Proceed to checkout</a>
      </div>
    `);

    $(document).off('click.jsCartRemove').on('click.jsCartRemove', '.js-cart-remove', function () {
      const id = parseInt($(this).data('id'), 10);
      App.cart.remove(id);
      App.mountCart();
    });
  };

  App.mountCheckout = function () {
    if (!App.requireFrontendAuth()) return;

    const items = App.cart.get();
    const $box = $('#checkout-dynamic');
    if (!$box.length) return;

    if (!items.length) {
      $box.html('<p>Your cart is empty. Go back to <a href="#category">Shop</a>.</p>');
      return;
    }

    const total = cartTotal(items);

    $box.html(`
      <div class="mb-3">
        <p><strong>Order total:</strong> ${total.toFixed(2)} KM</p>
      </div>
      <button id="btn-place-order" class="btn btn-primary">Place order</button>
    `);

    $('#btn-place-order').off('click').on('click', async function () {
      try {
        const user = App.getUser();
        if (!user || !user.id) throw new Error('User id missing in token. Re-login and try again.');

        // 1) Create order
        const orderRes = await App.apiFetch('/orders', {
          method: 'POST',
          body: { user_id: user.id, total: total, status: 'pending' }
        });

        const orderId = orderRes.id;
        if (!orderId) throw new Error('Order creation failed.');

        // 2) Create order items
        for (const it of items) {
          await App.apiFetch('/order_items', {
            method: 'POST',
            body: {
              order_id: orderId,
              product_id: it.product_id,
              qty: it.qty,
              unit_price: it.unit_price
            }
          });
        }

        App.cart.clear();
        App.flash('success', 'Order placed successfully. Order ID: ' + orderId);
        window.location.hash = '#confirmation';
      } catch (e) {
        App.flash('danger', e.message || 'Checkout failed.');
      }
    });
  };

  App.mountDashboard = function () {
    if (!App.requireFrontendAuth()) return;

    const user = App.getUser();
    $('#dash-email').text(user?.email || '');
    $('#dash-role').text(user?.role || 'user');

    // base URL editor for easy local setup
    $('#api-base-url').val(App.getApiBaseUrl());
    $('#btn-save-api').off('click').on('click', function () {
      const v = String($('#api-base-url').val() || '').trim();
      if (!v) return App.flash('warning', 'API base URL cannot be empty.');
      localStorage.setItem('apiBaseUrl', v);
      App.flash('success', 'API base URL saved.');
    });

    if (App.isAdmin && App.isAdmin()) {
      $('#dash-admin-hint').show();
    } else {
      $('#dash-admin-hint').hide();
    }
  };

  // Hook into hash changes to mount per view (works with SPApp)
  $(window).on('hashchange', function () {
    const h = (window.location.hash || '').replace('#', '');
    if (h === 'category') App.mountCategory();
    if (h === 'cart') App.mountCart();
    if (h === 'checkout') App.mountCheckout();
    if (h === 'dashboard') App.mountDashboard();
  });

  // Also mount after initial load
  $(function () {
    const h = (window.location.hash || '#home').replace('#', '');
    if (h === 'category') App.mountCategory();
    if (h === 'cart') App.mountCart();
    if (h === 'checkout') App.mountCheckout();
    if (h === 'dashboard') App.mountDashboard();
  });
})(window, window.jQuery);