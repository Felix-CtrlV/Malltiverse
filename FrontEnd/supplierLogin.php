<?php
$message = "";

// --- 1. DETERMINE RETURN URL (Logic from customerLogin) ---
$redirectUrl = 'suppliers/dashboard.php'; // Default for suppliers

if (isset($_GET['return_url']) && !empty($_GET['return_url'])) {
    $redirectUrl = $_GET['return_url'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    // Avoid loop if referer is the login page itself
    if (strpos($referer, 'supplierLogin.php') === false && strpos($referer, 'pricing.php') === false) {
        $redirectUrl = $referer;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Css/supplier.css">
</head>

<body>

    <div class="container">
        <div class="left-panel">
            <div class="top-row">
                <div class="logo-icon"> <img class="logo-icon" src="assets/images/MalltiverseLogo.jpg" alt="Logo"
                        style="width:100%; height:100%; object-fit:contain;">
                    </div>
                <a href="index.html" class="back-btn">Back to website &rarr;</a>
            </div>
            <div class="bottom-row">
                <div class="quote">
                    <h2>Where Malls,<br>Transcend Reality.</h2>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <h1>Supplier Login</h1>
            <p class="sub-text">Don't have an account? <a href="pricing.php">Create Account</a></p>

            <p id="message" class="<?= !empty($message) ? 'error-msg' : '' ?>"><?= $message; ?></p>

            <form id="loginform" method="POST">
                <input type="hidden" id="return_url" value="<?= htmlspecialchars($redirectUrl) ?>">

                <div class="input-group">
                    <input autocomplete="off" type="email" name="email" id="email" placeholder="Email" required>
                </div>

                <div class="input-group password-container">
                    <input autocomplete="off" type="password" id="password" name="password"
                        placeholder="Enter your password" required>
                    <i id="togglePassword" class="fa-regular fa-eye eye-icon"></i>
                </div>

                <div style="text-align: right; margin-bottom: 15px;">
                    <a href="#" id="forgot-password-link"
                        style="color: #666; text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
                </div>

                <button type="submit" name="submit" class="submit-btn">Login</button>
            </form>

            <div class="divider">
                <span>Or Login with</span>
            </div>

            <div class="social-login">
                <button type="button" class="social-btn"
                    onclick="window.location.href='utils/google_oauth.php?type=supplier&return_url=<?= urlencode($redirectUrl) ?>'">
                    <i class="fa-brands fa-google" style="color:#DB4437;"></i> Google
                </button>
                <button type="button" class="social-btn"
                    onclick="window.location.href='utils/github_oauth.php?type=supplier&return_url=<?= urlencode($redirectUrl) ?>'">
                    <i class="fa-brands fa-github" style="color:#fff;"></i> GitHub
                </button>
            </div>

            <div id="forgot-password-modal"
                style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                <div
                    style="background: linear-gradient(rgb(111 22 253 / 60%), rgb(73 73 120 / 90%)); padding: 30px; border-radius: 10px; max-width: 400px; width: 90%;">
                    <h3 style="margin-top: 0;">Reset Password</h3>
                    <p id="forgot-message" style="color: #666; font-size: 0.9rem;"></p>
                    <input type="email" id="forgot-email" placeholder="Enter your email"
                        style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button onclick="document.getElementById('forgot-password-modal').style.display='none'"
                            style="flex: 1; padding: 10px; background: #f5f5f5; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
                        <button onclick="handleForgotPassword()"
                            style="flex: 1; padding: 10px; background: #000; color: white; border: none; border-radius: 5px; cursor: pointer;">Send
                            Reset Link</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLoginBanAlert(ban) {
            const existing = document.getElementById('companyBanLoginModal');
            if (existing) existing.remove();

            const overlay = document.createElement('div');
            overlay.id = 'companyBanLoginModal';
            overlay.style.position = 'fixed';
            overlay.style.inset = '0';
            overlay.style.background = 'rgba(0,0,0,0.55)';
            overlay.style.zIndex = '10000';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.padding = '18px';

            const card = document.createElement('div');
            card.style.width = '100%';
            card.style.maxWidth = '520px';
            card.style.background = '#0b1220';
            card.style.border = '1px solid rgba(239, 68, 68, 0.35)';
            card.style.borderRadius = '14px';
            card.style.boxShadow = '0 25px 70px rgba(0,0,0,0.45)';
            card.style.padding = '18px 18px 14px 18px';
            card.style.color = '#e5e7eb';

            const title = document.createElement('div');
            title.textContent = 'Company Banned';
            title.style.fontSize = '18px';
            title.style.fontWeight = '700';
            title.style.color = '#fecaca';

            const body = document.createElement('div');
            body.style.marginTop = '10px';
            body.style.fontSize = '13px';
            body.style.color = 'rgba(229,231,235,0.9)';
            body.innerHTML = `Your login was successful, but your company is currently banned. Please contact the administrator for clarification.`;

            const reason = (ban && ban.reason) ? String(ban.reason) : '';
            const until = (ban && ban.banned_until) ? String(ban.banned_until) : '';

            const details = document.createElement('div');
            details.style.marginTop = '12px';
            details.style.padding = '12px';
            details.style.background = 'rgba(239, 68, 68, 0.08)';
            details.style.border = '1px solid rgba(239, 68, 68, 0.18)';
            details.style.borderRadius = '12px';
            details.style.color = '#e5e7eb';

            const reasonRow = document.createElement('div');
            reasonRow.style.marginBottom = '8px';
            reasonRow.innerHTML = `<div style="font-size:11px; text-transform:uppercase; letter-spacing:0.6px; color:rgba(254,202,202,0.95); margin-bottom:4px;">Reason</div><div style="color:rgba(229,231,235,0.95);">${reason ? reason.replace(/</g,'&lt;') : 'Not provided'}</div>`;

            const untilRow = document.createElement('div');
            untilRow.innerHTML = `<div style="font-size:11px; text-transform:uppercase; letter-spacing:0.6px; color:rgba(254,202,202,0.95); margin-bottom:4px;">Banned until</div><div style="color:rgba(229,231,235,0.95);">${until ? until.replace(/</g,'&lt;') : 'Unknown'}</div>`;

            details.appendChild(reasonRow);
            details.appendChild(untilRow);

            const actions = document.createElement('div');
            actions.style.display = 'flex';
            actions.style.gap = '10px';
            actions.style.justifyContent = 'flex-end';
            actions.style.marginTop = '14px';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = 'Continue';
            btn.style.padding = '10px 14px';
            btn.style.borderRadius = '10px';
            btn.style.border = '1px solid rgba(255,255,255,0.15)';
            btn.style.background = '#111827';
            btn.style.color = '#fff';
            btn.style.cursor = 'pointer';
            btn.onclick = () => overlay.remove();

            actions.appendChild(btn);

            card.appendChild(title);
            card.appendChild(body);
            card.appendChild(details);
            card.appendChild(actions);
            overlay.appendChild(card);
            document.body.appendChild(overlay);

            return new Promise((resolve) => {
                btn.addEventListener('click', () => resolve(), { once: true });
            });
        }

        // Password Visibility Toggle
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        toggle.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';

            password.setAttribute('type', type);
            toggle.classList.toggle('fa-eye');
            toggle.classList.toggle('fa-eye-slash');
        });

        // Forgot Password Logic
        document.getElementById('forgot-password-link').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('forgot-password-modal').style.display = 'flex';
        });

        function handleForgotPassword() {
            const email = document.getElementById('forgot-email').value.trim();
            const messageEl = document.getElementById('forgot-message');

            if (!email) {
                messageEl.textContent = 'Please enter your email';
                messageEl.style.color = '#d32f2f';
                return;
            }
            // Send request to forgot_password utils
            fetch('utils/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, type: 'supplier' })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageEl.textContent = data.message;
                        messageEl.style.color = '#388e3c';
                        document.getElementById('forgot-email').value = '';
                        setTimeout(() => { document.getElementById('forgot-password-modal').style.display = 'none'; }, 2000);
                    } else {
                        messageEl.textContent = data.message;
                        messageEl.style.color = '#d32f2f';
                    }
                });
        }

        // --- MAIN LOGIN LOGIC ---
        const form = document.getElementById('loginform');
        const message = document.getElementById('message');

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const returnUrl = document.getElementById('return_url').value;

            if (!email || !password) {
                message.textContent = "All fields are required.";
                message.classList.add('error-msg');
                return;
            }

            fetch('utils/supplierUtil.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password, return_url: returnUrl })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Success: Show banned-company alert if needed, then redirect.
                        const nextUrl = data.return_url || 'suppliers/dashboard.php';
                        if (data.company_banned) {
                            showLoginBanAlert(data.ban).then(() => {
                                window.location.href = nextUrl;
                            });
                        } else {
                            window.location.href = nextUrl;
                        }
                    } else {
                        // Check if we need to redirect to Banned Page
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            // Standard Error
                            message.classList.add('error-msg');
                            message.textContent = data.message || "Invalid Email or Password";
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    message.textContent = "Server error. Please try again.";
                    message.classList.add('error-msg');
                });
        });
    </script>
</body>

</html>