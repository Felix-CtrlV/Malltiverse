<?php
/**
 * OAuth Configuration File
 * 
 * INSTRUCTIONS:
 * 1. For Google OAuth:
 *    - Go to https://console.cloud.google.com/
 *    - Create a new project or select existing
 *    - Enable Google+ API
 *    - Go to Credentials > Create Credentials > OAuth 2.0 Client ID
 *    - Add authorized redirect URI: http://yourdomain.com/FrontEnd/utils/google_oauth.php
 *    - Copy Client ID and Client Secret below
 * 
 * 2. For GitHub OAuth:
 *    - Go to https://github.com/settings/developers
 *    - Click "New OAuth App"
 *    - Set Authorization callback URL: http://yourdomain.com/FrontEnd/utils/github_oauth.php
 *    - Copy Client ID and Client Secret below
 */

// Google OAuth Credentials
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');

// GitHub OAuth Credentials
define('GITHUB_CLIENT_ID', 'YOUR_GITHUB_CLIENT_ID_HERE');
define('GITHUB_CLIENT_SECRET', 'YOUR_GITHUB_CLIENT_SECRET_HERE');

// Base URL (auto-detect or set manually)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_path = dirname(dirname($_SERVER['PHP_SELF']));
define('BASE_URL', $protocol . '://' . $host . $base_path);
?>
