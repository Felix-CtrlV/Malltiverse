<?php
header('Content-Type: application/json');
session_start();

require_once '../../BackEnd/config/dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$sql = "SELECT customer_id, password FROM customers WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

$stmt->close();

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

if (password_verify($password, $customer['password'])) {
    $_SESSION['customer_logged_in'] = true;
    $_SESSION['customer_id'] = $customer['customer_id'];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}
?>
