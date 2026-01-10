<?php
session_start();
// Go up two levels: utils -> FrontEnd -> project root, then into BackEnd
include '../../BackEnd/config/dbconfig.php';

if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if (!isset($_SESSION['customer_id'])) {
//         die(json_encode(['status' => 'error', 'message' => 'Please login first']));
//     }

    $customer_id = 1;
    $variant_id = (int) $_POST['variant_id'];
    $supplier_id = (int) $_POST['supplier_id'];
    $quantity = (int) $_POST['quantity'];

    $check_stmt = mysqli_prepare($conn, "SELECT card_id, quantity FROM cart WHERE customer_id = ? AND variant_id = ?");
    mysqli_stmt_bind_param($check_stmt, "ii", $customer_id, $variant_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $new_qty = $row['quantity'] + $quantity;
        // Use card_id with a 'D'
        $update_stmt = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE card_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ii", $new_qty, $row['card_id']);
        $success = mysqli_stmt_execute($update_stmt);
    } else {
        // Standard insert
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO cart (customer_id, supplier_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "iiii", $customer_id, $supplier_id, $variant_id, $quantity);
        $success = mysqli_stmt_execute($insert_stmt);
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Item added to cart!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }

?>