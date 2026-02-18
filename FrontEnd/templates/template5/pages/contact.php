<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../utils/messages.php'; 

$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
$company_id = 0;

if ($supplier_id > 0) {
    $query = "SELECT company_id FROM companies WHERE supplier_id = $supplier_id LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $company_id = (int)$row['company_id'];
    }
}

$show_success_modal = false;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $message = trim($_POST['contact_message']);
    $customer_id = $_SESSION['customer_id'] ?? 0;

    if ($customer_id > 0 && $company_id > 0 && !empty($message)) {
        $is_sent = sendContactMessage($conn, $customer_id, $company_id, $message);
        if ($is_sent) { $show_success_modal = true; }
    } else {
        $error_message = "Please ensure you are logged in before sending a message.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Grand Horizon Timepieces</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;500&family=Inter:wght@200;400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* --- Layout Styles --- */
        .split-screen { display: flex; min-height: 100vh; width: 100%; }

        /* PC View: Image Side */
        .visual-side { flex: 1; height: 100vh; position: sticky; top: 0; overflow: hidden; }
        .visual-side img { width: 100%; height: 100%; object-fit: cover; }

        /* PC View: Content Side (Text is positioned high) */
        .content-side { 
            flex: 1; 
            display: flex; 
            align-items: flex-start; 
            justify-content: center; 
            padding: px; 
            background: #fff;
        }

        .form-wrapper { width: 100%; max-width: 450px; }

        header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.8rem;
            margin-bottom: 15px;
            line-height: 1.1;
        }

        header p { color: #666; margin-bottom: 40px; font-size: 1rem; }

        /* --- Form Elements --- */
        .field { margin-bottom: 30px; }
        .field label { 
            display: block; font-size: 11px; text-transform: uppercase; 
            letter-spacing: 2px; margin-bottom: 10px; font-weight: 500;
        }
        textarea {
            width: 100%; border: none; border-bottom: 1px solid #ddd; padding: 10px 0;
            font-family: inherit; font-size: 1rem; outline: none; resize: none;
        }
        .submit-btn {
            background: #1a1a1a; color: #fff; border: none; padding: 18px;
            width: 100%; font-size: 12px; letter-spacing: 2px; cursor: pointer;
        }

        /* --- Mobile Responsive Fixes --- */
        @media (max-width: 991px) {
            .split-screen { flex-direction: column; } 
            
            .visual-side { 
                width: 100%; 
                height: 50vh;
                position: relative; 
            }

            .content-side { 
                width: 100%; 
                padding: 40px 25px; 
                align-items: center; 
            }

            header h1 { font-size: 2.2rem; text-align: center; }
            header p { text-align: center; }
        }

        .luxury-font-title { font-family: 'Cormorant Garamond', serif !important; font-size: 28px !important; }
    </style>
</head>
<body>

    <main class="split-screen">
        <div class="visual-side">
            <img src="../uploads/shops/<?= $supplier_id ?>/<?= $banner1 ?? 'default.jpg' ?>">
        </div>

        <div class="content-side">
            <div class="form-wrapper">
                <header>
                    <h1><?= htmlspecialchars($supplier['tags'] ?? '') ?></h1>
                    <p><?= htmlspecialchars($about2 ?? 'How may we assist you today?') ?></p>
                </header>

                <form class="luxury-form" method="POST">
                    <div class="field">
                        <label>Message</label>
                        <textarea name="contact_message" rows="4" placeholder="I am interested in..." required></textarea>
                    </div>
                    <button type="submit" name="submit_message" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>
    </main>

    <?php if ($show_success_modal): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'MESSAGE SENT', 
                text: 'Your inquiry has been received.',
                icon: 'success',
                confirmButtonColor: '#1a1a1a',
                customClass: { title: 'luxury-font-title' }
            }).then(() => { window.location.href = window.location.href; });
        });
    </script>
    <?php endif; ?>

</body>
</html>