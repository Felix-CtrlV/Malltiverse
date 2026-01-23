<?php
session_start();
require_once '../../BackEnd/config/dbconfig.php';
require_once __DIR__ . '/oauth_config.php';

// GitHub OAuth Configuration
$client_id = GITHUB_CLIENT_ID;
$client_secret = GITHUB_CLIENT_SECRET;
$redirect_uri = BASE_URL . '/utils/github_oauth.php';

$user_type = $_GET['type'] ?? 'supplier'; // 'supplier' or 'customer'

if (!isset($_GET['code'])) {
    // Step 1: Redirect to GitHub OAuth
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    
    $auth_url = "https://github.com/login/oauth/authorize?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri . '?type=' . $user_type,
        'scope' => 'user:email',
        'state' => $state
    ]);
    header('Location: ' . $auth_url);
    exit;
} else {
    // Step 2: Verify state and exchange code for token
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
        header('Location: ../' . ($user_type === 'supplier' ? 'supplierLogin.php' : 'customerLogin.php') . '?error=invalid_state');
        exit;
    }
    
    unset($_SESSION['oauth_state']);
    $code = $_GET['code'];
    
    $token_url = 'https://github.com/login/oauth/access_token';
    $token_data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri . '?type=' . $user_type
    ];
    
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $token_response = curl_exec($ch);
    curl_close($ch);
    
    $token = json_decode($token_response, true);
    
    if (isset($token['access_token'])) {
        // Step 3: Get user info
        $user_info_url = 'https://api.github.com/user';
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $token['access_token'],
            'User-Agent: Malltiverse-App'
        ]);
        $user_info = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        // Get email (may require additional API call)
        $email_url = 'https://api.github.com/user/emails';
        $ch = curl_init($email_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $token['access_token'],
            'User-Agent: Malltiverse-App'
        ]);
        $emails = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        $email = null;
        if (is_array($emails)) {
            foreach ($emails as $email_data) {
                if ($email_data['primary'] ?? false) {
                    $email = $email_data['email'];
                    break;
                }
            }
            if (!$email && count($emails) > 0) {
                $email = $emails[0]['email'];
            }
        }
        
        if ($user_info && $email) {
            $name = $user_info['name'] ?? $user_info['login'] ?? 'User';
            $github_id = $user_info['id'];
            
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
