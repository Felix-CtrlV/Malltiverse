<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../../BackEnd/config/dbconfig.php"); 

$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 3;

// Color Fetching
$color_sql = "SELECT primary_color, secondary_color FROM shop_assets WHERE company_id = $company_id LIMIT 1";
$color_result = $conn->query($color_sql);
$primary_color = "#c5a059";   
$secondary_color = "#e0c08d"; 

if ($color_result && $color_result->num_rows > 0) {
    $color_row = $color_result->fetch_assoc();
    $primary_color = $color_row['primary_color'];
    $secondary_color = $color_row['secondary_color'];
}

// Logic for Stats Dashboard
$sql_stats = "SELECT rating FROM reviews WHERE company_id = $company_id";
$result_stats = $conn->query($sql_stats);
$total_reviews = 0;
$sum_ratings = 0;
$star_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if ($result_stats && $result_stats->num_rows > 0) {
    while ($row = $result_stats->fetch_assoc()) {
        $r = (int)$row['rating'];
        if (isset($star_counts[$r])) {
            $star_counts[$r]++;
            $sum_ratings += $r;
            $total_reviews++;
        }
    }
}
$avg_rating = $total_reviews > 0 ? number_format($sum_ratings / $total_reviews, 1) : "0.0";

// Fetch Reviews
$sql_reviews = "
    SELECT r.*, c.name, c.image 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.customer_id 
    WHERE r.company_id = $company_id 
    ORDER BY r.created_at DESC LIMIT 10";
$reviews_res = $conn->query($sql_reviews);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Section</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --gold: <?= $primary_color ?>; 
            --gold-leaf: <?= $secondary_color ?>;
        }

        body {
            background-color: black;
            color: white;
            margin: 0;
            font-family: 'Inter', sans-serif;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* --- Global Reveal Animation --- */
        .reveal-on-scroll { 
            opacity: 0; 
            transform: translateY(30px); 
            transition: 0.8s cubic-bezier(0.2, 0.8, 0.2, 1); 
        }
        .reveal-on-scroll.is-visible { 
            opacity: 1; 
            transform: translateY(0); 
        }

        /* --- Header Styling (PC) --- */
        .page-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .page-title {
            font-size: 50px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 9px;
            margin: 0;
            color: var(---primary);
        }
        .page-subtitle {
            color: var(--primary);
            letter-spacing: 4px;
            text-transform: uppercase;
            font-size: 14px;
            margin-top: 15px;
        }

        /* --- Dashboard Styling (PC) --- */
        .stats-dashboard {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            padding: 40px;
            margin-bottom: 50px;
            align-items: center;
        }
        .big-score { font-size: 80px; font-weight: 900; color: var(--primary); line-height: 1; }
        .total-count { color: #888; text-transform: uppercase; font-size: 12px; margin: 10px 0; }
        .stars-row { color: var(--gold); font-size: 20px; }

        .hud-bar-row { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; font-size: 12px; }
        .hud-label { color: #666; width: 60px; }
        .hud-track { flex-grow: 1; height: 2px; background: #222; position: relative; }
        .hud-fill { height: 100%; background: var(--primary); box-shadow: 0 0 10px var(--gold); }
        .hud-value { color: #888; width: 30px; text-align: right; }

        /* --- Reviews Feed (PC) --- */
        .reviews-feed {
            margin-bottom: 50px;
        }
        .section-heading {
            text-transform: uppercase;
            letter-spacing: 3px;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 18px;
        }
        .review-card {
            background: #050505;
            border: 1px solid #111;
            padding: 30px;
            margin-bottom: 20px;
        }
        .reviewer-header { display: flex; align-items: center; gap: 15px; }
        .reviewer-info h4 { margin: 0; font-size: 18px; color: white; }
        .reviewer-info span { font-size: 10px; color: #555; letter-spacing: 1px; }
        .review-stars { color: var(--gold); margin-left: auto; letter-spacing: 2px; }
        .review-body { color: #ccc; font-style: italic; line-height: 1.6; margin-top: 20px; font-size: 15px; }

        /* --- Form Styling (PC) --- */
        .form-sticky-panel {
            background: #0a0a0a;
            padding: 40px;
            border: 1px solid #1a1a1a;
        }
        .form-header {
            color: var(--gold);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
        }
        .luxury-input {
            width: 100%;
            background: black;
            border: 1px solid #222;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            outline: none;
            transition: 0.3s;
        }
        .luxury-input:focus { border-color: var(--gold); }
        .submit-btn {
            width: 100%;
            background: var(--gold);
            color: black;
            border: none;
            padding: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
        }
        .submit-btn:hover { background: white; }

        /* --- Mobile Responsive Fix --- */
        @media (max-width: 768px) {
            .page-title { 
                font-size: 26px !important; 
                letter-spacing: 4px !important; 
            }
            .stats-dashboard { 
                grid-template-columns: 1fr; 
                text-align: center; 
                padding: 30px 20px;
            }
            .reviewer-header { flex-direction: column; text-align: center; }
            .review-stars { margin-left: 0; margin-top: 10px; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <header class="page-header reveal-on-scroll">
        <h1 class="page-title"><?= htmlspecialchars($supplier['tags'] ?? '') ?></h1>
        <p class="page-subtitle"><?= htmlspecialchars($supplier['description'] ?? 'DISCOVER THE ARCHIVES OF EXCELLENCE') ?></p>
    </header>

    <div class="stats-dashboard reveal-on-scroll">
        <div class="big-score-block">
            <div class="big-score"><?= $avg_rating ?></div>
            <div class="total-count"><?= $total_reviews ?> Global Reviews</div>
            <div class="stars-row">
                <?php 
                $full_stars = floor((float)$avg_rating);
                for($i=0; $i<5; $i++) echo $i < $full_stars ? '★' : '☆'; 
                ?>
            </div>
        </div>

        <div class="bars-block">
            <?php foreach ([5, 4, 3, 2, 1] as $star): 
                $count = $star_counts[$star];
                $percent = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
            ?>
                <div class="hud-bar-row">
                    <span class="hud-label"><?= $star ?> Star</span>
                    <div class="hud-track"><div class="hud-fill" style="width: <?= $percent ?>%;"></div></div>
                    <span class="hud-value"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <section class="reviews-feed">
        <h3 class="section-heading">The Archives</h3>
        <?php if ($reviews_res && $reviews_res->num_rows > 0): ?>
            <?php while ($row = $reviews_res->fetch_assoc()): ?>
                <article class="review-card reveal-on-scroll">
                    <div class="reviewer-header">
                        <img style="width: 50px; height: 50px; border-radius: 50%; border: 1px solid #222;" 
                             src="../assets/customer_profiles/<?= htmlspecialchars($row['image']) ?>" alt="">
                        <div class="reviewer-info">
                            <h4><?= htmlspecialchars($row['name']) ?></h4>
                            <span>EST. <?= strtoupper(date('F Y', strtotime($row['created_at']))) ?></span>
                        </div>
                        <div class="review-stars">
                            <?php for($i=0; $i<$row['rating']; $i++) echo '★'; ?>
                        </div>
                    </div>
                    <p class="review-body">"<?= htmlspecialchars($row['review']) ?>"</p>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #444; font-style: italic; padding: 40px;">The archives are currently silent.</p>
        <?php endif; ?>
    </section>

    <aside class="form-sticky-panel reveal-on-scroll">
        <div class="form-header">Leave Your Mark</div>
        <?php if (isset($_SESSION['customer_id'])): ?>
            <form method="POST">
                <textarea name="review_text" class="luxury-input" rows="5" placeholder="Share your experience..." required></textarea>
                <button type="submit" class="submit-btn">Publish Review</button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px;">
                <p style="color: #666; margin-bottom: 20px;">Join the archives to leave your review.</p>
                <a href="../customerLogin.php?supplier_id=<?= $supplier_id ?>" class="submit-btn" style="text-decoration: none; display: inline-block;">Login to Review</a>
            </div>
        <?php endif; ?>
    </aside>
</div>

<script>
    // Intersection Observer to handle reveal animation
    const observerOptions = { threshold: 0.1 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));
</script>
</body>
</html>