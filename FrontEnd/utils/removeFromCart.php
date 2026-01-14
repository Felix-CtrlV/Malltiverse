<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);
require_once __DIR__ . '/../../BackEnd/config/dbconfig.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = (int) $_POST['cart_id'];
    
    $customer_id = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : 1;

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    if ($customer_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Customer not logged in']);
        exit;
    }

    if ($cart_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart ID']);
        exit;
    }

    // Delete the cart item, ensuring it belongs to the customer
    $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE cart_id = ? AND customer_id = ?");
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $customer_id);

    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found or already removed']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>