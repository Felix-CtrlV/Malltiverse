<?php
include("../BackEnd/config/dbconfig.php");

// Capture Duration from URL (default to 1 if missing)
$duration = isset($_GET['duration']) ? intval($_GET['duration']) : 1;
$calculated_amount = 1000 * $duration;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Step 1: Supplier Info
    $name = $_POST['name'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'] ?? '';

    $password_ok =
        strlen($raw_password) >= 8 &&
        preg_match('/[A-Z]/', $raw_password) &&
        preg_match('/[0-9]/', $raw_password) &&
        preg_match('/[^A-Za-z0-9]/', $raw_password);

    if (!$password_ok) {
        $error_message = "Password must be at least 8 characters and include 1 uppercase letter, 1 number, and 1 special symbol.";
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT);

    // Step 2: Company Info
    $company_name = $_POST['company_name'];
    $tags = $_POST['tags'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = !empty($_POST['phone_full']) ? $_POST['phone_full'] : ($_POST['phone'] ?? '');
    $account_number = $_POST['account_number'] ?? '';

    // Step 3: Company Appearances
    $template_id = intval($_POST['selected_template']);
    $primary_color = $_POST['primary'] ?? '#7d6de3';
    $secondary_color = $_POST['secondary'] ?? '#ff00e6';
    $template_type = $_POST['template_type'] ?? 'image'; // image or video
    $about = $_POST['about'] ?? '';
    $banner_description = $_POST['banner_description'] ?? '';

    // Package info
    $months_to_add = intval($_POST['selected_duration']);
    $rent_price = 1000;
    $renting_price = 1000 * $months_to_add;
    $price = $renting_price; // For companies table

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. INSERT SUPPLIER
        $supplier_image = 'default_supplier.png'; // Default image

        // Handle supplier profile image upload
        if (isset($_FILES['supplier_image']) && $_FILES['supplier_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['supplier_image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $supplier_image = 'supplier_' . time() . '.' . $ext;
                $upload_dir = "assets/customer_profiles/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($_FILES['supplier_image']['tmp_name'], $upload_dir . $supplier_image);
            }
        }

        $sql_supplier = "INSERT INTO suppliers (name, email, password, status, created_at, image) 
                        VALUES (?, ?, ?, 'active', NOW(), ?)";
        $stmt_supplier = $conn->prepare($sql_supplier);
        $stmt_supplier->bind_param("ssss", $name, $email, $password, $supplier_image);

        if (!$stmt_supplier->execute()) {
            throw new Exception("Supplier insertion failed: " . $stmt_supplier->error);
        }

        $supplier_id = $conn->insert_id;
        $stmt_supplier->close();

        // 2. INSERT COMPANY
        $sql_company = "INSERT INTO companies (supplier_id, company_name, tags, description, address, phone, account_number, template_id, renting_price, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt_company = $conn->prepare($sql_company);
        $stmt_company->bind_param("issssssid", $supplier_id, $company_name, $tags, $description, $address, $phone, $account_number, $template_id, $rent_price);

        if (!$stmt_company->execute()) {
            throw new Exception("Company insertion failed: " . $stmt_company->error);
        }

        $company_id = $conn->insert_id;
        $stmt_company->close();

        // 3. Handle file uploads for shop assets
        $upload_dir = "uploads/shops/$supplier_id/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $logo_name = '';
        $banner_name = '';

        // Upload logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_name = 'logo.' . $logo_ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name);
        }

        // Upload banner (image or video)
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $banner_ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
            $banner_name = 'banner.' . $banner_ext;
            move_uploaded_file($_FILES['banner']['tmp_name'], $upload_dir . $banner_name);
        }

        // 4. INSERT SHOP ASSETS
        $sql_assets = "INSERT INTO shop_assets (company_id, logo, banner, primary_color, secondary_color, about, description, template_type) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_assets = $conn->prepare($sql_assets);
        $stmt_assets->bind_param("isssssss", $company_id, $logo_name, $banner_name, $primary_color, $secondary_color, $about, $banner_description, $template_type);

        if (!$stmt_assets->execute()) {
            throw new Exception("Shop assets insertion failed: " . $stmt_assets->error);
        }
        $stmt_assets->close();

        // 5. INSERT RENT PAYMENT
        $paid_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$months_to_add month"));
        $paid_amount = $renting_price;

        $sql_rent = "INSERT INTO rent_payments (company_id, paid_date, due_date, amount) 
                     VALUES (?, ?, ?, ?)";
        $stmt_rent = $conn->prepare($sql_rent);
        $stmt_rent->bind_param("issd", $company_id, $paid_date, $due_date, $paid_amount);

        if (!$stmt_rent->execute()) {
            throw new Exception("Rent payment insertion failed: " . $stmt_rent->error);
        }
        $stmt_rent->close();

        // Commit transaction
        mysqli_commit($conn);

        header("Location: supplierLogin.php?msg=registered");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $error_message = "Registration failed: " . $e->getMessage();
    }
}
}

// Fetch templates
$templatequery = "SELECT * FROM templates";
$templateResult = mysqli_query($conn, $templatequery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/css/intlTelInput.css" />
    <link rel="stylesheet" href="assets/Css/supplierregister.css?v=2">

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
    <style>
        .profile-upload-box {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background-color: #2f2f36;
            border: 2px dashed #3e3e46;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
            overflow: hidden;
            position: relative;
        }

        .profile-upload-box:hover {
            border-color: #7d6de3;
        }

        .profile-upload-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .profile-upload-box i {
            font-size: 48px;
            color: #666;
        }

        .banner-mode-selector {
            margin-bottom: 20px;
        }

        .banner-mode-selector label {
            transition: all 0.3s ease;
        }

        .banner-mode-selector input[type="radio"]:checked+label,
        .banner-mode-selector label:has(input[type="radio"]:checked) {
            border-color: #7d6de3 !important;
            background: #f0f0ff;
        }

        .banner-upload-box video {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }

        .banner-mode-selector label:hover {
            border-color: #7d6de3;
            background: #f9f9ff;
        }

        .error-message {
            background: #ff4444;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="left-panel">
            <h1>Join Us</h1>
            <p class="sub-text">Creating account with <strong><?php echo $duration; ?> Month</strong> Plan. Total:
                $<?php echo number_format($calculated_amount); ?>.</p>

            <p class="sub-text" style="margin-top:-18px;">Already have an account? <a href="supplierLogin.php">Login</a></p>

            <?php if (isset($error_message)): ?>
                <div class="error-message show"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="progress-bar">
                <div class="dot active" id="d1"></div>
                <div class="dot" id="d2"></div>
                <div class="dot" id="d3"></div>
            </div>

            <form id="regForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="selected_duration" value="<?php echo $duration; ?>">

                <!-- STEP 1: Supplier Personal Info -->
                <div class="step-group active" id="step1">
                    <h3>Personal Details</h3>

                    <div class="profile-upload-box" onclick="document.getElementById('supplier_image').click()">
                        <i class="fas fa-user"></i>
                        <img id="prev_supplier_image" src="" alt="Profile">
                    </div>
                    <input type="file" name="supplier_image" id="supplier_image" accept="image/*"
                        onchange="previewImage(this, 'prev_supplier_image')" style="display: none;">

                    <div class="input-group">
                        <input type="text" name="name" placeholder="Full Name" required>
                    </div>
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="input-group password-container">
                        <input type="password" id="password" autocomplete="off" name="password" placeholder="Password" required>
                        <i class="fa-regular fa-eye eye-icon" onclick="togglePass('password', this)"></i>
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

                    <button type="button" class="submit-btn" onclick="nextStep(2)">Next Step</button>
                </div>

                <!-- STEP 2: Company Info -->
                <div class="step-group" id="step2">
                    <h3>Company Details</h3>

                    <div class="input-group">
                        <input type="text" name="company_name" placeholder="Company Name" required>
                    </div>
                    <div class="input-group">
                        <input type="text" name="tags" placeholder="Tags (e.g., Fashion, Electronics)" required>
                    </div>
                    <div class="input-group">
                        <textarea rows="3" name="description" placeholder="Company Description" required></textarea>
                    </div>
                    <div class="input-group">
                        <input type="text" name="address" placeholder="Address" required>
                    </div>
                    <div class="input-group">
                        <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
                        <input type="hidden" id="phone_full" name="phone_full" value="">
                    </div>
                    <div class="input-group">
                        <input type="text" name="account_number" placeholder="Account Number">
                    </div>

                    <div class="btn-row">
                        <button type="button" class="back-btn-form" onclick="prevStep(1)">Back</button>
                        <button type="button" class="submit-btn" onclick="nextStep(3)">Next Step</button>
                    </div>
                </div>

                <!-- STEP 3: Company Appearances -->
                <div class="step-group" id="step3">
                    <div class="step-3-content">
                        <h3>Company Appearance</h3>

                        <!-- Banner Type Selector -->
                        <label style="display: block; margin-bottom: 10px; font-weight: 600;">Banner Type:</label>
                        <div class="banner-mode-selector">
                            <div style="display: flex; gap: 15px;">
                                <label
                                    style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s;">
                                    <input type="radio" name="template_type" value="image" checked
                                        onchange="toggleBannerType('image')" style="margin-right: 8px;">
                                    <i class="fas fa-image" style="margin-right: 5px;"></i> Image
                                </label>
                                <label
                                    style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s;">
                                    <input type="radio" name="template_type" value="video"
                                        onchange="toggleBannerType('video')" style="margin-right: 8px;">
                                    <i class="fas fa-video" style="margin-right: 5px;"></i> Video
                                </label>
                            </div>
                        </div>

                        <!-- Logo and Banner Upload -->
                        <div class="shop-visual-header">
                            <label class="banner-upload-box" for="u_banner" id="banner-label">
                                <span id="banner-ph">
                                    Upload Banner <?= ucfirst($template_type ?? 'Image'); ?>
                                </span> <img id="prev_banner" src="" style="display: none;">
                                <video id="prev_banner_video" src=""
                                    style="display: none; width: 100%; max-height: 200px;"></video>
                            </label>
                            <input type="file" name="banner" id="u_banner" accept="image/*,video/*"
                                onchange="previewBanner(this)">

                            <div class="logo-upload-box">
                                <label class="logo-inner" for="u_logo">
                                    <i class="fas fa-camera" id="logo-icon"></i>
                                    <img id="prev_logo" src="">
                                </label>
                                <input type="file" name="logo" id="u_logo" accept="image/*"
                                    onchange="previewImage(this, 'prev_logo')">
                            </div>
                        </div>

                        <!-- Template Selection -->
                        <h3 style="margin-top: 30px;">Select Template</h3>
                        <div class="template-grid">
                            <?php while ($template = mysqli_fetch_assoc($templateResult)) { ?>
                                <div class="template-card"
                                    style="background-image: url(assets/template_preview/<?= $template['preview_image'] ?>); background-size: cover;"
                                    data-template-id="<?= $template['template_id'] ?>">
                                    <div class="t-name" style="background: rgba(0,0,0,0.7); color:white; padding: 2px 5px;">
                                        <?= $template['template_name'] ?></div>
                                </div>
                            <?php } ?>
                            <input type="hidden" name="selected_template" id="selected_template" value="" required>
                        </div>

                        <!-- Theme Colors -->
                        <span class="input-label">Theme Colors</span>
                        <div class="color-picker-row">
                            <div class="color-item" onclick="document.getElementById('c_primary').click()">
                                <div class="color-preview" id="cp_primary" style="background: #7d6de3;"></div>
                                <span class="color-label">Primary</span>
                                <input name="primary" type="color" id="c_primary" value="#7d6de3"
                                    oninput="updateColor('cp_primary', this.value)">
                            </div>
                            <div class="color-item" onclick="document.getElementById('c_secondary').click()">
                                <div class="color-preview" id="cp_secondary" style="background: #ff00e6;"></div>
                                <span class="color-label">Accent</span>
                                <input name="secondary" type="color" id="c_secondary" value="#ff00e6"
                                    oninput="updateColor('cp_secondary', this.value)">
                            </div>
                        </div>

                        <!-- About and Description -->
                        <div class="input-group">
                            <textarea rows="3" name="about" placeholder="About (for shop assets)"></textarea>
                        </div>
                        <div class="input-group">
                            <textarea rows="3" name="banner_description"
                                placeholder="Banner Description (for shop assets)"></textarea>
                        </div>

                        <div class="btn-row">
                            <button type="button" class="back-btn-form" onclick="prevStep(2)">Back</button>
                            <button type="submit" class="submit-btn">Create & Pay
                                $<?php echo number_format($calculated_amount); ?></button>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="right-panel" id="visualPanel">
            <div class="logo-icon"><i class="fas fa-vr-cardboard"></i></div>
            <div class="quote-box" id="staticQuote">
                <h2>Where Malls,<br>Transcend Reality.</h2>
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
            const metCount = [minLenOk, upperOk, numOk, specialOk].filter(Boolean).length;
            let strength = 'Weak';
            if (metCount === 4 && pass.length >= 12) strength = 'Strong';
            else if (metCount >= 3) strength = 'Medium';

            if (strengthLabel) {
                strengthLabel.innerHTML = `Strength: <b>${strength}</b>`;

                const strengthColor = strength === 'Strong' ? '#198754' : (strength === 'Medium' ? '#fd7e14' : '#dc3545');
                strengthLabel.style.color = strengthColor;

                if (strengthBar) {
                    const pct = Math.min(100, Math.round((metCount / 4) * 100));
                    strengthBar.style.width = pct + '%';
                    strengthBar.style.background = strengthColor;
                }
            }

            return minLenOk && upperOk && numOk && specialOk;
        }

        const passInputEl = document.getElementById('password');
        if (passInputEl) {
            updatePasswordUI(passInputEl.value || '');
            passInputEl.addEventListener('input', function () {
                updatePasswordUI(this.value);
            });
        }

        function nextStep(step) {
            // Validate current step before proceeding
            if (step === 2) {
                const step1Inputs = document.querySelectorAll('#step1 input[required]');
                let isValid = true;
                step1Inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = '#ff4444';
                    } else {
                        input.style.borderColor = '';
                    }
                });
                if (!isValid) {
                    alert('Please fill in all required fields');
                    return;
                }

                const passInput = document.getElementById('password');
                if (passInput) {
                    const ok = updatePasswordUI(passInput.value || '');
                    if (!ok) {
                        alert('Password must be at least 8 characters and include uppercase, number, and special symbol.');
                        passInput.style.borderColor = '#ff4444';
                        return;
                    }
                }
            } else if (step === 3) {
                const step2Inputs = document.querySelectorAll('#step2 input[required], #step2 textarea[required]');
                let isValid = true;
                step2Inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = '#ff4444';
                    } else {
                        input.style.borderColor = '';
                    }
                });
                if (!isValid) {
                    alert('Please fill in all required fields');
                    return;
                }

                if (window.itiPhone && window.phoneInputEl) {
                    if (!window.itiPhone.isValidNumber()) {
                        alert('Please enter a valid phone number.');
                        window.phoneInputEl.style.borderColor = '#ff4444';
                        window.phoneInputEl.focus();
                        return;
                    }
                    const full = window.itiPhone.getNumber();
                    const hidden = document.getElementById('phone_full');
                    if (hidden) hidden.value = full;
                }
            }

            // Handle Form Sections
            document.querySelectorAll('.step-group').forEach(el => el.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');

            // Handle Dots
            document.querySelectorAll('.dot').forEach((el, index) => {
                if (index < step) el.classList.add('active');
                else el.classList.remove('active');
            });

            // Handle Right Panel
            const quote = document.getElementById('staticQuote');
            if (step === 3) {
                quote.style.display = 'none';
            } else {
                quote.style.display = 'block';
            }
        }

        function prevStep(step) {
            nextStep(step);
        }

        function previewImage(input, imgId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById(imgId);
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (imgId === 'prev_supplier_image') {
                        document.querySelector('.profile-upload-box i').style.display = 'none';
                    } else {
                        document.getElementById('logo-icon').style.display = 'none';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleBannerType(type) {
            const bannerInput = document.getElementById('u_banner');
            const bannerPh = document.getElementById('banner-ph');

            if (type === 'video') {
                bannerInput.accept = 'video/*';
                bannerPh.textContent = 'Upload Banner Video';
                document.getElementById('prev_banner').style.display = 'none';
                document.getElementById('prev_banner_video').style.display = 'none';
            } else {
                bannerInput.accept = 'image/*';
                bannerPh.textContent = 'Upload Banner Image';
                document.getElementById('prev_banner').style.display = 'none';
                document.getElementById('prev_banner_video').style.display = 'none';
            }
        }

        function previewBanner(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const bannerType = document.querySelector('input[name="template_type"]:checked').value;
                const reader = new FileReader();

                reader.onload = function (e) {
                    if (bannerType === 'video') {
                        document.getElementById('prev_banner').style.display = 'none';
                        const videoEl = document.getElementById('prev_banner_video');
                        videoEl.src = e.target.result;
                        videoEl.style.display = 'block';
                        videoEl.controls = true;
                    } else {
                        document.getElementById('prev_banner_video').style.display = 'none';
                        const imgEl = document.getElementById('prev_banner');
                        imgEl.src = e.target.result;
                        imgEl.style.display = 'block';
                    }
                    document.getElementById('banner-ph').style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        }

        function updateColor(previewId, colorVal) {
            document.getElementById(previewId).style.background = colorVal;
        }

        // Template Selection Logic
        const cards = document.querySelectorAll('.template-card');
        const hiddenInput = document.getElementById('selected_template');

        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                hiddenInput.value = card.dataset.templateId;
            });
        });

        // Form validation before submit
        document.getElementById('regForm').addEventListener('submit', function (e) {
            const templateSelected = document.getElementById('selected_template').value;
            if (!templateSelected) {
                e.preventDefault();
                alert('Please select a template');
                return false;
            }

            if (window.itiPhone && window.phoneInputEl) {
                if (!window.itiPhone.isValidNumber()) {
                    e.preventDefault();
                    alert('Please enter a valid phone number.');
                    window.phoneInputEl.style.borderColor = '#ff4444';
                    window.phoneInputEl.focus();
                    return false;
                }
                const full = window.itiPhone.getNumber();
                const hidden = document.getElementById('phone_full');
                if (hidden) hidden.value = full;
            }
        });
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