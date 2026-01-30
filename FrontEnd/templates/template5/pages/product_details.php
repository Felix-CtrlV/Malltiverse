<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../../BackEnd/config/dbconfig.php';


$is_logged_in = isset($_SESSION['customer_id']) ? 'true' : 'false';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($product_id <= 0) {
    exit("<div class='container mt-5 text-center'><h4>Invalid Product ID.</h4><a href='index.php' class='btn btn-outline-dark'>Back to Shop</a></div>");
}


$stmt = mysqli_prepare($conn, "
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN category c ON p.category_id = c.category_id 
    WHERE p.product_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$product) {
    exit("<div class='container mt-5 text-center'><h4>Product not found.</h4><a href='index.php' class='btn btn-outline-dark'>Back to Shop</a></div>");
}


$stmt2 = mysqli_prepare($conn, "SELECT variant_id, color, size, quantity FROM product_variant WHERE product_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $product_id);
mysqli_stmt_execute($stmt2);
$variants_result = mysqli_stmt_get_result($stmt2);

$variants_data = [];
$colors = []; 

while ($v = mysqli_fetch_assoc($variants_result)) {
    $variants_data[] = $v;
    if (!empty($v['color'])) $colors[] = trim($v['color']); 
}
$colors = array_unique($colors); 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div class="container mt-5">
    <div class="row g-5">
        <div class="col-lg-7 mb-4">
            <div class="product-image-box shadow-sm">
                <img src="../uploads/products/<?= $product['product_id'] ?>_<?= htmlspecialchars($product['image']) ?>"
                     class="img-fluid w-100" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
        </div>

        <div class="col-lg-5 ps-lg-5">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-secondary text-uppercase small fw-semibold">Shop</a></li>
                    <li class="breadcrumb-item active text-uppercase small" aria-current="page">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </li>
                </ol>
            </nav>

            <h1 class="display-6 fw-bold mb-2 text-dark"><?= htmlspecialchars($product['product_name']) ?></h1>
            <h3 class="price-tag mb-4 text-primary">$<?= number_format($product['price'], 2) ?></h3>
            
            <div class="mb-4">
                <label class="fw-bold small text-uppercase text-muted mb-2">Description</label>
                <p class="text-muted lh-base" style="font-size: 0.95rem;">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
                </p>
            </div>

          <div class="mb-4">
    <label class="fw-bold small text-uppercase text-muted mb-2">Select Color</label>
    <div class="d-flex align-items-center">
        <?php foreach ($colors as $color): ?>
            <?php 
                $cleanColor = trim($color); 
                $uniqueId = 'color_' . preg_replace('/[^a-zA-Z0-9]/', '', $cleanColor); 
            ?>
            <input type="radio" name="color_option" id="<?= $uniqueId ?>" value="<?= htmlspecialchars($cleanColor) ?>" class="color-radio">
            <label for="<?= $uniqueId ?>" class="color-label" style="background-color: <?= htmlspecialchars($cleanColor) ?>;" title="<?= htmlspecialchars($cleanColor) ?>"></label>
        <?php endforeach; ?>
    </div>
</div>

            <div class="row g-3 mb-4">
                <div class="col-7">
                    <label class="fw-bold small text-uppercase text-muted mb-2">Select Size</label>
                    <select id="sizeSelect" class="form-select shadow-sm" onchange="displayStock()">
                       <option value="">Select Color First</option>
                    </select>
                    <div id="stockDisplay" class="mt-1 small fw-bold text-secondary"></div>
                </div>

                <div class="col-5">
                    <label class="fw-bold small text-uppercase text-muted mb-2">Quantity</label>
                    <div class="qty-container">
                        <button type="button" class="btn-qty" onclick="changeQty(-1)">-</button>
                        <input type="number" id="qtyInput" value="1" min="1" readonly style="width: 40px; text-align: center; border: none;">
                        <button type="button" class="btn-qty" onclick="changeQty(1)">+</button>
                    </div>
                </div>
            </div>

       <div id="qtyErrorMessage" class="custom-alert-danger d-flex align-items-center mt-2" style="display: none !important;">
    <div class="alert-icon-circle">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="alert-message-content">
        <strong class="d-block">Stock Limit Exceeded</strong>
        <span>You cannot add more than the available stock.</span>
    </div>
</div>

            <input type="hidden" id="supplier_id" value="<?= htmlspecialchars($product['supplier_id']) ?>">
            <button id="addToCartBtn" class="btn btn-dark w-100 py-3">ADD TO CART</button>
        </div>
    </div>
</div>

<script>
    const isLoggedIn = <?= $is_logged_in ?>;
    const allVariants = <?= json_encode($variants_data) ?>;
    const sizeSelect = document.getElementById('sizeSelect');
    const colorRadios = document.querySelectorAll('input[name="color_option"]');
    let currentVariant = null;

    window.addEventListener('DOMContentLoaded', () => {
        sizeSelect.disabled = true;
        refreshBag();
    });

    
    colorRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedColor = this.value;
            sizeSelect.disabled = false;
            sizeSelect.innerHTML = '<option value="" selected disabled>Choose your size</option>';

            const availableVariants = allVariants.filter(v => 
                String(v.color).trim() === String(selectedColor).trim()
            );

            availableVariants.forEach(v => {
                const option = document.createElement('option');
                option.value = v.size;
                option.textContent = v.size;
                sizeSelect.appendChild(option);
            });

            document.getElementById('stockDisplay').innerText = "";
            document.getElementById('qtyInput').value = 1;
            currentVariant = null;
        });
    });

  


function displayStock() {
    const sizeSelect = document.getElementById('sizeSelect');
    const selectedSize = sizeSelect.value;
    const colorInput = document.querySelector('input[name="color_option"]:checked');
    const stockDisplay = document.getElementById('stockDisplay');
    const addToCartBtn = document.getElementById('addToCartBtn');

    if (colorInput && selectedSize) {
        const selectedColor = colorInput.value.trim();
        const formattedSize = selectedSize.trim();

       
        currentVariant = allVariants.find(v => 
            String(v.size).trim() === formattedSize && 
            String(v.color).trim() === selectedColor
        );

        
        if (currentVariant) {
            fetch(`../utils/get_cart_data.php?variant_id=${currentVariant.variant_id}`)
            .then(res => res.json())
            .then(data => {
                let realStock = 0;
                
               
                if (data.items && data.items.length > 0) {
                    const matchedItem = data.items.find(item => item.variant_id == currentVariant.variant_id);
                    realStock = matchedItem ? parseInt(matchedItem.availableStock) : 0;
                } else if (data.availableStock !== undefined) {
                    realStock = parseInt(data.availableStock);
                }

                
                currentVariant.quantity = realStock; 

                if (realStock <= 0) {
                    stockDisplay.className = "mt-1 small fw-bold text-danger";
                    stockDisplay.innerHTML = `<i class="fas fa-times-circle me-1"></i> Out of Stock`;
                    addToCartBtn.disabled = true;
                } else {
                    stockDisplay.className = "mt-1 small fw-bold text-success";
                    stockDisplay.innerHTML = `<i class="fas fa-check-circle me-1"></i> Stock available: ${realStock}`;
                    addToCartBtn.disabled = false;
                }
                validateQty(); 
            })
            .catch(err => {
                console.error("Fetch error:", err);
                stockDisplay.innerText = "Error loading stock info";
            });
        } else {
            
            stockDisplay.innerText = "Variant not found";
            addToCartBtn.disabled = true;
        }
    }
}

    function changeQty(amount) {
        const qtyInput = document.getElementById('qtyInput');
        let newVal = (parseInt(qtyInput.value) || 1) + amount;
        if (newVal >= 1) {
            qtyInput.value = newVal;
            validateQty();
        }
    }

 function validateQty() {
    const qtyInput = document.getElementById('qtyInput');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const errorMsg = document.getElementById('qtyErrorMessage');
    
    const selectedQty = parseInt(qtyInput.value);
    
    const availableStock = currentVariant ? parseInt(currentVariant.quantity) : 0;

    if (selectedQty > availableStock) {
    
        errorMsg.style.setProperty('display', 'flex', 'important');
        
       
        addToCartBtn.disabled = true;
        addToCartBtn.style.opacity = '0.5'; 
        addToCartBtn.style.cursor = 'not-allowed'; 
    } else {
        
        errorMsg.style.setProperty('display', 'none', 'important');
        
        
        addToCartBtn.disabled = false;
        addToCartBtn.style.opacity = '1';
        addToCartBtn.style.cursor = 'pointer';
    }
}

    // Add to Cart Logic
    document.getElementById('addToCartBtn').addEventListener('click', function () {
        if (!isLoggedIn) {
            Swal.fire({
                icon: 'info',
                title: 'Login Required',
                text: 'Please login to add items to your cart.',
                showCancelButton: true,
                confirmButtonText: 'Login Now',
                confirmButtonColor: '#212529'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = '../customerLogin.php';
            });
            return;
        }

        if (!currentVariant) {
            Swal.fire({ icon: 'warning', title: 'Selection Missing', text: 'Please select color and size.' });
            return;
        }

        const qty = parseInt(document.getElementById('qtyInput').value);
        if (qty > currentVariant.quantity) {
            Swal.fire({ icon: 'error', title: 'Low Stock', text: 'Not enough items available.' });
            return;
        }

        const formData = new FormData();
        formData.append('variant_id', currentVariant.variant_id);
        formData.append('supplier_id', document.getElementById('supplier_id').value);
        formData.append('quantity', qty);

        fetch('../utils/add_to_cart.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
               Swal.fire({
            title: 'Added to Bag!',
            html: 'Your item is waiting for you.',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#f8f9fa', // Soft off-white
            iconColor: '#28a745',
            icon: 'success',
            customClass: {
            popup: 'my-rounded-popup',
            title: 'my-soft-title'
  }
});
                refreshBag();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        });
    });

    function refreshBag() {
        const supplierId = document.getElementById('supplier_id').value;
        fetch(`../utils/fetch_cart_drawer.php?supplier_id=${supplierId}&t=${new Date().getTime()}`)
        .then(res => res.json())
        .then(data => {
            const cartBadge = document.getElementById('cart-badge-count');
            if (cartBadge) {
                cartBadge.innerText = data.total_count || 0;
                cartBadge.style.display = data.total_count > 0 ? 'inline-block' : 'none';
            }
        });
    }
</script>

<style>
.custom-alert-danger {
    background-color: #FFF5F5; 
    border: 1px solid #FED7D7;
    border-left: 5px solid #E53E3E; 
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}


.alert-icon-circle {
    background-color: #FEB2B2;
    color: #C53030;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}


.alert-message-content strong {
    color: #9B2C2C;
    font-size: 0.9rem;
}

.alert-message-content span {
    color: #C53030;
    font-size: 0.8rem;
}
.my-rounded-popup {
    border-radius: 25px !important;
    padding: 2rem !important;
    border: 1px solid #e0e0e0;
}
.my-soft-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    color: #333;
}</style>