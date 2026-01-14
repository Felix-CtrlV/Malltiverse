<?php
// Ensure correct path to the central DB config
include '../../../BackEnd/config/dbconfig.php';

// Helper function to map color names to hex codes
function getColorHex($colorName) {
    $c = strtolower(trim($colorName));
    $map = [
        'black' => '#212121', 'white' => '#f5f5f5', 'red' => '#D32F2F', 
        'blue' => '#1976D2', 'green' => '#388E3C', 'yellow' => '#FBC02D',
        'navy' => '#1A237E', 'grey' => '#9E9E9E', 'gray' => '#9E9E9E',
        'gold' => '#D4AF37', 'orange' => '#F57C00', 'purple' => '#7B1FA2',
        'brown' => '#5D4037', 'beige' => '#F5F5DC'
    ];
    return isset($map[$c]) ? $map[$c] : $colorName;
}

$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$category_filter = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$limit = 9;

$query = "SELECT p.*, c.category_name FROM products p 
          LEFT JOIN category c ON p.category_id = c.category_id 
          WHERE p.supplier_id = ?";
          
if ($category_filter) {
    $query .= " AND p.category_id = ?";
    $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiii", $supplier_id, $category_filter, $limit, $offset);
} else {
    $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $supplier_id, $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $imgUrl = "../uploads/products/" . $row['product_id'] . "_" . $row['image'];
        $price = number_format($row['price'], 2);
        $name = htmlspecialchars($row['product_name']);
        $catName = htmlspecialchars($row['category_name'] ?? 'Exclusive');
        $detailLink = "?supplier_id=$supplier_id&page=productdetail&product_id=" . $row['product_id'];
        
        // Fetch variants for color dots
        $pId = $row['product_id'];
        $vStmt = mysqli_prepare($conn, "SELECT DISTINCT color FROM product_variant WHERE product_id = ? AND quantity > 0");
        mysqli_stmt_bind_param($vStmt, "i", $pId);
        mysqli_stmt_execute($vStmt);
        $vResult = mysqli_stmt_get_result($vStmt);
        $availableColors = [];
        while($vRow = mysqli_fetch_assoc($vResult)) $availableColors[] = $vRow['color'];
?>
        <div class="product-card-wrapper">
            <div class="product-card tilt-element">
                <a href="<?= $detailLink ?>" class="card-link"></a>
                <div class="card-image-box">
                    <img src="<?= $imgUrl ?>" alt="<?= $name ?>" class="product-img">
                </div>
                <div class="card-info">
                    <div>
                        <div class="p-category"><?= $catName ?></div>
                        <h3 class="p-title"><?= $name ?></h3>
                    </div>
                    <div class="p-footer">
                        <span class="p-price">$<?= $price ?></span>
                        <div class="color-options" style="position: relative; z-index: 20;">
                            <?php if(empty($availableColors)): ?>
                                <span style="font-size:0.8rem; color:#555;">Sold Out</span>
                            <?php else: ?>
                                <?php foreach($availableColors as $col): 
                                    $hex = getColorHex($col); ?>
                                    <div class="color-dot-radio" 
                                         title="<?= htmlspecialchars($col) ?>"
                                         style="background-color: <?= $hex ?>;">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
} else {
    echo "NO_MORE";
}
?>