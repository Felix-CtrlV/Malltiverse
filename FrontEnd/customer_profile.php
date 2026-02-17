<?php
session_start();
require_once '../BackEnd/config/dbconfig.php'; // Update path as needed

// Check Auth
if (!isset($_SESSION['customer_id'])) {
    header("Location: customerLogin.php");
    exit();
}

$id = $_SESSION['customer_id'];
$msg = "";
$msgType = "";

// --- 0. AJAX HANDLER FOR ORDER DETAILS (MODAL) ---
if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'get_order_details') {
    header('Content-Type: application/json');
    $order_id = (int) $_GET['order_id'];

    // 1. Fetch Order + Customer Info
    $stmt = $conn->prepare("
            SELECT o.*, c.name, c.email, c.address, c.phone 
            FROM orders o 
            JOIN customers c ON o.customer_id = c.customer_id 
            WHERE o.order_id = ? AND o.customer_id = ?");
    
    $stmt->bind_param("ii", $order_id, $id);
    $stmt->execute();
    $orderResult = $stmt->get_result();

    if ($orderResult->num_rows === 0) {
        echo json_encode(['error' => 'Order not found or access denied']);
        exit;
    }

    $order = $orderResult->fetch_assoc();

    // 2. Fetch Items for that Order
    $pStmt = $conn->prepare("SELECT od.quantity, p.price, p.product_name, p.product_id, p.image 
                             FROM order_detail od
                             JOIN product_variant pv ON od.variant_id = pv.variant_id 
                             JOIN products p ON pv.product_id = p.product_id 
                             WHERE od.order_id = ?");
    $pStmt->bind_param("i", $order_id);
    $pStmt->execute();
    $products = $pStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['order' => $order, 'products' => $products]);
    exit;
}
// --------------------------------------------------

// Determine active tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// --- HANDLE FORM SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. UPDATE PROFILE
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $img_sql_part = "";
        $filename = "";
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "assets/customer_profiles/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $filename = time() . "_" . basename($_FILES["profile_image"]["name"]);
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $img_sql_part = ", image = ?";
                $_SESSION['user_image'] = $filename; 
            }
        }

        $query = "UPDATE customers SET name=?, email=?, phone=?, address=?";
        if ($img_sql_part) $query .= $img_sql_part;
        $query .= " WHERE customer_id=?";

        $stmt = $conn->prepare($query);

        if ($img_sql_part) {
            $stmt->bind_param("sssssi", $name, $email, $phone, $address, $filename, $id);
        } else {
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);
        }

        if ($stmt->execute()) {
            $msg = "Profile updated successfully.";
            $msgType = "success";
        } else {
            $msg = "Error updating profile.";
            $msgType = "error";
        }
        $activeTab = 'profile';
    }

    // 2. CHANGE PASSWORD
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPass = $_POST['current_password'];
        $newPass = $_POST['new_password'];
        $confirmPass = $_POST['confirm_password'];

        $stmt = $conn->prepare("SELECT password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $dbHash = $row['password'];

        if (password_verify($currentPass, $dbHash)) {
            if ($newPass === $confirmPass) {
                $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE customers SET password=? WHERE customer_id=?");
                $updateStmt->bind_param("si", $newHash, $id);
                
                if ($updateStmt->execute()) {
                    $msg = "Password changed successfully.";
                    $msgType = "success";
                } else {
                    $msg = "Database error occurred.";
                    $msgType = "error";
                }
            } else {
                $msg = "New passwords do not match.";
                $msgType = "error";
            }
        } else {
            $msg = "Current password is incorrect.";
            $msgType = "error";
        }
        $activeTab = 'security';
    }
}

// --- FETCH USER DATA ---
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$currentImg = $user['image'] ? "assets/customer_profiles/" . $user['image'] : "assets/default-user.png";

// --- FETCH ORDERS DATA ---
$orders = [];
if ($activeTab === 'orders') {
    $sql = "SELECT 
        o.order_id, o.order_code, o.order_date, o.order_status, o.price as order_total_price,
        od.variant_id, od.quantity, p.product_name, p.price as item_price
    FROM orders o
    LEFT JOIN order_detail od ON o.order_id = od.order_id
    LEFT JOIN product_variant v ON od.variant_id = v.variant_id
    LEFT JOIN products p ON v.product_id = p.product_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC;";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()) {
            $oid = $row['order_id'];
            if (!isset($orders[$oid])) {
                $orders[$oid] = [
                    'order_code' => $row['order_code'],
                    'order_date' => $row['order_date'],
                    'status' => $row['order_status'],
                    'total_amount' => $row['order_total_price'],
                    'items' => []
                ];
            }
            if ($row['product_name']) {
                $orders[$oid]['items'][] = [
                    'product_name' => $row['product_name'],
                    'quantity' => $row['quantity'],
                    'price' => $row['item_price']
                ];
            }
        }
    }
}

// --- FETCH NOTIFICATIONS ---
$notifications = [];
$msgFilter = isset($_GET['msg_filter']) ? $_GET['msg_filter'] : 'all';

if ($activeTab === 'notifications') {
    $sql = "SELECT cm.message_id, cm.message, cm.status, cm.created_at, c.company_name 
            FROM contact_messages cm 
            JOIN companies c ON cm.company_id = c.company_id 
            WHERE cm.customer_id = ?";
            
    if ($msgFilter === 'pending') {
        $sql .= " AND cm.status = 'pending'";
    } elseif ($msgFilter === 'replied') {
        $sql .= " AND cm.status = 'replied'";
    }
    
    $sql .= " ORDER BY cm.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>My Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #f9fafb;
            --surface-color: #ffffff;
            --border-color: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --accent-color: #0f172a; 
            --accent-hover: #334155;
            --primary-blue: #2563eb;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --warning-bg: #fef9c3;
            --warning-text: #854d0e;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }

        .layout-wrapper { display: flex; width: 100%; max-width: 1100px; gap: 40px; }
        .sidebar { width: 250px; flex-shrink: 0; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 500; margin-bottom: 30px; transition: color 0.2s; }
        .back-link:hover { color: var(--text-primary); }
        .nav-menu { display: flex; flex-direction: column; gap: 5px; }
        .menu-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: 500; transition: all 0.2s; }
        .menu-item i { width: 18px; text-align: center; }
        .menu-item:hover { background: #f3f4f6; color: var(--text-primary); }
        .menu-item.active { background: var(--surface-color); color: var(--primary-blue); box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-weight: 600; }

        .content { flex: 1; background: var(--surface-color); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); padding: 40px; }
        .section-header { margin-bottom: 30px; }
        .section-header h1 { margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.02em; }
        .section-header p { margin: 8px 0 0; color: var(--text-secondary); font-size: 14px; }

        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: var(--text-primary); }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px 14px; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-size: 14px; box-sizing: border-box; transition: all 0.2s; }
        input:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        
        .btn-submit { background-color: var(--accent-color); color: #ffffff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background-color 0.2s; width: auto; }
        .btn-submit:hover { background-color: var(--accent-hover); }

        .profile-pic-section { display: flex; gap: 24px; align-items: center; margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid var(--border-color); }
        .avatar-preview { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .file-upload-btn { background: #fff; border: 1px solid var(--border-color); color: var(--text-primary); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; transition: 0.2s; }
        .file-upload-btn:hover { background: #f9fafb; border-color: #d1d5db; }
        .helper-text { font-size: 12px; color: var(--text-secondary); margin-top: 8px; }

        .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; font-weight: 500; }
        .alert.success { background: var(--success-bg); color: var(--success-text); }
        .alert.error { background: var(--danger-bg); color: var(--danger-text); }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .badge-pending { background: var(--warning-bg); color: var(--warning-text); }
        .badge-replied, .badge-delivered, .badge-completed, .badge-shipped, .badge-confirm { background: var(--success-bg); color: var(--success-text); }
        .badge-cancelled { background: var(--danger-bg); color: var(--danger-text); }

        .order-card { border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 20px; overflow: hidden; }
        .order-header { background: var(--bg-color); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); }
        .order-meta { display: flex; gap: 20px; font-size: 13px; color: var(--text-secondary); flex-wrap: wrap; }
        .order-meta strong { color: var(--text-primary); display: block; margin-bottom: 4px; }
        .order-items { padding: 0 20px; }
        .order-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        .order-item:last-child { border-bottom: none; }
        .item-details { display: flex; flex-direction: column; gap: 4px; }
        .item-name { font-weight: 500; color: var(--text-primary); }
        .item-qty { color: var(--text-secondary); font-size: 13px; }
        .item-price { font-weight: 500; }

        .btn-view-order { background: #fff; border: 1px solid var(--border-color); color: var(--text-primary); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; transition: 0.2s; margin-left: 10px; }
        .btn-view-order:hover { background: #f3f4f6; }

        .notif-card { border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .notif-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .notif-company { font-weight: 600; font-size: 15px; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
        .notif-date { font-size: 12px; color: var(--text-secondary); margin-top: 4px; font-weight: 400; }
        .notif-message { font-size: 14px; color: var(--text-secondary); line-height: 1.5; background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 16px;}
        .reply-box { border-left: 3px solid var(--primary-blue); padding-left: 16px; margin-top: 16px; }
        .reply-label { font-size: 12px; font-weight: 600; color: var(--primary-blue); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
        .reply-text { font-size: 14px; color: var(--text-primary); line-height: 1.5; }

        .empty-state { text-align: center; padding: 40px 0; color: var(--text-secondary); }
        .empty-state i { font-size: 40px; color: #d1d5db; margin-bottom: 16px; }

        .filter-select { padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 14px; color: var(--text-primary); background-color: #fff; outline: none; cursor: pointer; }
        .filter-select:focus { border-color: var(--primary-blue); }

        /* --- NEW SIMPLIFIED MODAL STYLES --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); display: none; justify-content: center; align-items: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.open { opacity: 1; }
        
        .modal-box { 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); 
            width: 90%; 
            max-width: 550px; 
            display: flex; 
            flex-direction: column; 
            max-height: 90vh; 
            transform: scale(0.95);
            transition: transform 0.2s ease;
            overflow: hidden;
        }
        .modal-overlay.open .modal-box { transform: scale(1); }

        .modal-header { padding: 20px 25px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .modal-title { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .modal-close { background: none; border: none; font-size: 24px; color: var(--text-secondary); cursor: pointer; transition: color 0.2s; padding: 0; line-height: 1; }
        .modal-close:hover { color: var(--text-primary); }

        .modal-body { padding: 0; overflow-y: auto; background: #fff; }
        
        .modal-prod-list { padding: 10px 25px; }
        .modal-prod-item { display: flex; align-items: center; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
        .modal-prod-item:last-child { border-bottom: none; }
        .modal-prod-img { width: 50px; height: 50px; border-radius: 8px; background: #f3f4f6; object-fit: cover; border: 1px solid #eee; }
        .modal-prod-info { flex: 1; }
        .modal-prod-name { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
        .modal-prod-meta { font-size: 12px; color: var(--text-secondary); }
        .modal-prod-total { font-weight: 600; font-size: 14px; }

        .modal-footer { background: #f9fafb; padding: 20px 25px; border-top: 1px solid var(--border-color); font-size: 13px; }
        .receipt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }
        .receipt-col h4 { margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; }
        .receipt-val { color: var(--text-primary); line-height: 1.5; font-weight: 500; }
        
        .total-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #d1d5db; }
        .total-label { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .total-amount { font-size: 20px; font-weight: 800; color: var(--primary-blue); }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 850px) {
            body { padding: 20px 15px; }
            .layout-wrapper { flex-direction: column; gap: 20px; }
            .sidebar { width: 100%; }
            .nav-menu { flex-direction: row; flex-wrap: wrap; justify-content: flex-start; }
            .menu-item { flex: 1; justify-content: center; padding: 10px; font-size: 14px; min-width: 120px;}
            .content { padding: 25px 20px; }
            
            .section-header { flex-direction: column; align-items: stretch !important; gap: 15px; }
            .filter-select { width: 100%; }

            .order-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .order-meta { flex-direction: column; gap: 10px; }
            .order-header > div:last-child { width: 100%; justify-content: space-between; margin-top: 10px;}
            
            .notif-top { flex-direction: column; gap: 10px; }
            
            .receipt-grid { grid-template-columns: 1fr; gap: 15px; }
        }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <div class="sidebar">
        <a href="customer/index.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Mall</a>
        <nav class="nav-menu">
            <a href="?tab=profile" class="menu-item <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <i class="far fa-user"></i> Public Profile
            </a>
            <a href="?tab=orders" class="menu-item <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> My Orders
            </a>
            <a href="?tab=notifications" class="menu-item <?php echo $activeTab === 'notifications' ? 'active' : ''; ?>">
                <i class="far fa-bell"></i> Messages
            </a>
            <a href="?tab=security" class="menu-item <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> Security
            </a>
        </nav>
    </div>

    <div class="content">
        <?php if ($msg): ?>
            <div class="alert <?php echo $msgType; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($activeTab === 'profile'): ?>
            <div class="section-header">
                <h1>Public Profile</h1>
                <p>Manage your personal information and how others see you.</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="profile-pic-section">
                    <img src="<?php echo $currentImg; ?>" alt="Profile" class="avatar-preview">
                    <div>
                        <input type="file" name="profile_image" id="fileInput" hidden accept="image/*">
                        <button type="button" class="file-upload-btn" onclick="document.getElementById('fileInput').click()">Change Avatar</button>
                        <div class="helper-text">JPG, GIF or PNG. 2MB max.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                </div>
                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        <?php endif; ?>

        <?php if ($activeTab === 'orders'): ?>
            <div class="section-header">
                <h1>My Orders</h1>
                <p>View and track your recent purchases.</p>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <p>You haven't placed any orders yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $oid => $orderData): ?>
                    <?php 
                        $statusClass = 'badge-' . strtolower($orderData['status'] ?? 'pending');
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-meta">
                                <div>
                                    <strong>Order Placed</strong>
                                    <?php echo date('M d, Y', strtotime($orderData['order_date'])); ?>
                                </div>
                                <div>
                                    <strong>Total Amount</strong>
                                    $<?php echo number_format($orderData['total_amount'], 2); ?>
                                </div>
                                <div>
                                    <strong>Order #</strong>
                                    <?php echo htmlspecialchars($orderData['order_code']); ?>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center;">
                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($orderData['status']); ?></span>
                                <button type="button" class="btn-view-order" onclick="openViewModal(<?php echo $oid; ?>)">View Details</button>
                            </div>
                        </div>
                        <div class="order-items">
                            <?php if (!empty($orderData['items'])): ?>
                                <?php foreach ($orderData['items'] as $item): ?>
                                    <div class="order-item">
                                        <div class="item-details">
                                            <span class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="item-qty">Qty: <?php echo htmlspecialchars($item['quantity']); ?></span>
                                        </div>
                                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="order-item" style="color:var(--text-secondary)">No items found for this order.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($activeTab === 'security'): ?>
            <div class="section-header">
                <h1>Security</h1>
                <p>Update your password to keep your account safe.</p>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Update Password</button>
            </form>
        <?php endif; ?>

        <?php if ($activeTab === 'notifications'): ?>
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1>Messages & Notifications</h1>
                    <p>Your communication history with suppliers.</p>
                </div>
                <form method="GET" style="margin-top: 5px;">
                    <input type="hidden" name="tab" value="notifications">
                    <select name="msg_filter" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $msgFilter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                        <option value="pending" <?php echo $msgFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="replied" <?php echo $msgFilter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                    </select>
                </form>
            </div>
            
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <i class="far fa-envelope"></i>
                    <p>No messages found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $note): ?>
                    <?php 
                        $isReplied = (strtolower($note['status']) == 'replied');
                        $badgeClass = $isReplied ? 'badge-replied' : 'badge-pending'; 
                    ?>
                    
                    <div class="notif-card">
                        <div class="notif-top">
                            <div>
                                <div class="notif-company">
                                    <i class="far fa-building"></i>
                                    <?php echo htmlspecialchars($note['company_name']); ?>
                                </div>
                                <div class="notif-date">Sent on <?php echo date('M d, Y \a\t h:i A', strtotime($note['created_at'])); ?></div>
                            </div>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($note['status']); ?></span>
                        </div>
                        
                        <div class="notif-message">
                            <strong>You asked:</strong> "<?php echo htmlspecialchars($note['message']); ?>"
                        </div>

                        <?php 
                        $msgId = $note['message_id'];
                        $rStmt = $conn->prepare("SELECT * FROM contact_replies WHERE message_id = ?");
                        $rStmt->bind_param("i", $msgId);
                        $rStmt->execute();
                        $replies = $rStmt->get_result();

                        if ($replies->num_rows > 0): 
                            while($reply = $replies->fetch_assoc()):
                        ?>
                            <div class="reply-box">
                                <div class="reply-label">Supplier Reply</div>
                                <div class="reply-text"><?php echo nl2br(htmlspecialchars($reply['reply_text'])); ?></div>
                            </div>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
</div>

<div id="viewModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">Order Details <span id="headerOrderCode" style="color:var(--text-secondary); font-weight:400;">#</span></div>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        
        <div class="modal-body" id="modalProductList">
            </div>

        <div class="modal-footer">
            <div class="receipt-grid">
                <div class="receipt-col">
                    <h4>Shipping To</h4>
                    <div class="receipt-val" id="footAddress">...</div>
                </div>
                <div class="receipt-col">
                    <h4>Payment & Status</h4>
                    <div class="receipt-val" id="footPayment">...</div>
                    <div style="margin-top:4px;"><span id="footStatus" class="badge">...</span></div>
                </div>
            </div>
            
            <div class="total-row">
                <span class="total-label">Total Paid</span>
                <span class="total-amount" id="footTotal">...</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Profile image preview logic
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(event) {
            if(event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    }

    // --- NEW MODAL & AJAX LOGIC ---
    function openViewModal(orderId) {
        const modal = document.getElementById('viewModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('open'), 10);

        const list = document.getElementById('modalProductList');
        list.innerHTML = '<div style="padding:40px; text-align:center; color:#999;">Loading...</div>';

        // Clear previous data
        document.getElementById('headerOrderCode').innerText = '#...';
        document.getElementById('footAddress').innerText = '...';
        document.getElementById('footTotal').innerText = '...';
        document.getElementById('footPayment').innerText = '...';

        fetch(`customer_profile.php?ajax_action=get_order_details&order_id=${orderId}`)
            .then(res => res.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON:", text);
                    alert("Server error.");
                    closeModal();
                    return;
                }

                if (data.error) {
                    alert(data.error);
                    closeModal();
                    return;
                }

                const o = data.order;

                // Update Header & Footer Info
                document.getElementById('headerOrderCode').innerText = '#' + o.order_code;
                document.getElementById('footAddress').innerText = o.address || 'No address provided';
                document.getElementById('footPayment').innerText = o.payment_method ? o.payment_method.toUpperCase() : 'N/A';
                document.getElementById('footTotal').innerText = '$' + parseFloat(o.price).toFixed(2);
                
                // Status Badge
                const pill = document.getElementById('footStatus');
                const s = o.order_status.toLowerCase();
                pill.className = 'badge'; 
                pill.innerText = o.order_status;
                if (s === 'confirm' || s === 'shipped' || s === 'delivered') pill.classList.add('badge-replied');
                else if (s === 'cancelled') pill.classList.add('badge-cancelled');
                else pill.classList.add('badge-pending');

                // Render Products List
                list.innerHTML = '<div class="modal-prod-list"></div>';
                const innerList = list.querySelector('.modal-prod-list');

                if (data.products.length > 0) {
                    data.products.forEach(p => {
                        const imgPath = p.image ? 'uploads/products/' + p.product_id + '_' + p.image : 'assets/placeholder.png';
                        const totalItemPrice = (p.quantity * p.price).toFixed(2);
                        
                        const html = `
                            <div class="modal-prod-item">
                                <img src="${imgPath}" class="modal-prod-img" alt="img">
                                <div class="modal-prod-info">
                                    <div class="modal-prod-name">${p.product_name}</div>
                                    <div class="modal-prod-meta">Qty: ${p.quantity} × $${parseFloat(p.price).toFixed(2)}</div>
                                </div>
                                <div class="modal-prod-total">$${totalItemPrice}</div>
                            </div>
                        `;
                        innerList.insertAdjacentHTML('beforeend', html);
                    });
                } else {
                    list.innerHTML = '<div style="padding:40px; text-align:center; color:#999;">No items in this order.</div>';
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
                list.innerHTML = '<div style="padding:20px; text-align:center; color:red;">Failed to load details.</div>';
            });
    }

    function closeModal() {
        const modal = document.getElementById('viewModal');
        modal.classList.remove('open');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal();
        }
    }
</script>

</body>
</html>