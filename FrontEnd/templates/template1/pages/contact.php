<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../utils/messages.php";

if (!isset($supplier_id)) {
    die("Supplier not defined.");
}

$customer_id = $_SESSION['customer_id'] ?? null;

/* ===============================
    FETCH SUPPLIER + COMPANY + BANNER
================================= */
$stmt = mysqli_prepare($conn, "
    SELECT 
        s.email,
        c.company_id,
        c.company_name,
        c.description,
        c.address,
        c.phone,
        sa.banner
    FROM suppliers s
    LEFT JOIN companies c ON s.supplier_id = c.supplier_id
    LEFT JOIN shop_assets sa ON c.company_id = sa.company_id
    WHERE s.supplier_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $supplier_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$company_id = (int)($data['company_id'] ?? 0);
$db_banner = $data['banner'] ?? ''; // This is the filename from your database

mysqli_stmt_close($stmt);

/* ===============================
   HANDLE MESSAGE SUBMIT
================================= */
$feedback_js = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && $customer_id) {
    $message_text = trim($_POST['message']);

    if (sendContactMessage($conn, $customer_id, $company_id, $message_text)
) {
        $feedback_js = "showPopup('Message sent successfully!', 'success')";
    } else {
        $feedback_js = "showPopup('Failed to send message.', 'error')";
    }
}

/* ===============================
   LOAD BACKGROUND IMAGE
================================= */
$contact_bg = '';
$base_url_path = "/Malltiverse/FrontEnd/uploads/shops/{$supplier_id}/";
$base_fs_path  = $_SERVER['DOCUMENT_ROOT'] . $base_url_path;

$allowed_ext = ['jpg','png','webp'];

// 1. Try to find the specific contact image first
foreach ($allowed_ext as $ext) {
    $file = "{$supplier_id}_contact.$ext";
    if (file_exists($base_fs_path . $file)) {
        $contact_bg = $base_url_path . $file;
        break;
    }
}

// 2. Fallback: Use the banner filename from the database
if (empty($contact_bg) && !empty($db_banner)) {
    // Check if the file recorded in the database actually exists on the server
    if (file_exists($base_fs_path . $db_banner)) {
        $contact_bg = $base_url_path . $db_banner;
    }
}
?>

<style>
<?php if ($contact_bg): ?>
.contact-section {
    background-image: url("<?= htmlspecialchars($contact_bg) ?>");
}
<?php endif; ?>
</style>
</head>

<body class="contact">

<section class="contact-section">

    <div class="contact-content">

        <!-- Company Name -->
        <h2><?= htmlspecialchars($data['company_name'] ?? '') ?></h2>

        <!-- Slogan -->
        <?php if (!empty($data['description'])): ?>
            <p class="slogan"><?= nl2br(htmlspecialchars($data['description'])) ?></p>
        <?php endif; ?>

        <!-- Contact Info -->
        <p><strong>Email:</strong> <?= htmlspecialchars($data['email'] ?? '') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($data['phone'] ?? '') ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($data['address'] ?? '') ?></p>

        <!-- Message Box -->
        <?php if ($customer_id): ?>
            <form method="POST">
                <textarea name="message" placeholder="Write your message..." required></textarea>
                <button type="submit">Send Message</button>
            </form>
        <?php else: ?>
            <textarea 
                placeholder="Please log in to send us a message"
                readonly
                onclick="openAuthModal()"
                style="cursor:pointer;"></textarea>

            <button type="button" onclick="openAuthModal()">
                Login Required
            </button>
        <?php endif; ?>

        <!-- Popup -->
        <div id="message-popup" class="popup"></div>

    </div>

</section>



<!-- ===============================
     WHY US SECTION
================================= -->
<section class="why-us">
    <h2>Why Choose Us?</h2>

    <div class="why-grid">
        <div class="why-card">
            <h3>Fast Response</h3>
            <p>We reply within 24 hours.</p>
        </div>

        <div class="why-card">
            <h3>Trusted Quality</h3>
            <p>We ensure high product standards.</p>
        </div>

        <div class="why-card">
            <h3>Secure Shopping</h3>
            <p>Your data is safe with us.</p>
        </div>
    </div>
</section>

<script>
function showPopup(message, type) {
    const popup = document.getElementById('message-popup');
    popup.textContent = message;
    popup.className = 'popup ' + type;
    popup.style.display = 'block';
    popup.style.opacity = '1';

    // Hide after 2 seconds
    setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => { popup.style.display = 'none'; }, 300);
    }, 2000);
}

// Trigger popup if PHP has feedback
<?php if ($feedback_js): ?>
    <?= $feedback_js ?>;
<?php endif; ?>
</script>

</body>
