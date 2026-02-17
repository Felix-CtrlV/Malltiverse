<?php
include("../BackEnd/config/dbconfig.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = !empty($_POST['phone_full']) ? $_POST['phone_full'] : $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    $password_ok =
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password);

    if (!$password_ok) {
        $message = "Password must be at least 8 characters and include 1 uppercase letter, 1 number, and 1 special symbol.";
    } else {
    
        // Hash Password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
    
        // Default Values
        $status = "Active"; 
        $created_at = date('Y-m-d H:i:s');
        $imageName = "default_user.png"; // Default image

        // Handle Image Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                // Generate unique name
                $imageName = "cust_" . time() . "." . $ext;
                $uploadDir = "assets/customer_profiles/";
                
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        // Insert Query
        $sql = "INSERT INTO customers (name, email, password, phone, address, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssssss", $name, $email, $hashed, $phone, $address, $imageName, $status, $created_at);
            
            if ($stmt->execute()) {
                header("Location: customerLogin.php?msg=success");
                exit();
            } else {
                $message = "Registration failed: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/css/intlTelInput.css" />
    <link rel="stylesheet" href="assets/Css/customerAuth.css?v=1">

    <style>
        .iti {
            width: 100%;
        }

        .iti__dropdown-content {
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(0, 0, 0, 0.12);
        }

        .iti__search-input {
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(0, 0, 0, 0.12);
        }

        .iti__country {
            color: #111827;
        }

        .iti__dial-code {
            color: #374151;
        }

        .iti__country.iti__highlight {
            background-color: rgba(0, 0, 0, 0.06);
        }

        .iti__country.iti__active {
            background-color: rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>

    <div class="container">
        
        <div class="left-panel scrollable-panel">
            <h1>Create Account</h1>
            <p class="sub-text">Already a member? <a href="customerLogin.php">Log In</a></p>
            
            <?php if($message): ?>
                <div class="alert-box"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="profile-upload-center">
                    <label for="u_image" class="profile-circle">
                        <img id="prev_image" src="assets/images/default_avatar.png">
                        <div class="overlay"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" name="profile_image" id="u_image" accept="image/*" onchange="previewImage(this)">
                    <p class="tiny-text">Upload Profile Picture</p> 
                </div>

                <div class="input-group">
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="row-group">
                    <div class="input-group">
                        <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
                        <input type="hidden" id="phone_full" name="phone_full" value="">
                    </div>
                    <div class="input-group">
                        <input type="text" name="address" placeholder="Address" required>
                    </div>
                </div>

                <div class="input-group password-container">
                    <input type="password" id="reg_pass" name="password" placeholder="Create Password" required>
                    <i class="fa-regular fa-eye eye-icon" onclick="togglePass('reg_pass', this)"></i>
                </div>

                <div id="pass_strength" style="margin-top:-10px; margin-bottom: 15px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem; color:#6c757d;">
                        <div id="pass_strength_label">Strength: <b>Weak</b></div>
                        <div id="pass_strength_hint" style="font-size:0.75rem;">Use 8+ chars, A-Z, 0-9, symbol</div>
                    </div>
                    <div style="height:6px; background:#e9ecef; border-radius:999px; margin-top:8px; overflow:hidden;">
                        <div id="pass_strength_bar" style="height:100%; width:0%; background:#dc3545;"></div>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="reg_submit">Register</button>
            </form>
        </div>

        <div class="right-panel register-visual">
            <div class="logo-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="quote-box">
                <h2>Join the Revolution<br>of Virtual Shopping.</h2>
            </div>
        </div>

    </div>

    <script>
        function togglePass(id, icon) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('prev_image').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function hasUppercase(s) {
            return /[A-Z]/.test(s);
        }

        function hasNumber(s) {
            return /[0-9]/.test(s);
        }

        function hasSpecial(s) {
            return /[^A-Za-z0-9]/.test(s);
        }

        function updatePasswordUI(pass) {
            const minLenOk = pass.length >= 8;
            const upperOk = hasUppercase(pass);
            const numOk = hasNumber(pass);
            const specialOk = hasSpecial(pass);

            const strengthLabel = document.getElementById('pass_strength_label');
            const strengthBar = document.getElementById('pass_strength_bar');
            const submitBtn = document.getElementById('reg_submit');

            const metCount = [minLenOk, upperOk, numOk, specialOk].filter(Boolean).length;
            let strength = 'Weak';
            if (metCount === 4 && pass.length >= 12) strength = 'Strong';
            else if (metCount >= 3) strength = 'Medium';

            strengthLabel.innerHTML = `Strength: <b>${strength}</b>`;

            const strengthColor = strength === 'Strong' ? '#198754' : (strength === 'Medium' ? '#fd7e14' : '#dc3545');
            strengthLabel.style.color = strengthColor;

            if (strengthBar) {
                const pct = Math.min(100, Math.round((metCount / 4) * 100));
                strengthBar.style.width = pct + '%';
                strengthBar.style.background = strengthColor;
            }

            const allOk = minLenOk && upperOk && numOk && specialOk;
            submitBtn.disabled = !allOk;
            submitBtn.style.opacity = allOk ? '1' : '0.6';
            submitBtn.style.cursor = allOk ? 'pointer' : 'not-allowed';
        }

        const passInput = document.getElementById('reg_pass');
        const formEl = document.querySelector('form');
        if (passInput) {
            updatePasswordUI(passInput.value || '');
            passInput.addEventListener('input', function () {
                updatePasswordUI(this.value);
            });
        }

        if (formEl && passInput) {
            formEl.addEventListener('submit', function (e) {
                if (window.itiPhone && window.phoneInputEl) {
                    if (!window.itiPhone.isValidNumber()) {
                        e.preventDefault();
                        alert('Please enter a valid phone number.');
                        window.phoneInputEl.focus();
                        return;
                    }
                    const full = window.itiPhone.getNumber();
                    const hidden = document.getElementById('phone_full');
                    if (hidden) hidden.value = full;
                }

                const pass = passInput.value || '';
                const ok = pass.length >= 8 && hasUppercase(pass) && hasNumber(pass) && hasSpecial(pass);
                if (!ok) {
                    e.preventDefault();
                    updatePasswordUI(pass);
                    alert('Password must be at least 8 characters and include uppercase, number, and special symbol.');
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/intlTelInput.min.js"></script>
    <script>
        (function initIntlTelInput() {
            const phoneInput = document.getElementById('phone');
            if (!phoneInput || !window.intlTelInput) return;

            window.phoneInputEl = phoneInput;
            window.itiPhone = window.intlTelInput(phoneInput, {
                initialCountry: 'auto',
                nationalMode: false,
                autoPlaceholder: 'polite',
                utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/utils.js',
                geoIpLookup: function (callback) {
                    fetch('https://ipapi.co/json/')
                        .then(r => r.json())
                        .then(data => callback((data && data.country_code) ? data.country_code : 'US'))
                        .catch(() => callback('US'));
                }
            });

            phoneInput.addEventListener('blur', function () {
                if (!phoneInput.value.trim()) return;
                if (window.itiPhone.isValidNumber()) {
                    phoneInput.style.borderColor = '';
                } else {
                    phoneInput.style.borderColor = '#ff4444';
                }
            });
        })();
    </script>

</body>
</html>