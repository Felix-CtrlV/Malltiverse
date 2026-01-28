<?php
session_start();
header('Content-Type: application/json');

// Assuming your login script sets $_SESSION['customer_id']
if (isset($_SESSION['customer_id'])) {
    require_once '../../../BackEnd/config/dbconfig.php'; // Update path as needed

    $stmt = $conn->prepare("SELECT customer_id, name, email, address, phone, image FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo json_encode(['loggedIn' => true, 'user' => $user]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
} else {
    echo json_encode(['loggedIn' => false]);
}