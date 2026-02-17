<?php

$db_tags = $supplier['tags'] ?? ''; 
$heroTitle = $supplier['description'] ?? 'Absolute Precision';



if (!empty($shop_assets['primary_color'])) {
    $primary_color = $shop_assets['primary_color'];
} else {
    
    $primary_color = ($supplier_id == '108') ? "#cd3e32" : "#006039"; 
}

// Features List Logic
if (!empty($db_tags)) {
    $tag_list = explode(',', str_replace('/', ',', $db_tags)); 
    $features = array_map('trim', $tag_list); 
    $display_features = array_slice($features, 0, 3);
    $marquee_content = strtoupper(implode(' — ', $features)) . ' — ';
} else {
    $display_features = ['Premium Quality', 'Exceptional Service', 'Trusted Store'];
    $marquee_content = "WELCOME TO OUR STORE — QUALITY GUARANTEED — SHOP NOW — ";
}
?>

<div class="swiss-wrapper">
    <nav class="top-nav fade-in">
        <span class="nav-brand" style="color: <?= $primary_color ?>;">
            <?= strtoupper(htmlspecialchars($db_tags)) ?>
        </span>
    </nav>

    <section class="hero-editorial">
        <div class="container-fluid p-0">
            <div class="row g-0 align-items-center">
                
                <div class="col-lg-5 p-5 position-relative z-2">
                    <div class="text-mask">
                        <span class="meta-tag reveal-text" style="color: <?= $primary_color ?> !important;">
                            <?= strtoupper(htmlspecialchars($db_tags)) ?>
                        </span>
                    </div>

                    <div class="title-wrapper">
                        <h1 class="editorial-title">
                            <?= htmlspecialchars($heroTitle) ?>
                        </h1>
                    </div>

                    <p class="editorial-desc reveal-text-delay">
                        <?= htmlspecialchars($shop_assets['description'] ?? '') ?>
                    </p>

                    <div class="btn-group-custom reveal-text-delay">
                        <a href="?supplier_id=<?= $supplier_id ?>&page=products" class="btn-magnetic">
                            <span class="btn-text">Discover Collection</span>
                            <span class="btn-circle" style="border-color: <?= $primary_color ?>; color: <?= $primary_color ?>;">
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                </div>

                <div class="col-lg-7 position-relative overflow-hidden hero-image-col">
                    <div class="image-reveal-curtain"></div>
                    <div class="hero-media-wrapper parallax-target">
                        <?php if (isset($shop_assets['template_type']) && $shop_assets['template_type'] == 'video'): ?>
                            <video class="hero-media" autoplay muted loop playsinline
                                src="../uploads/shops/<?= $supplier_id ?>/<?= $banner1 ?>"></video>
                        <?php else: ?>
                            <img src="../uploads/shops/<?= $supplier_id ?>/<?= $banner1 ?>" 
                                 alt="Banner" class="hero-media">
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="marquee-strip" style="background-color: <?= $primary_color ?> !important;">
        <div class="track">
            <div class="content">
                &nbsp;<?= $marquee_content ?> <?= $marquee_content ?>
            </div>
        </div>
    </div>

    <section class="features-minimal">
        <div class="container">
            <div class="row">
                <?php foreach ($display_features as $feature): ?>
                    <div class="col-md-4 feature-col">
                        <div class="minimal-feature">
                            <h4 class="f-title" style="color: <?= $primary_color ?> !important;">
                                <?= htmlspecialchars($feature) ?>
                            </h4>
                            <span class="f-line" style="background-color: <?= $primary_color ?> !important;"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<style>
/* --- Dynamic CSS Variables --- */
:root {
    --primary-main: <?= $primary_color ?>;
    --sw-dark: #111111;
    --sw-light: #f4f4f4;
    --font-head: 'Archivo', sans-serif;
}

body {
    background-color: var(--sw-light);
    color: var(--sw-dark);
    font-family: var(--font-head);
    overflow-x: hidden;
}

/* Nav & General */
.top-nav { display: flex; justify-content: space-between; padding: 20px 40px; border-bottom: 1px solid rgba(0,0,0,0.05); font-weight: 700; }
.hero-editorial { min-height: 85vh; display: flex; align-items: center; }
.editorial-title { font-size: 65px; font-weight: 900; line-height: 1.1; text-transform: uppercase; margin-bottom: 25px; }

/* Image Section */
.hero-image-col { height: 85vh; background: #eee; position: relative; }
.hero-media { width: 100%; height: 100%; object-fit: cover; animation: slowZoom 20s infinite alternate; }

/* Marquee */
.marquee-strip { color: white; padding: 20px 0; overflow: hidden; white-space: nowrap; }
.marquee-strip .track { display: inline-block; animation: marquee 25s linear infinite; }
.marquee-strip .content { font-weight: 900; font-size: 1.3rem; letter-spacing: 5px; }

/* Features Section (Desktop) */
.features-minimal { padding: 80px 0; background: white; }
.minimal-feature { display: flex; flex-direction: column; align-items: flex-start; }
.f-title { font-weight: 700; font-size: 1.25rem; margin-bottom: 15px; text-transform: uppercase; }
.f-line { display: block; width: 40px; height: 3px; transition: 0.4s width; }
.minimal-feature:hover .f-line { width: 100%; }

/* Buttons */
.btn-magnetic { display: inline-flex; align-items: center; text-decoration: none; color: var(--sw-dark); font-weight: 700; }
.btn-circle { width: 50px; height: 50px; border: 1px solid; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 15px; transition: 0.3s; }
.btn-magnetic:hover .btn-circle { background-color: var(--primary-main) !important; color: white !important; border-color: var(--primary-main) !important; }

/* Animations */
@keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
@keyframes slowZoom { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }
.image-reveal-curtain { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 10; animation: revealImage 1.5s cubic-bezier(0.77, 0, 0.175, 1) forwards; }
@keyframes revealImage { to { transform: translateY(-100%); } }

/* --- Mobile Responsiveness (အလယ်ရောက်အောင် သေချာပြင်ထားသည်) --- */
@media (max-width: 991px) {
    /* 1. Hero Content Area */
    .hero-editorial .p-5 {
        text-align: center !important;
        padding: 40px 20px !important;
        display: flex;
        flex-direction: column;
        align-items: center; /* Content အားလုံးကို အလယ်ပို့ရန် */
    }

    /* 2. Luxury, Watches, Precision Tags (Center) */
    .meta-tag {
        display: block;
        text-align: center;
        width: 100%;
        margin-bottom: 15px;
    }

    /* 3. Title & Description (Center) */
    .editorial-title { 
        font-size: 2.5rem; 
        text-align: center;
        width: 100%;
    }
    .editorial-desc {
        text-align: center;
        width: 100%;
        max-width: 100%;
    }

    /* 4. Discover Button (Center) */
    .btn-group-custom {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    /* 5. Features Section (Center) */
    .feature-col {
        margin-bottom: 30px;
    }
    .minimal-feature {
        align-items: center; /* Features တွေကို Mobile မှာ အလယ်ပို့ရန် */
        text-align: center;
    }
    .f-line {
        margin: 0 auto; /* Line အတိုလေးကို အလယ်ပို့ရန် */
    }

    /* 6. Image Column */
    .hero-image-col { 
        height: 40vh; 
        order: -1; 
    }
}
</style>