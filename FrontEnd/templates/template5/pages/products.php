<?php

$supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 10;


$query = "SELECT * FROM shop_assets WHERE supplier_id = $supplier_id";
$result = mysqli_query($conn, $query);
$shop_assets = mysqli_fetch_assoc($result);
?>

<section class="page-content products-page t5-products-section">
    <div class="container">
        <h2 class="text-center mb-5"><?= htmlspecialchars($shop_assets['about'] ?? 'Our Products') ?></h2>

        <div class="row g-4 products-container" id="productResults">
            <?php
            // Products ဆွဲထုတ်တဲ့ မူလ PHP Code များ...
            $products_stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE supplier_id = ? ORDER BY created_at DESC");
            if ($products_stmt) {
                mysqli_stmt_bind_param($products_stmt, "i", $supplier_id);
                mysqli_stmt_execute($products_stmt);
                $products_result = mysqli_stmt_get_result($products_stmt);

                if ($products_result && mysqli_num_rows($products_result) > 0) {
                    while ($product = mysqli_fetch_assoc($products_result)) {
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card t5-product-card h-100">
                                <div class="card product-card h-100">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../uploads/products/<?= $product['product_id'] ?>_<?= htmlspecialchars($product['image']) ?>"
                                             class="card-img-top" alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                                        <p class="card-text price">$<?= number_format($product['price'], 2) ?></p>
                                        <button class="btn btn-primary btn-add-cart" data-product-id="<?= $product['product_id'] ?>">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center">No products available.</p></div>';
                }
                mysqli_stmt_close($products_stmt);
            }
            ?>
        </div>
    </div>
</section>

