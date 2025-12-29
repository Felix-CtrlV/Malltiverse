<?php
// 1. Database Connection
include("../../BackEnd/dbConfig.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed");
}

// 2. Capture parameters from the POST request (sent by your JS)
$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';

// 3. IMPORTANT: Get Supplier ID from the URL (Referer) or pass it in JS body
// For this example, we assume you are passing it in the 'search' body or via GET
$supplierId = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : null;

if (!$supplierId) {
    echo "<tr><td colspan='5' class='text-center text-danger'>Invalid Supplier ID. Please select a shop first.</td></tr>";
    exit;
}

// 4. Build the Query to filter by BOTH Supplier and Search Term
$query = "SELECT * FROM products WHERE supplier_id = :sid";

if (!empty($searchTerm)) {
    $query .= " AND (product_name LIKE :search OR category LIKE :search)";
}

$stmt = $pdo->prepare($query);
$stmt->bindValue(':sid', $supplierId, PDO::PARAM_INT);

if (!empty($searchTerm)) {
    $stmt->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Output results
if ($products) {
    foreach ($products as $row) {
        echo "<tr>
                <td>" . htmlspecialchars($row['product_name']) . "</td>
                <td>" . htmlspecialchars($row['category']) . "</td>
                <td>$" . number_format($row['price'], 2) . "</td>
                <td><button class='btn btn-primary' onclick='addToCart(" . $row['id'] . ")'>Add</button></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No products found in this shop.</td></tr>";
}
?>