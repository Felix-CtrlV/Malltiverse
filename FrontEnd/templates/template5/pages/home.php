<?php
$current_page = 'home.php';

// ၁။ Theme Class သတ်မှတ်ချက် (Switch logic ကို အပေါ်မှာပဲ ထားပါ)
// သင့် Database အရ ID 4 က Nike, ID 10 က Rolex ဖြစ်ပါတယ်
switch ($supplier_id) {
    case 10: 
        $hero_class = "luxury-hero"; 
        break; 
    case 4:  
        $hero_class = "sport-hero";  // Nike အတွက်
        break; 
    case 5:  
        $hero_class = "casual-hero"; // Uniqlo အတွက်
        break; 
    default: 
        $hero_class = "default-hero";
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($supplier['company_name'] ?? 'Store') ?></title>
    <link rel="stylesheet" href="../templates/<?= basename(__DIR__) ?>/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<section class="hero-container <?= $hero_class ?>">
    <div class="hero-content">
        <span class="category-title">
            <?php if($supplier_id == 10): ?>
                <i class="fa-solid fa-clock"></i> Luxury Watch
            <?php elseif($supplier_id == 4): ?>
                <i class="fa-solid fa-bolt"></i> Sport Wear
            <?php else: ?>
                <i class="fa-solid fa-store"></i> Our Collection
            <?php endif; ?>
        </span>

        <h2 class="home" style="width:500px;">
            <?= htmlspecialchars($shop_assets['description'] ?? 'Welcome to our store') ?>
        </h2>
        
        <br>
        
        <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-shop-now">
          SHOP NOW
        </a>
    </div>

    <div class="hero-banner">
        <div class="banner-shape-wrapper">
            <img src="../uploads/shops/<?= $supplier_id ?>/<?= $banner1 ?>" class="fashion-banner" alt="Store Banner">
        </div>
    </div>
</section>