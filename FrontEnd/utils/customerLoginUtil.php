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
$returnUrl = $data['return_url'] ?? 'index.php'; // fallback

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$sql = "SELECT customer_id, password FROM customers WHERE email = ? and status = 'active' LIMIT 1";
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
    // ပုံသေသတ်မှတ်ထားတဲ့ logic အစား session ထဲမှာ data တွေ သိမ်းမယ်
    $_SESSION['customer_logged_in'] = true; // ဒါက login ဝင်ထားကြောင်း အတည်ပြုတာ
    $_SESSION['customer_id'] = $customer['customer_id']; // ဒါက ဘယ်သူလဲဆိုတာ မှတ်ထားတာ
    $_SESSION['is_logged_in'] = true; // သင်အလိုရှိတဲ့ နောက်ထပ် check variable တစ်ခု

    // Login ဝင်ဝင်ချင်းမှာ အရင်က ID 1 နဲ့ ထည့်ထားတဲ့ ပစ္စည်းတွေကို ID အသစ်ဆီ ပြောင်းပေးမယ် (Optional but recommended)
    $new_id = $customer['customer_id'];
    mysqli_query($conn, "UPDATE cart SET customer_id = '$new_id' WHERE customer_id = 1");

    // Success response ပြန်မယ်
    echo json_encode(['success' => true, 'return_url' => $returnUrl]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}
?>
