<?php
require_once __DIR__ . '/../../../utils/Ordered.php';
include '../../BackEnd/config/dbconfig.php';

$customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
$company_id = isset($supplier['company_id']) ? (int)$supplier['company_id'] : 0;
if ($company_id <= 0 && $supplier_id > 0) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT company_id FROM companies WHERE supplier_id = $supplier_id LIMIT 1"));
    $company_id = $r ? (int)$r['company_id'] : 0;
}

if ($customer_id > 0 && isset($_GET['payment_status']) && $_GET['payment_status'] === 'success') {
    $is_ordered = placeOrder($conn, $customer_id, $company_id);

    if ($is_ordered) {
        echo "<script>alert('Order Placed Successfully!'); window.location.href='?supplier_id=$supplier_id&page=cart';</script>";
        exit();
    }
}

$cart_query = "SELECT c.cart_id, c.quantity, p.product_name, p.price, p.image, p.product_id, v.color, v.size 
               FROM cart c 
               JOIN product_variant v ON c.variant_id = v.variant_id 
               JOIN products p ON v.product_id = p.product_id 
               WHERE c.customer_id = ? AND c.company_id = ?";

$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, "ii", $customer_id, $company_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cart_count = mysqli_num_rows($result);
$total_price = 0;
?>

<style>
    /* --- SHARED THEME VARIABLES (Matched to Home) --- */
    :root {
        --bg-color: #0a0a0a;
        --card-bg: #1a1a1a;
        --text-main: #ffffff;
        --text-muted: #888888;
        --accent: #D4AF37;
        --font-display: 'Helvetica Neue', 'Arial Black', sans-serif;
        --font-body: 'Helvetica', sans-serif;
        --transition-smooth: cubic-bezier(0.16, 1, 0.3, 1);
        --danger-color: #ff3b3b;
    }

    .cart-wrapper {
        background-color: var(--bg-color);
        color: var(--text-main);
        font-family: var(--font-body);
        min-height: 100vh;
        padding-top: 60px;
    }

    /* Cinematic Headings */
    h2.cart-title {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: -0.04em;
        margin-bottom: 50px;
        color: #fff;
    }

    /* Bento Card Style */
    .dark-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 30px;
        border: 1px solid #333;
        transition: transform 0.3s var(--transition-smooth), border-color 0.3s;
    }

    .dark-card:hover {
        border-color: #555;
    }

    /* Table Styles */
    .table-dark-custom {
        background: transparent;
        color: var(--text-main);
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
    }

    .table-dark-custom th {
        background-color: transparent;
        border-bottom: 1px solid #333;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 2px;
        font-weight: 600;
        padding-bottom: 20px;
    }

    .table-dark-custom td {
        background-color: transparent;
        border-bottom: 1px solid #222;
        vertical-align: middle;
        padding: 25px 0;
        color: var(--text-main);
    }

    .product-img {
        border-radius: 12px;
        border: 1px solid #333;

    }

    .product-name {
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 1.2rem;
        color: #fff;
        text-decoration: none;
        letter-spacing: -0.02em;
    }

    .variant-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        margin-top: 5px;
    }

    /* --- REDESIGNED REMOVE BUTTON --- */
    .btn-remove-custom {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: #2a2a2a;
        border: 1px solid #444;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.4s var(--transition-smooth);
        position: relative;
        overflow: hidden;
        margin-left: 10px;
    }

    .btn-remove-custom i {
        font-size: 1rem;
        transition: transform 0.3s ease;
    }

    .btn-remove-custom:hover {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        box-shadow: 0 0 15px rgba(255, 59, 59, 0.4);
        transform: translateY(-2px);
    }

    .btn-remove-custom:hover i {
        transform: scale(1.1);
    }

    .btn-remove-custom:active {
        transform: scale(0.95);
        box-shadow: 0 0 5px rgba(255, 59, 59, 0.6);
    }

    /* --- NEW QUANTITY CONTROL BUTTONS --- */
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .qty-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #2a2a2a;
        border: 1px solid #444;
        color: #fff;
        font-size: 1.2rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s var(--transition-smooth);
        user-select: none;
    }

    .qty-btn:hover {
        background-color: var(--accent);
        border-color: var(--accent);
        color: #000;
        box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
        transform: translateY(-2px);
    }

    .qty-btn:active {
        transform: scale(0.95);
    }

    .qty-btn:disabled {
        opacity: 0.5;
        pointer-events: none;
        filter: grayscale(80%);
    }

    .qty-value {
        min-width: 30px;
        text-align: center;
        font-weight: bold;
        color: #fff;
        font-size: 1.1rem;
    }

    /* Magnet Button Style (Matched to Home) */
    .btn-magnet {
        display: block;
        width: 100%;
        padding: 20px;
        background: #fff;
        color: #000;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        letter-spacing: 1px;
    }

    .btn-magnet:hover {
        background: #ccc;
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        color: #000;
    }

    .btn-link-custom {
        color: var(--text-muted);
        text-decoration: none;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 2px;
        font-weight: bold;
        transition: color 0.3s;
        opacity: 0.7;
    }

    .btn-link-custom:hover {
        color: #fff;
        opacity: 1;
    }

    /* Summary Section */
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #333;
        font-family: var(--font-display);
        font-size: 1.8rem;
        font-weight: 900;
        color: #fff;
        letter-spacing: -0.02em;
    }

    .quantity-badge {
        background: #222 !important;
        color: var(--text-main);
        border: 1px solid #333;
        padding: 8px 16px;
        font-family: var(--font-body);
        font-weight: bold;
    }
</style>

<div class="cart-wrapper">
    <div class="container pb-5">
        <h2 class="cart-title">Your Cart</h2>

        <?php if ($cart_count > 0): ?>
            <div class="row g-5">
                <div class="col-lg-8">
                    <div class="dark-card">
                        <div class="table-responsive">
                            <table class="table table-dark-custom align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50%">Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th class="text-end">Total</th>
                                        <th style="width: 50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = mysqli_fetch_assoc($result)):
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total_price += $subtotal;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../uploads/products/<?= $item['product_id'] ?>_<?= $item['image'] ?>"
                                                        alt="<?= $item['product_name'] ?>"
                                                        class="product-img"
                                                        style="width: 90px; height: 90px; object-fit: contain; margin-right: 25px;">
                                                    <div>
                                                        <div class="product-name mb-2"><?= htmlspecialchars($item['product_name']) ?></div>
                                                        <div class="variant-text text-uppercase">
                                                            Size: <span class="text-white"><?= htmlspecialchars($item['size']) ?></span>
                                                            <span class="mx-2" style="opacity: 0.3;">|</span>
                                                            <span style="display:inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: <?= $item['color'] ?>; border: 1px solid #555;"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="color: var(--text-muted);">$<?= number_format($item['price'], 2) ?></td>
                                            <td>
                                                <!-- Quantity Control -->
                                                <div class="quantity-control">
                                                    <button class="qty-btn qty-decrease" data-cart-id="<?= $item['cart_id'] ?>" <?= ($item['quantity'] <= 1) ? 'disabled' : '' ?>>−</button>
                                                    <span class="qty-value" id="qty-<?= $item['cart_id'] ?>"><?= $item['quantity'] ?></span>
                                                    <button class="qty-btn qty-increase" data-cart-id="<?= $item['cart_id'] ?>">+</button>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold text-white subtotal" id="subtotal-<?= $item['cart_id'] ?>" data-price="<?= $item['price'] ?>">
                                                $<?= number_format($subtotal, 2) ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn-remove-custom" onclick="removeFromCart(<?= $item['cart_id'] ?>)" title="Remove Item">
                                                    <lord-icon
                                                        src="https://cdn.lordicon.com/shlsuhqu.json"
                                                        trigger="hover"
                                                        stroke="bold"
                                                        state="hover-swirl"
                                                        colors="primary:#ffffff,secondary:#ffffff"
                                                        style="width:25px;height:25px">
                                                    </lord-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="dark-card sticky-top" style="top: 100px; z-index: 1;">
                        <h4 class="fw-bold text-uppercase mb-4" style="letter-spacing: 1px;">Summary</h4>

                        <div class="summary-row">
                            <span>Subtotal (<?= $cart_count ?> items)</span>
                            <span class="text-white" id="summary-subtotal">$<?= number_format($total_price, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span style="color: var(--text-muted); font-style: italic;">Calculated at checkout</span>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>
                            <span id="summary-total">$<?= number_format($total_price, 2) ?></span>
                        </div>

                        <div class="mt-5">
                            <a href="../utils/accessCheckout.php?supplier_id=<?= $supplier_id ?>"
                                class="btn-magnet mb-4">
                                Checkout
                            </a>

                            <div class="text-center">
                                <a href="?supplier_id=<?= $supplier_id ?>&page=collection" class="btn-link-custom">
                                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-4" style="color: #333;">
                    <i class="fas fa-shopping-bag" style="font-size: 5rem;"></i>
                </div>
                <h3 class="text-white text-uppercase fw-bold mb-3" style="font-family: var(--font-display);">Your cart is empty</h3>
                <p class="text-muted mb-5">Looks like you haven't made your choices yet.</p>
                <a href="?supplier_id=<?= $supplier_id ?>&page=collection" class="btn-magnet" style="display: inline-block; width: auto; padding: 15px 50px;">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Utility to update the total summary
    function updateTotal() {
        let subtotalElements = document.querySelectorAll('.subtotal');
        let total = 0;
        subtotalElements.forEach(el => {
            let value = parseFloat(el.textContent.replace('$', '').replace(',', ''));
            total += value;
        });
        document.getElementById('summary-subtotal').textContent = '$' + total.toFixed(2);
        document.getElementById('summary-total').textContent = '$' + total.toFixed(2);
    }

    // Quantity update with AJAX
    function updateQuantity(cartId, newQuantity, button) {
        // Disable buttons to prevent double submission
        let decreaseBtn = document.querySelector(`.qty-decrease[data-cart-id="${cartId}"]`);
        let increaseBtn = document.querySelector(`.qty-increase[data-cart-id="${cartId}"]`);
        if (decreaseBtn) decreaseBtn.disabled = true;
        if (increaseBtn) increaseBtn.disabled = true;

        const url = window.location.origin + '/malltiverse/frontend/utils/update_cart_qty.php';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ cart_id: cartId, quantity: newQuantity })
        })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // Update displayed quantity
                document.getElementById('qty-' + cartId).textContent = newQuantity;

                // Update subtotal for this row
                let price = parseFloat(document.getElementById('subtotal-' + cartId).dataset.price);
                let subtotalElem = document.getElementById('subtotal-' + cartId);
                subtotalElem.textContent = '$' + (price * newQuantity).toFixed(2);

                // Disable decrease button if quantity becomes 1
                if (newQuantity <= 1) {
                    if (decreaseBtn) decreaseBtn.disabled = true;
                } else {
                    if (decreaseBtn) decreaseBtn.disabled = false;
                }

                // Recalculate total summary
                updateTotal();
            } else {
                alert(data.message);
                // Revert quantity display to previous value (we don't store old, but we can fetch from DOM)
                // For simplicity, reload the page to ensure consistency
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Cannot connect to server. Please try again.');
        })
        .finally(() => {
            // Re-enable buttons (if not reloading)
            if (decreaseBtn) decreaseBtn.disabled = false;
            if (increaseBtn) increaseBtn.disabled = false;
        });
    }

    // Attach event listeners to quantity buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Increase buttons
        document.querySelectorAll('.qty-increase').forEach(btn => {
            btn.addEventListener('click', function(e) {
                let cartId = this.dataset.cartId;
                let qtySpan = document.getElementById('qty-' + cartId);
                let currentQty = parseInt(qtySpan.textContent);
                let newQty = currentQty + 1;
                updateQuantity(cartId, newQty, this);
            });
        });

        // Decrease buttons
        document.querySelectorAll('.qty-decrease').forEach(btn => {
            btn.addEventListener('click', function(e) {
                let cartId = this.dataset.cartId;
                let qtySpan = document.getElementById('qty-' + cartId);
                let currentQty = parseInt(qtySpan.textContent);
                if (currentQty > 1) {
                    let newQty = currentQty - 1;
                    updateQuantity(cartId, newQty, this);
                }
            });
        });
    });

    // Existing remove function (unchanged)
    function removeFromCart(cartId) {
        if (confirm('Remove item from cart?')) {
            const rootPath = window.location.origin + '/malltiverse/frontend/utils/removeFromCart.php';

            fetch(rootPath, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ 'cart_id': cartId })
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error Details:', error);
                    alert('Cannot connect to server.');
                });
        }
    }
</script>