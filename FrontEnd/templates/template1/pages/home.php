<?php
$supplier_id = (int) ($_GET['supplier_id'] ?? 1);

/* =============================
   1. FETCH HERO DATA (Updated to fetch company_id)
============================= */
$sql = "
    SELECT 
    c.company_id,  /* Added this line so we can use it later */
    c.company_name,
    sa.banner,
    sa.template_type,
    c.description
FROM suppliers s
JOIN companies c ON s.supplier_id = c.supplier_id
JOIN shop_assets sa ON sa.company_id = c.company_id
WHERE s.supplier_id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
$shop_assets = $result->fetch_assoc();
$stmt->close();

$company_id = $shop_assets['company_id'] ?? 0; /* Ensure this variable exists */
$company_name = $shop_assets['company_name'] ?? '';
$description = $shop_assets['description'] ?? '';

/* =============================
   SPLIT TITLE INTO 2 PARTS
============================= */
function splitHeroTitle(string $title): array
{
  $title = trim($title);
  $words = preg_split('/\s+/', $title);

  if (count($words) === 1) {
    $mid = ceil(strlen($words[0]) / 2);
    return [
      substr($words[0], 0, $mid),
      substr($words[0], $mid)
    ];
  }

  return [
    $words[0],
    implode(' ', array_slice($words, 1))
  ];
}

[$heroWord1, $heroWord2] = splitHeroTitle($company_name);
?>

<section class="hero-section">
  <?php if (($shop_assets['template_type'] ?? '') === 'video'): ?>
    <video class="hero-media" autoplay muted loop playsinline>
      <source src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['banner']) ?>" type="video/mp4">
    </video>
  <?php else: ?>
    <img class="hero-media" src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['banner']) ?>"
      alt="Hero Banner">
  <?php endif; ?>

  <div class="hero-overlay">
    <div class="hero-title">
      <span><?= htmlspecialchars($heroWord1) ?></span>
      <span><?= htmlspecialchars($heroWord2) ?></span>
    </div>

    <?php if (!empty($description)): ?>
      <p class="hero-tagline"><?= htmlspecialchars($description) ?></p>
    <?php endif; ?>
  </div>
</section>
<section class="featured-section">
  <div class="section-header">
    <h2 class="text-center mb-5 h1" style="color: var(--primary); margin-top: 30px;">Our Featured Products</h2>
    <span class="section-line"></span>
  </div>
  <div class="categories-grid">
    <?php
    $category_stmt = mysqli_prepare(
      $conn,
      "SELECT category_id, category_name 
     FROM category 
     WHERE company_id = ?
     LIMIT 4"
    );
    mysqli_stmt_bind_param($category_stmt, "i", $company_id);
    mysqli_stmt_execute($category_stmt);
    $category_result = mysqli_stmt_get_result($category_stmt);

    while ($row = mysqli_fetch_assoc($category_result)) {

      // 1. Default category image path
      $categoryImage = "../uploads/shops/{$supplier_id}/category_{$row['category_id']}.jpg";

      // 2. If category image NOT found → get first product image
      if (!file_exists($categoryImage)) {

        $product_stmt = mysqli_prepare(
          $conn,
          "SELECT product_id, image 
             FROM products 
             WHERE company_id = ? 
             AND category_id = ? 
             AND image IS NOT NULL 
             AND image != ''
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
          $product_stmt,
          "ii",
          $company_id,
          $row['category_id']
        );

        mysqli_stmt_execute($product_stmt);
        $product_result = mysqli_stmt_get_result($product_stmt);

        if ($product = mysqli_fetch_assoc($product_result)) {
          $categoryImage = "../uploads/products/{$product['product_id']}_{$product['image']}";
        } else {
          // 3. Final fallback image
          $categoryImage = "../assets/images/no-image.png";
        }

        mysqli_stmt_close($product_stmt);
      }
      ?>
      <div class="category-card">
        <img src="<?= htmlspecialchars($categoryImage) ?>" alt="<?= htmlspecialchars($row['category_name']) ?>">

Mei, [2/17/2026 10:35 PM]
<div class="category-overlay">
          <h3><?= htmlspecialchars($row['category_name']) ?></h3>
          <a href="?supplier_id=<?= $supplier_id ?>&category_id=<?= $row['category_id'] ?>&page=products"
            class="shop-btn">
            Shop Now
          </a>
        </div>
      </div>
      <?php
    }
    mysqli_stmt_close($category_stmt);
    ?>
  </div>

</section>