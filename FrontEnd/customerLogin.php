<?php
$message = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Css/customerAuth.css">
</head>

<body>

    <div class="container">
        <div class="left-panel login-visual">
            <div class="top-row">
                <div class="logo-icon"><i class="fas fa-shopping-bag"></i></div>
                <a href="index.html" class="back-btn">Back to Home &rarr;</a>
            </div>

            <div class="bottom-row">
                <div class="quote">
                    <h2>Experience Shopping,<br>Beyond Limits.</h2>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <h1>Welcome Back</h1>
            <p class="sub-text">New here? <a href="customerRegister.php">Create Customer Account</a></p>

            <p id="message" class="<?= !empty($message) ? 'error-msg' : '' ?>"><?= $message; ?></p>
            
            <form id="loginform" method="POST">
                <div class="input-group">
                    <input autocomplete="off" type="email" name="email" id="email" placeholder="Email Address" required>
                </div>

                <div class="input-group password-container">
                    <input autocomplete="off" type="password" id="password" name="password" placeholder="Password" required>
                    <i id="togglePassword" class="fa-regular fa-eye eye-icon"></i>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="divider">
                <span>Or continue with</span>
            </div>

            <div class="social-login">
                <button class="social-btn">
                    <i class="fa-brands fa-google" style="color:#DB4437;"></i> Google
                </button>
                <button class="social-btn">
                    <i class="fa-brands fa-github" "></i> GitHub
                </button>
            </div>
        </div>
    </div>

    <script>
        // Password Toggle
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');

        toggle.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            toggle.classList.toggle('fa-eye');
            toggle.classList.toggle('fa-eye-slash');
        });

        // AJAX Login Logic
        const form = document.getElementById('loginform');
        const message = document.getElementById('message');

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Point this to your actual Customer Login Backend
            fetch('utils/customerLoginUtil.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php'; // Redirect to home/dashboard
                } else {
                    message.classList.add('error-msg');
                    message.textContent = data.message || "Invalid Credentials";
                }
            })
            .catch(err => {
                console.error(err);
                message.textContent = "Server error. Please try again.";
            });
        });
    </script>
</body>
</html>