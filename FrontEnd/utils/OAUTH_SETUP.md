# OAuth Setup Instructions

## Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API:
   - Go to "APIs & Services" > "Library"
   - Search for "Google+ API" and enable it
4. Create OAuth 2.0 Credentials:
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "OAuth 2.0 Client ID"
   - Choose "Web application"
   - Add Authorized redirect URI: `http://yourdomain.com/FrontEnd/utils/google_oauth.php`
5. Copy the Client ID and Client Secret
6. Update `FrontEnd/utils/oauth_config.php` with your credentials

## GitHub OAuth Setup

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in:
   - Application name: Your App Name
   - Homepage URL: `http://yourdomain.com`
   - Authorization callback URL: `http://yourdomain.com/FrontEnd/utils/github_oauth.php`
4. Click "Register application"
5. Copy the Client ID and generate a Client Secret
6. Update `FrontEnd/utils/oauth_config.php` with your credentials

## Important Notes

- Replace `YOUR_GOOGLE_CLIENT_ID_HERE` and `YOUR_GOOGLE_CLIENT_SECRET_HERE` in `oauth_config.php`
- Replace `YOUR_GITHUB_CLIENT_ID_HERE` and `YOUR_GITHUB_CLIENT_SECRET_HERE` in `oauth_config.php`
- Make sure your redirect URIs match exactly (including http/https and domain)
- For production, use HTTPS URLs
