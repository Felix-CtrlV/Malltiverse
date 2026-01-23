<?php
session_start();
require_once '../../BackEnd/config/dbconfig.php';
require_once __DIR__ . '/oauth_config.php';

// Google OAuth Configuration
$client_id = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri = BASE_URL . '/utils/google_oauth.php';

$user_type = $_GET['type'] ?? 'supplier'; // 'supplier' or 'customer'

if (!isset($_GET['code'])) {
    // Step 1: Redirect to Google OAuth
    $auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri . '?type=' . $user_type,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    header('Location: ' . $auth_url);
    exit;
} else {
    // Step 2: Exchange code for token
    $code = $_GET['code'];
    
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri . '?type=' . $user_type,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    $token_response = curl_exec($ch);
    curl_close($ch);
    
    $token = json_decode($token_response, true);
    
    if (isset($token['access_token'])) {
        // Step 3: Get user info
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token['access_token'];
        $user_info = json_decode(file_get_contents($user_info_url), true);
        
        if ($user_info && isset($user_info['email'])) {
            $email = $user_info['email'];
            $name = $user_info['name'] ?? $user_info['given_name'] ?? 'User';
            $google_id = $user_info['id'];
            
            if ($user_type === 'supplier') {
                // Check if supplier exists
                $stmt = $conn->prepare("SELECT supplier_id FROM suppliers WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $supplier = $result->fetch_assoc();
                    $_SESSION['supplier_logged_in'] = true;
                    $_SESSION['supplierid'] = $supplier['supplier_id'];
                    header('Location: ../suppliers/dashboard.php');
                } else {
                    // Create new supplier account
                    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO suppliers (name, email, password, company_name, status) VALUES (?, ?, ?, ?, 'pending')");
                    $company_name = $name . "'s Shop";
                    $stmt->bind_param("ssss", $name, $email, $password, $company_name);
                    
                    if ($stmt->execute()) {
                        $supplier_id = $conn->insert_id;
                        $_SESSION['supplier_logged_in'] = true;
                        $_SESSION['supplierid'] = $supplier_id;
                        header('Location: ../suppliers/dashboard.php');
                    } else {
                        header('Location: ../supplierLogin.php?error=registration_failed');
                    }
                }
            } else {
                // Customer login
                $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $customer = $result->fetch_assoc();
                    $_SESSION['customer_logged_in'] = true;
                    $_SESSION['customer_id'] = $customer['customer_id'];
                    header('Location: ../index.html');
                } else {
                    // Create new customer account
                    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $email, $password);
                    
                    if ($stmt->execute()) {
                        $customer_id = $conn->insert_id;
                        $_SESSION['customer_logged_in'] = true;
                        $_SESSION['customer_id'] = $customer_id;
                        header('Location: ../index.html');
                    } else {
                        header('Location: ../customerLogin.php?error=registration_failed');
                    }
                }
            }
            exit;
        }
    }
    
    header('Location: ../' . ($user_type === 'supplier' ? 'supplierLogin.php' : 'customerLogin.php') . '?error=oauth_failed');
    exit;
}
?>
