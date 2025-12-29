<?php
include($_SERVER['DOCUMENT_ROOT'] . "/Malltiverse/BackEnd/config/dbconfig.php");

// Check both POST (AJAX) and GET (URL) for the search term
$search = $_POST['search'] ?? $_GET['search_product'] ?? '';
$supplier_id = $_POST['supplier_id'] ?? $_GET['supplier_id'] ?? null;

if (!$supplier_id) {
    echo '<div class="col-12 text-center text-danger">Error: Missing Supplier ID.</div>';
    exit;
}

// Search logic (using % for "contains" is usually better than starts with)
$searchTerm = "%" . $search . "%"; 

$sql = "SELECT * FROM products WHERE supplier_id = ? AND product_name LIKE ? ORDER BY product_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $supplier_id, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// ... rest of your display code
?>
// OPTION A: "Contains" search (RECOMMENDED for search bars)
// Finds the text anywhere in the product name
$searchTerm = "%" . $search . "%"; 

// OPTION B: Keep your original "Starts With" logic
// $searchTerm = $search . "%"; 

$sql = "SELECT * FROM products WHERE supplier_id = ? AND product_name LIKE ? ORDER BY product_name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $supplier_id, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) {
        // Construct image path
        $imgName = $product['product_id'] . "_" . $product['image'];
        $imgPath = "../uploads/products/" . $imgName;
        
        // Use a default image if the specific product image doesn't exist
        // (Optional check, useful if files get deleted)
        if(empty($product['image'])) {
             $displayImg = "../uploads/default_product.png";
        } else {
             $displayImg = $imgPath;
        }
        ?>
        
        <div class="col-md-3 col-sm-6 col-12 mb-4">
            <div class="card-product image h-100 shadow-sm">
                <img src="<?= htmlspecialchars($displayImg) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>"
                     style="height: 200px; object-fit: cover;">
                     
                <div class="card-body d-flex flex-column">
                    <h5 class="card_title text-truncate"><?= htmlspecialchars($product['product_name']) ?></h5>
                    <p class="card-text price fw-bold text-primary">$<?= number_format($product['price'], 2) ?></p>
                    
                    <a href="product_detail.php?id=<?= $product['product_id'] ?>" class="btn-black-rounded mt-auto">Shop Now</a>
                </div>
            </div>
        </div>
        
        <?php
    }
} else {
    // Message adjusted for "Contains" logic
    echo '<div class="col-12 text-center py-5">';
    echo '<p class="text-muted">No products found matching "' . htmlspecialchars($search) . '"</p>';
    echo '</div>';
}
?>