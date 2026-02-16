<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Database connection & User ID
$customer_id = $_SESSION["customer_id"] ?? 0;
$supplier_id = $_GET["supplier_id"] ?? 0;

// 2. Fetch User Data (Only if logged in)
if ($customer_id > 0) {
    // Ensure $conn is defined in your included files before this script runs
    if (isset($conn)) {
        $u_stmt = $conn->prepare("SELECT name, email, image FROM customers WHERE customer_id = ?");
        $u_stmt->bind_param("i", $customer_id);
        $u_stmt->execute();
        $u_res = $u_stmt->get_result()->fetch_assoc();

        $user_name = $u_res['name'] ?? 'User';
        $user_email = $u_res['email'] ?? 'No email';

        $has_image = !empty($u_res['image']) && file_exists("../assets/customer_profiles/" . $u_res['image']);
        $user_image_path = $has_image ? "../assets/customer_profiles/" . $u_res['image'] : "";
        $user_initial = strtoupper(substr($user_name, 0, 1));
    }
}

// 3. Cart Count Logic
$cart_count = 0;
if (isset($conn) && isset($supplier)) {
    $company_id = isset($supplier['company_id']) ? (int) $supplier['company_id'] : 0;
    $sql_cart = "SELECT COUNT(*) AS total_items FROM cart WHERE customer_id = ? AND company_id = ?";
    $stmt_c = $conn->prepare($sql_cart);
    $stmt_c->bind_param("ii", $customer_id, $company_id);
    $stmt_c->execute();
    $cart_count = $stmt_c->get_result()->fetch_assoc()['total_items'] ?? 0;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<nav class="main-nav navbar navbar-expand-lg">
    <div class="container-fluid px-0 nav-container">
        <div class="header-wrapper">
            <div class="logo-container d-flex align-items-center">
                <?php if (!empty($shop_assets['logo'])): ?>
                    <img src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['logo']) ?>"
                        class="NFlogo">
                    <h1 class="site-title"><?= htmlspecialchars($supplier['company_name']) ?></h1>
                <?php endif; ?>
            </div>

            <button class="navbar-toggler" id="navToggle" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navMenuContent">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <?php $base_url = "?supplier_id=" . $supplier_id; ?>
                <li class="nav-item"><a class="navlink <?= ($page === 'home') ? 'active' : '' ?>" href="<?= $base_url ?>&page=home">HOME</a></li>
                <li class="nav-item"><a class="navlink <?= ($page === 'product') ? 'active' : '' ?>" href="<?= $base_url ?>&page=product">PRODUCT</a></li>
                <li class="nav-item"><a class="navlink <?= ($page === 'about') ? 'active' : '' ?>" href="<?= $base_url ?>&page=about">ABOUT US</a></li>
                <li class="nav-item"><a class="navlink <?= ($page === 'contact') ? 'active' : '' ?>" href="<?= $base_url ?>&page=contact">CONTACT</a></li>
                <li class="nav-item"><a class="navlink <?= ($page === 'review') ? 'active' : '' ?>" href="<?= $base_url ?>&page=review">REVIEW</a></li>

                <li class="nav-item">
                    <a class="navlink exit-btn" href="/malltiverse/frontend/customer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </li>

                <li class="nav-item ms-lg-2">
                    <a href="<?= $base_url ?>&page=cart" class="cart-linkk">
                        <i class="fa-solid fa-bag-shopping fs-4"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="badge rounded-pill bg-danger cart-badge"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item ms-lg-3">
                    <?php if ($customer_id > 0): ?>
                        <div class="dropdown">
                            <a class="nav-link p-0 no-caret profile-trigger" href="#" role="button"
                                data-bs-toggle="dropdown">
                                <?php if ($has_image): ?>
                                    <img src="<?= $user_image_path ?>" class="rounded-circle nav-profile-img shadow-sm">
                                <?php else: ?>
                                    <div class="profile-initial-circle shadow-sm"><?= $user_initial ?></div>
                                <?php endif; ?>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end glass-dropdown fade-animation">

                                <div
                                    class="user-card mt-3 mx-3 mb-3 p-3 d-flex align-items-center justify-content-center text-center">
                                    <div class="overflow-hidden">
                                        <h6 class="mb-1 fw-bold text-dark text-truncate user-name">
                                            <?= htmlspecialchars($user_name) ?></h6>
                                        <small
                                            class="text-muted text-truncate d-block user-email"><?= htmlspecialchars($user_email) ?></small>
                                    </div>
                                </div>

                                <div class="dropdown-divider mx-3" style="border-color: rgba(0,0,0,0.05);"></div>
                                <div class="p-3" style="display: flex; flex-direction: column; gap: 15px;">
                                    <a class="btn-logout-modern" href="/malltiverse/FrontEnd/customer_profile.php">
                                        <span>Edit Profile</span>
                                        <i class="fas fa-user-cog ms-2"></i>
                                    </a>

                                    <a class="btn-logout-modern" href="../utils/logout.php?supplier_id=<?= $supplier_id ?>">
                                        <span>Log out</span>
                                        <i class="fa-solid fa-arrow-right-from-bracket ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../customerLogin.php" class="login-pill-btn" data-tooltip="LOGIN">
                            <i class="fa-regular fa-user"></i>
                        </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .main-nav {
        font-family: 'Poppins', sans-serif;

    }

    .glass-dropdown {
        width: 260px;
        padding: 5px;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px) saturate(180%);
        -webkit-backdrop-filter: blur(15px) saturate(180%);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-top: 8px;
        overflow: hidden;
    }

    .user-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        padding: 8px !important;
        margin-bottom: 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-name {
        font-size: 0.9rem;
        color: #000000;
        font-weight: 700;
        margin: 0;
    }

    .user-email {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.7);
        margin: 0;
    }

    /* Modern Buttons - Slim & Transparent Version */
    .btn-logout-modern {

        display: flex !important;
        
        justify-content: center !important;
        align-items: center !important;
        width: 100%;
        padding: 6px 0;
        margin: 2px 0 !important;
        background: rgba(255, 255, 255, 0.1);
        color: #0b0101;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.8rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.2s ease;
    }

    .btn-logout-modern:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    .profile-initial-circle, .nav-profile-img {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .profile-trigger:hover .profile-initial-circle,
    .profile-trigger:hover .nav-profile-img {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
    }

    .cart-linkk {
        color: #0b0101;
        position: relative;
        display: inline-block;
        padding: 0.5rem;
        transform: scale(1.1);
        margin-right: 5px;
    }

    .cart-badge {
        position: absolute;
        top: 2px;
        right: -2px;
        font-size: 0.6rem;
        padding: 0.3em 0.5em;
        transform: translate(20%, -20%);
    }

    .login-pill-btn {
        width: 35px;
        height: 35px;
        background: rgba(255, 255, 255, 0.7);
        color: #0b0101;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .fade-animation {
        animation: fadeInUp 0.2s ease-out forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .no-caret::after { display: none !important; }
</style>

<script>
    const toggleBtn = document.getElementById('navToggle');
    const menu = document.getElementById('navMenuContent');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            menu.classList.toggle('show');
        });
    }
</script>