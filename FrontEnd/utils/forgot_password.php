<?php
session_start();
require_once '../../BackEnd/config/dbconfig.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$user_type = $data['type'] ?? 'supplier'; // 'supplier' or 'customer'

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

// Generate reset token
$reset_token = bin2hex(random_bytes(32));
$reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

if ($user_type === 'supplier') {
    // Check if supplier exists
    $stmt = $conn->prepare("SELECT supplier_id, name FROM suppliers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }
    
    $supplier = $result->fetch_assoc();
    
    // Store reset token (you might want to create a password_resets table)
    // For now, we'll use a simple approach with session
    $_SESSION['reset_token_' . $supplier['supplier_id']] = [
        'token' => $reset_token,
        'expiry' => $reset_expiry,
        'type' => 'supplier'
    ];
    
    // In production, send email with reset link
    // For now, we'll return the token (remove this in production!)
    $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/reset_password.php?token=' . $reset_token . '&type=supplier';
    
    // TODO: Send email with reset link
    // mail($email, 'Password Reset', "Click here to reset: $reset_link");
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset link sent to your email',
        'token' => $reset_token // Remove this in production!
    ]);
    
} else {
    // Customer password reset
    $stmt = $conn->prepare("SELECT customer_id, name FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }
    
    $customer = $result->fetch_assoc();
    
    $_SESSION['reset_token_' . $customer['customer_id']] = [
        'token' => $reset_token,
        'expiry' => $reset_expiry,
        'type' => 'customer'
    ];
    
    $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . '/reset_password.php?token=' . $reset_token . '&type=customer';
    
    // TODO: Send email with reset link
    // mail($email, 'Password Reset', "Click here to reset: $reset_link");
    
    echo json_encode([
        'success' => true,
        'message' => 'Password reset link sent to your email',
        'token' => $reset_token // Remove this in production!
    ]);
}
?>
