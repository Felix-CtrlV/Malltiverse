<?php
header('Content-Type: application/json');
session_start();

require_once '../../BackEnd/config/dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// 1. Inputs (Name removed to match customer flow, relying on Email)
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$returnUrl = $data['return_url'] ?? 'suppliers/dashboard.php';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

// 2. Fetch Supplier (added status check)
$sql = "SELECT supplier_id, password, status FROM suppliers WHERE email = ? and status != 'inactive' LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if (!$supplier) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

// 3. Verify Password
if (password_verify($password, $supplier['password'])) {
    
    // 4. CHECK BAN STATUS
    // Assuming status column contains 'banned' or 'active'
    if ($supplier['status'] === 'banned') {
        // Set session data for banned.php to use
        $_SESSION['banned_user'] = [
            'id' => $supplier['supplier_id'],
            'type' => 'supplier'
        ];
        
        // Return redirect instruction to frontend
        echo json_encode([
            'success' => false, 
            'redirect' => 'banned.php' 
        ]);
        exit;
    }

    // Success Login
    $_SESSION['supplier_logged_in'] = true;
    $_SESSION['supplierid'] = $supplier['supplier_id'];

    // If the supplier's company is banned, surface a one-time warning for the UI
    $company_banned = false;
    $ban_payload = null;
    $company_id = null;
    $c_stmt = $conn->prepare("SELECT company_id, status FROM companies WHERE supplier_id = ? ORDER BY company_id DESC LIMIT 1");
    if ($c_stmt) {
        $c_stmt->bind_param("i", $supplier['supplier_id']);
        $c_stmt->execute();
        $c_res = $c_stmt->get_result();
        $c_row = $c_res ? $c_res->fetch_assoc() : null;
        $c_stmt->close();
        if ($c_row) {
            $company_id = (int)($c_row['company_id'] ?? 0);
            if (($c_row['status'] ?? '') === 'banned' && $company_id > 0) {
                $company_banned = true;
                $b_stmt = $conn->prepare("SELECT reason, banned_until FROM banned_list WHERE entity_type = 'company' AND entity_id = ? ORDER BY banned_at DESC LIMIT 1");
                if ($b_stmt) {
                    $b_stmt->bind_param("i", $company_id);
                    $b_stmt->execute();
                    $b_res = $b_stmt->get_result();
                    $b_row = $b_res ? $b_res->fetch_assoc() : null;
                    $b_stmt->close();
                    if ($b_row) {
                        $ban_payload = [
                            'reason' => $b_row['reason'] ?? '',
                            'banned_until' => $b_row['banned_until'] ?? null
                        ];
                    }
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'return_url' => $returnUrl,
        'company_banned' => $company_banned,
        'ban' => $ban_payload
    ]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
?>