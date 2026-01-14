<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

include __DIR__ . '/../../BackEnd/config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

if (!isset($_POST['variant_id'], $_POST['supplier_id'], $_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    exit;
}

// Get customer_id from session, fallback to 1 for testing
$customer_id = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : 1;
$variant_id = (int) $_POST['variant_id'];
$supplier_id = (int) $_POST['supplier_id'];
$quantity = max(1, (int) $_POST['quantity']);

if ($customer_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Customer not logged in']);
    exit;
}

// Check if item already exists in cart for this customer and variant
$check_stmt = mysqli_prepare(
    $conn,
    "SELECT cart_id, quantity FROM cart WHERE customer_id = ? AND variant_id = ? AND supplier_id = ?"
);

if (!$check_stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($check_stmt, "iii", $customer_id, $variant_id, $supplier_id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

$success = false;
$error_message = '';

if ($row = mysqli_fetch_assoc($result)) {
    // Item exists, update quantity
    $new_qty = $row['quantity'] + $quantity;
    
    $update_stmt = mysqli_prepare(
        $conn,
        "UPDATE cart SET quantity = ? WHERE cart_id = ?"
    );
    
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "ii", $new_qty, $row['cart_id']);
        $success = mysqli_stmt_execute($update_stmt);
        if (!$success) {
            $error_message = mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $error_message = mysqli_error($conn);
    }
} else {
    // Item doesn't exist, insert new
    $insert_stmt = mysqli_prepare(
        $conn,
        "INSERT INTO cart (customer_id, supplier_id, variant_id, quantity) VALUES (?, ?, ?, ?)"
    );
    
    if ($insert_stmt) {
        mysqli_stmt_bind_param(
            $insert_stmt,
            "iiii",
            $customer_id,
            $supplier_id,
            $variant_id,
            $quantity
        );
        $success = mysqli_stmt_execute($insert_stmt);
        if (!$success) {
            $error_message = mysqli_error($conn);
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        $error_message = mysqli_error($conn);
    }
}

mysqli_stmt_close($check_stmt);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Item added to cart!']);
} else {
    echo json_encode(['status' => 'error', 'message' => $error_message ?: 'Failed to add item to cart']);
}
