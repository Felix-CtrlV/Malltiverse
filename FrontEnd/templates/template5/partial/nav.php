<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['customer_id']);
$base_url = "?supplier_id=" . $supplier_id;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .user-dropdown .dropdown-toggle::after { display: none; }
    .user-avatar-nav { width: 35px; height: 35px; object-fit: cover; border: 2px solid #ddd; }
    .nav-auth-buttons .btn { font-size: 0.9rem; border-radius: 20px; padding: 5px 15px; }
    .dropdown-menu { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none; }
    .shopping-back { font-size: 0.9rem; text-decoration: none; color: #666; transition: 0.3s; }
    .shopping-back:hover { color: #000; }
</style>

<nav class="main-nav navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-4">
        <a href="<?= $base_url ?>&page=home" class="brand-link navbar-brand py-0 d-flex align-items-center">
            <?php if (!empty($shop_assets['logo'])): ?>
                <img src="../uploads/shops/<?= $supplier_id ?>/<?= htmlspecialchars($shop_assets['logo']) ?>"
                     alt="Logo" 
                     class="NFlogo rounded-circle me-2" style="height: 45px; width: 45px; object-fit: cover;">
            <?php endif; ?>
            
            <div class="header-text d-flex flex-column justify-content-center">
                <h1 class="site-title-text fs-6 fw-bold mb-0" style="line-height: 1.2;">
                    <?= htmlspecialchars($supplier['tags']) ?>
                </h1>
                <?php if (!empty($supplier['tagline'])): ?>
                    <p class="site-tagline mb-0 text-muted" style="font-size: 0.65rem;"><?= htmlspecialchars($supplier['tagline']) ?></p>
                <?php endif; ?>
            </div>
        </a>
        
        <a href="../customer/index.html" class="shopping-back ms-3 d-none d-md-block">
            <i class="fas fa-arrow-left me-1"></i> Back to Shopping Mall
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto gap-lg-4">
                <?php
                $nav_items = [
                    'home' => 'Home',
                    'products' => 'Products',
                    'about' => 'About Us',
                    'contact' => 'Contact',
                    'review' => 'Review'
                ];
                
                foreach ($nav_items as $key => $label): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page === $key) ? 'active fw-bold text-dark' : 'text-muted' ?>" 
                           href="<?= $base_url ?>&page=<?= $key ?>">
                           <?= $label ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="nav-cart me-2">

                    <a href="javascript:void(0)" 
                    onclick="handleCartClick(<?= $isLoggedIn ? 'true' : 'false' ?>)" 
                    class="position-relative text-dark">
                    <i class="fas fa-shopping-basket fa-lg"></i>
                    <span id="cart-badge-count"></span>
                    </a>
                       
                        <span id="cart-badge-count" 
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                              style="font-size: 0.6rem; min-width: 18px; height: 18px; display: none;">
                            0
                        </span>
                    </a>
                </div>

               <div class="nav-auth-section border-start ps-3">
    <?php if ($isLoggedIn): ?>
        <div class="dropdown user-dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" 
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=User&background=random&color=fff" 
                     class="user-avatar-nav rounded-circle me-2" alt="Profile">
                <small class="fw-medium d-none d-sm-block">My Account</small>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                <!--<li><a class="dropdown-item" href="<?= $base_url ?>&page=cart"><i class="fas fa-shopping-bag me-2"></i> My Orders</a></li>-->
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../utils/logout.php?supplier_id=<?= $supplier_id ?>">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a></li>
            </ul>
        </div>
    <?php else: ?>
        <div class="nav-auth-buttons d-flex gap-2">
            <a href="../customerLogin.php" class="btn btn-outline-dark">Login</a>
            <a href="../customerRegister.php" class="btn btn-dark">Register</a>
        </div>
    <?php endif; ?>
</div>
</nav>

<!--Shopping Cart -->
<div id="loginAlertModal" class="login-alert-overlay" style="display: none;">
    <div class="login-alert-box">
        <button class="close-x" onclick="closeLoginAlert()">&times;</button>
        <div class="icon-wrapper">
            <div class="pulse-ring"></div>
            <i class="fas fa-user-shield"></i>
        </div>
        <h3>Login Required</h3>
        <p>To access your shopping cart and enjoy a seamless experience, please sign in to your account.</p>
        <div class="login-alert-btns">
            <button onclick="closeLoginAlert()" class="btn-cancel">Later</button>
            <a href="../customerLogin.php" class="btn-login">Login Now</a>
        </div>
    </div>
</div>


<script>
function showLoginAlert() {
    Swal.fire({
        title: 'Sign In Required',
        text: 'Please login to add items to your cart.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Login Now'
    }).then((result) => {
        if (result.isConfirmed) {
            
            const currentUrl = encodeURIComponent(window.location.href);
            window.location.href = `../customerLogin.php?return_url=${currentUrl}`;
        }
    });
}</script>

<script>
function handleCartClick(isLoggedIn) {
    if (isLoggedIn) {
        
        window.location.href = "<?= $base_url ?>&page=cart";
    } else {
       
        document.getElementById('loginAlertModal').style.display = 'flex';
    }
}

function closeLoginAlert() {
    document.getElementById('loginAlertModal').style.display = 'none';
}</script>

<style>
.login-alert-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(8px); /* Blur effect */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

/* Alert Box Main Card */
.login-alert-box {
    background: #ffffff;
    padding: 40px 30px;
    border-radius: 24px;
    width: 90%;
    max-width: 380px;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Close button on top-right */
.close-x {
    position: absolute;
    top: 15px;
    right: 20px;
    background: none;
    border: none;
    font-size: 24px;
    color: #999;
    cursor: pointer;
}

/* Icon Styling with Animation */
.icon-wrapper {
    position: relative;
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #2d3436;
    font-size: 32px;
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 2px solid #2d3436;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

/* Text Styling */
.login-alert-box h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
}

.login-alert-box p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 30px;
}

/* Button Group */
.login-alert-btns {
    display: flex;
    gap: 12px;
}

.btn-cancel {
    flex: 1;
    padding: 12px;
    border: 1.5px solid #eee;
    background: #fff;
    color: #666;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-login {
    flex: 1;
    padding: 12px;
    background: #1a1a1a; /* Dark Elegant Color */
    color: #fff;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-login:hover {
    background: #333;
    transform: translateY(-2px);
}

/* Animations */
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes pulse {
    0% { transform: scale(1); opacity: 0.5; }
    100% { transform: scale(1.4); opacity: 0; }
}</style>