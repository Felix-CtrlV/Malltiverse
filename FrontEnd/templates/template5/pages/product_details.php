<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<?php
// product_details.php - Logic Header
$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($product_id <= 0) {
    exit("<div class='container mt-5 text-center'><h4>Invalid Product ID.</h4><a href='index.php' class='btn btn-outline-dark'>Back to Shop</a></div>");
}

// Fetch Product and Category
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

// Fetch Variants and Sizes
$stmt2 = mysqli_prepare($conn, "SELECT variant_id, color, size FROM product_variant WHERE product_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $product_id);
mysqli_stmt_execute($stmt2);
$variants_result = mysqli_stmt_get_result($stmt2);

$variants_data = [];
$sizes = [];
while ($v = mysqli_fetch_assoc($variants_result)) {
    $variants_data[] = $v;
    if (!empty($v['size'])) $sizes[] = $v['size'];
}
$sizes = array_unique($sizes);
?>

<div class="container mt-5">
    <div class="row g-5">
        <div class="col-lg-7 mb-4">
            <div class="product-image-box shadow-sm">
                <img src="../uploads/products/<?= $product['product_id'] ?>_<?= htmlspecialchars($product['image']) ?>"
                     class="img-fluid w-100" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
        </div>

        <div class="col-lg-5">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-muted">Shop</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($product['category_name']) ?></li>
                </ol>
            </nav>

            <h1 class="h2 fw-bold mb-2"><?= htmlspecialchars($product['product_name']) ?></h1>
            <p class="price-tag mb-4">$<?= number_format($product['price'], 2) ?></p>
            
            <p class="text-muted small mb-4"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="fw-bold small text-uppercase">Size</label>
                    <select id="sizeSelect" class="form-select border-dark-subtle">
                        <option value="">Select Size</option>
                        <?php foreach ($sizes as $size): ?>
                            <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="fw-bold small text-uppercase">Qty</label>
                    <input type="number" id="qtyInput" class="form-control border-dark-subtle" value="1" min="1">
                </div>
            </div>

            <input type="hidden" id="supplier_id" value="<?= htmlspecialchars($product['supplier_id']) ?>">
            
            <button id="addToCartBtn" class="btn btn-dark btn-lg w-100 mb-5">
                <i class="fas fa-shopping-bag me-2"></i> ADD TO CART
            </button>
                            
          <div class="rolex-cart shadow-lg">
    
    <div class="cart-header d-flex justify-content-between align-items-center">
        <span class="header-title">My Selection</span>
        <i class="fas fa-shopping-bag" style="color: var(--gold-dark);"></i>
    </div>

    <div id="cartitem" class="custom-scrollbar">
        <p class="text-center text-muted py-5 small" style="font-family: 'Inter', sans-serif; letter-spacing: 1px;">
            <i class="fas fa-spinner fa-spin me-2"></i>Loading selection...
        </p>
    </div>

    <div class="cart-footer">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <span class="subtotal-label">Subtotal</span>
            <h4 class="subtotal-amount">$<span id="cart-subtotal">0.00</span></h4>
        </div>
        
        <button class="btn btn-luxury w-100" onclick="location.href='checkout.php'">
            PROCEED TO CHECKOUT
        </button>
    </div>

</div>

<script>
    const allVariants = <?= json_encode($variants_data) ?>;

    // 1. Add to Cart Logic
    document.getElementById('addToCartBtn').addEventListener('click', function () {
        
        const selectedSize = document.getElementById('sizeSelect').value;
        const qty = parseInt(document.getElementById('qtyInput').value) || 1;
        const supplierId = document.getElementById('supplier_id').value;

        const variant = allVariants.find(v => v.size === selectedSize);

        // --- Alert if Size not selected ---
        if (!selectedSize || !variant) {
            Swal.fire({
                icon: 'warning',
                title: 'Choose a Size',
                text: 'Please Choose a Size',
                confirmButtonColor: '#212529'
            });
            return;
        }

        const formData = new FormData();
        formData.append('variant_id', variant.variant_id);
        formData.append('supplier_id', supplierId);
        formData.append('quantity', qty);

        fetch('../utils/add_to_cart.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Items added to the cart',
                        showConfirmButton: false,
                        timer: 1500 
                    });
                    refreshBag();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

   function refreshBag() {
    const supplierId = document.getElementById('supplier_id').value;
    const cartContainer = document.getElementById('cartitem');

   
    cartContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
            <p class="mt-2 small text-muted">Checking your bag...</p>
        </div>`;

    fetch(`../utils/fetch_cart_drawer.php?supplier_id=${supplierId}&t=${new Date().getTime()}`)
        .then(res => res.json())
        .then(data => {
           
            
            if (data.drawer_html && data.drawer_html.trim() !== "") {
                cartContainer.innerHTML = data.drawer_html;
            } else {
            
                cartContainer.innerHTML = `
                    <div class="text-center py-5 animated fadeIn">
                        <i class="fas fa-shopping-bag mb-3" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mb-0">Your selection is empty</p>
                        <small class="text-muted">Start adding items to your bag.</small>
                    </div>`;
            }

            
            const totalElement = document.getElementById('cart-subtotal');
            if (totalElement) {
                const total = parseFloat(data.total) || 0;
                totalElement.textContent = total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        })
        .catch(err => {
            console.error("Error fetching cart:", err);
            cartContainer.innerHTML = `<p class="text-center text-danger py-3 small">Failed to load cart.</p>`;
        });
}
    window.onload = refreshBag;

    // 2. Remove from Cart Logic with SweetAlert2
    function handleRemove(cartId) {
        Swal.fire({
            title: 'Are You Sure?',
            text: "Are you Sure to Delete this item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#212529',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const supplierId = document.getElementById('supplier_id').value;
                const formData = new FormData();
                formData.append('cart_id', cartId);
                formData.append('supplier_id', supplierId);

                fetch('../utils/removeFromCart.php', { 
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Deleted Successfully!',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });
                        refreshBag(); 
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Path Error', err.message, 'error');
                });
            }
        });
    }
</script>
