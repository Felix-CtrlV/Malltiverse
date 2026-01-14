document.addEventListener("DOMContentLoaded", function () {
  const getButtons = () => document.querySelectorAll(".btn-category");
  const itemsContainer = document.querySelector(".product-grid");
  const itemsSelector = ".product-item";
  const loadBtn = document.getElementById("load-more-btn");
  let activeFilter = "all";
  let noResultsEl = null; // Store the message element

  // 1. Filter Logic
  function applyFilter(filter) {
    activeFilter = filter || "all";
    const normFilter = (activeFilter || "").toString().toLowerCase().trim();

    // Toggle active class on buttons
    getButtons().forEach((b) => {
      const btnFilter = (b.dataset.filter || "")
        .toString()
        .toLowerCase()
        .trim();
      b.classList.toggle("active", btnFilter === normFilter);
    });

    let visibleCount = 0;

    // Loop through products and show/hide based on category
    document.querySelectorAll(itemsSelector).forEach((it) => {
      const cat = (it.dataset.category || "").toString().toLowerCase().trim();

      // If filter is 'all', show everything. Otherwise, match exact category name.
      const show = normFilter === "all" || cat === normFilter;

      it.style.display = show ? "" : "none";
      if (show) visibleCount++;
    });

    // 2. Handle "No Results" Message - Use custom alert box
    // Remove any existing no-results element
    const existingNoResults = document.querySelector('.no-results');
    if (existingNoResults) {
      existingNoResults.remove();
    }
    
    if (visibleCount === 0 && activeFilter !== "all") {
      // Use custom alert box instead of inline element
      if (typeof window.showMinimalAlert === 'function') {
        window.showMinimalAlert('No products found in this category', 'info');
      }
    }
  }

  // 3. Click Event for Categories
  const catFilterContainer = document.querySelector(".category-filter");
  if (catFilterContainer) {
    catFilterContainer.addEventListener("click", function (e) {
      const btn = e.target.closest(".btn-category");
      if (!btn) return;

      const f = btn.dataset.filter || "all";
      applyFilter(f);
    });
  }

  // 4. Load More Logic (Preserved from your code)
  if (loadBtn) {
    loadBtn.addEventListener("click", async function () {
      let offset = parseInt(loadBtn.dataset.offset || "0", 10);
      const supplier = parseInt(loadBtn.dataset.supplier || "0", 10);

      loadBtn.disabled = true;
      const prevText = loadBtn.textContent;
      loadBtn.textContent = "LOADING...";

      try {
        const form = new FormData();
        form.append("offset", offset);
        form.append("supplier_id", supplier);

        const res = await fetch("../fetch_products.php", {
          method: "POST",
          body: form,
        });

        const text = await res.text();

        if (text.trim() === "NO_MORE") {
          loadBtn.textContent = "NO MORE PRODUCTS";
          loadBtn.disabled = true;
        } else {
          // Append new items
          const temp = document.createElement("div");
          temp.innerHTML = text;
          while (temp.firstChild) itemsContainer.appendChild(temp.firstChild);

          offset += 6;
          loadBtn.dataset.offset = offset;
          loadBtn.disabled = false;
          loadBtn.textContent = prevText;

          // IMPORTANT: Re-apply the current filter to the newly loaded items
          applyFilter(activeFilter);
        }
      } catch (err) {
        console.error(err);
        loadBtn.textContent = prevText;
        loadBtn.disabled = false;
      }
    });
  }

  // Initialize with All
  applyFilter("all");
});

// ==========================
// CART FUNCTIONALITY
// ==========================

// Make functions globally accessible
window.getSupplierId = function() {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get('supplier_id');
};

// Toggle cart popup
window.toggleCartPopup = function() {
  const popup = document.getElementById('cart-popup');
  if (popup) {
    if (popup.classList.contains('active')) {
      // Closing - add closing class and remove active after animation
      popup.classList.add('closing');
      setTimeout(() => {
        popup.classList.remove('active', 'closing');
      }, 300);
    } else {
      // Opening - add active class
      popup.classList.add('active');
      const supplierId = window.getSupplierId();
      if (supplierId) {
        window.refreshCart(supplierId);
      }
    }
  }
};

// Attach event listeners for cart button
document.addEventListener('DOMContentLoaded', function() {
  const cartBtn = document.getElementById('cart-icon-btn');
  const closeBtn = document.getElementById('cart-close-btn');
  
  if (cartBtn) {
    cartBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      window.toggleCartPopup();
    });
  }
  
  if (closeBtn) {
    closeBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      window.toggleCartPopup();
    });
  }
});

// Close cart popup when clicking outside
document.addEventListener('click', function(event) {
  const popup = document.getElementById('cart-popup');
  const popupContent = popup ? popup.querySelector('.cart-popup-content') : null;
  const cartBtn = document.getElementById('cart-icon-btn');
  const closeBtn = document.getElementById('cart-close-btn');
  
  if (popup && popup.classList.contains('active') && !popup.classList.contains('closing')) {
    if (popupContent && !popupContent.contains(event.target) && 
        cartBtn && !cartBtn.contains(event.target) &&
        closeBtn && !closeBtn.contains(event.target)) {
      // Close with slide-out animation
      popup.classList.add('closing');
      setTimeout(() => {
        popup.classList.remove('active', 'closing');
      }, 300);
    }
  }
});

// Refresh cart data
window.refreshCart = function(supplierId) {
  if (!supplierId) return;

  fetch(`../utils/get_cart_data.php?supplier_id=${supplierId}`)
    .then(res => res.json())
    .then(data => {
      window.updateCartUI(data);
    })
    .catch(err => {
      console.error('Error fetching cart:', err);
    });
};

// Update cart UI
window.updateCartUI = function(data) {
  const container = document.getElementById('cart-items-container');
  const footer = document.getElementById('cart-footer');
  const badge = document.getElementById('cart-badge');
  const supplierId = window.getSupplierId();

  if (!container) return;

  // Update badge
  if (badge) {
    const count = data.itemCount || 0;
    badge.textContent = count;
    badge.style.display = (count > 0) ? 'flex' : 'none';
  }

  // Update cart items
  if (data.items && data.items.length > 0) {
    let html = '';
    data.items.forEach(item => {
      const sizeText = item.size ? `Size: ${window.escapeHtml(item.size)}` : '';
      const colorText = item.color_code ? `<span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: ${item.color_code}; vertical-align: middle; margin-left: 4px;" title="${window.escapeHtml(item.color_code)}"></span>` : '';
      const detailsSeparator = (item.size && item.color_code) ? ' | ' : '';
      
      html += `
        <div class="cart-item">
          <img src="${item.image}" alt="${window.escapeHtml(item.name)}" class="cart-item-image" 
               onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23ddd%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E'">
          <div class="cart-item-info">
            <h4 class="cart-item-name">${window.escapeHtml(item.name)}</h4>
            <div class="cart-item-details">
              ${sizeText}${detailsSeparator}${colorText ? 'Color:' : ''} ${colorText}
              <br><small>Qty: ${item.qty}</small>
            </div>
            <div class="cart-item-footer">
              <span class="cart-item-price">$${parseFloat(item.price * item.qty).toFixed(2)}</span>
              <button class="cart-item-remove" onclick="window.removeFromCart(${item.cart_id}, ${supplierId})">Remove</button>
            </div>
          </div>
        </div>
      `;
    });
    container.innerHTML = html;
    
    // Show footer
    if (footer) {
      footer.style.display = 'block';
      const totalEl = document.getElementById('cart-total-amount');
      if (totalEl) {
        totalEl.textContent = `$${data.total || '0.00'}`;
      }
    }
  } else {
    container.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
    if (footer) footer.style.display = 'none';
  }
};

// Custom confirmation dialog
window.showConfirmDialog = function(message, onConfirm, onCancel) {
  let dialogEl = document.getElementById('custom-confirm-dialog');
  
  if (!dialogEl) {
    // Create dialog if it doesn't exist
    const dialogHTML = `
      <div class="custom-confirm-overlay" id="custom-confirm-dialog">
        <div class="custom-confirm-box">
          <h3>Confirm</h3>
          <p id="confirm-message"></p>
          <div class="confirm-buttons">
            <button class="confirm-btn-cancel" id="confirm-cancel">Cancel</button>
            <button class="confirm-btn-ok" id="confirm-ok">OK</button>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', dialogHTML);
    dialogEl = document.getElementById('custom-confirm-dialog');
  }
  
  const messageEl = document.getElementById('confirm-message');
  const okBtn = document.getElementById('confirm-ok');
  const cancelBtn = document.getElementById('confirm-cancel');
  
  messageEl.textContent = message;
  dialogEl.style.display = 'flex';
  
  // Remove existing event listeners by cloning and replacing
  const newOkBtn = okBtn.cloneNode(true);
  const newCancelBtn = cancelBtn.cloneNode(true);
  okBtn.parentNode.replaceChild(newOkBtn, okBtn);
  cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
  
  // Add new event listeners
  newOkBtn.addEventListener('click', function handleOk() {
    dialogEl.style.display = 'none';
    if (onConfirm) onConfirm();
  });
  
  newCancelBtn.addEventListener('click', function handleCancel() {
    dialogEl.style.display = 'none';
    if (onCancel) onCancel();
  });
  
  // Close on overlay click (remove old listener first)
  const overlayHandler = function(e) {
    if (e.target === dialogEl) {
      dialogEl.style.display = 'none';
      if (onCancel) onCancel();
      dialogEl.removeEventListener('click', overlayHandler);
    }
  };
  dialogEl.addEventListener('click', overlayHandler);
};

// Remove item from cart
window.removeFromCart = function(cartId, supplierId) {
  window.showConfirmDialog(
    'Remove this item from cart?',
    function() {
      // User confirmed
      const formData = new FormData();
      formData.append('cart_id', cartId);

      fetch('../utils/removeFromCart.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            window.showMinimalAlert('Item removed from cart', 'success');
            window.refreshCart(supplierId);
          } else {
            window.showMinimalAlert(data.message || 'Error removing item', 'error');
          }
        })
        .catch(err => {
          console.error('Error:', err);
          window.showMinimalAlert('Network error. Please try again.', 'error');
        });
    },
    function() {
      // User cancelled - do nothing
    }
  );
};

// Minimalist Alert Box
window.showMinimalAlert = function(message, type = 'info') {
  const alertEl = document.getElementById('minimal-alert');
  if (!alertEl) {
    console.error('Alert element not found');
    return;
  }

  alertEl.textContent = message;
  alertEl.className = `minimal-alert ${type} show`;

  setTimeout(() => {
    alertEl.classList.remove('show');
  }, 3000);
};

// Escape HTML to prevent XSS
window.escapeHtml = function(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
};

// Initialize cart on page load (separate DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function() {
  const supplierId = window.getSupplierId();
  if (supplierId) {
    window.refreshCart(supplierId);
  }
});