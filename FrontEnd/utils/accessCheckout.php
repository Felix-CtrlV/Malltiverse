<?php
session_start();
header('Content-Type: application/json');

include '../../BackEnd/config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

if (!isset($_POST['variant_id'], $_POST['supplier_id'], $_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$customer_id = $_SESSION['customer_id'] ?? 1; 

$variant_id = (int) $_POST['variant_id'];
$supplier_id = (int) $_POST['supplier_id'];
$chose_qty = (int) $_POST['quantity'];

$stock_query = "SELECT p.product_name, v.quantity as db_qty 
                FROM product_variant v 
                JOIN products p ON v.product_id = p.product_id 
                WHERE v.variant_id = ?";
                
$stock_stmt = mysqli_prepare($conn, $stock_query);
mysqli_stmt_bind_param($stock_stmt, "i", $variant_id);
mysqli_stmt_execute($stock_stmt);
$stock_result = mysqli_stmt_get_result($stock_stmt);
$stock_data = mysqli_fetch_assoc($stock_result);

if (!$stock_data) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

$product_name = $stock_data['product_name'];
$db_qty = (int)$stock_data['db_qty'];

$cart_check_query = "SELECT cart_id, quantity as inCart_qty FROM cart WHERE customer_id = ? AND variant_id = ?";
$cart_check_stmt = mysqli_prepare($conn, $cart_check_query);
mysqli_stmt_bind_param($cart_check_stmt, "ii", $customer_id, $variant_id);
mysqli_stmt_execute($cart_check_stmt);
$cart_result = mysqli_stmt_get_result($cart_check_stmt);
$cart_data = mysqli_fetch_assoc($cart_result);

$inCart_qty = $cart_data ? (int)$cart_data['inCart_qty'] : 0;

$total_needed = $chose_qty + $inCart_qty;

if ($db_qty >= $total_needed) {
    if ($cart_data) {
        $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE cart_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ii", $total_needed, $cart_data['cart_id']);
        $success = mysqli_stmt_execute($update_stmt);
    } else {
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO cart (customer_id, supplier_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "iiii", $customer_id, $supplier_id, $variant_id, $chose_qty);
        $success = mysqli_stmt_execute($insert_stmt);
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Item added to cart successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }

} else {
    $can_add_more = $db_qty - $inCart_qty;
    
    $error_msg = "Your item '$product_name' is not enough! ";
    $error_msg .= "Only $db_qty items left in stock. ";
    
    if ($inCart_qty > 0) {
        $error_msg .= "You already have $inCart_qty in your cart. ";
        if ($can_add_more > 0) {
            $error_msg .= "You can only add $can_add_more more.";
        } else {
            $error_msg .= "You cannot add any more of this item.";
        }
    }

    echo json_encode([
        'status' => 'error', 
        'message' => $error_msg
    ]);
}
?>