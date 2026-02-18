<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 - Page Not Found</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #e5e7eb;
            font-family: Arial, Helvetica, sans-serif;
        }
        .card {
            width: min(720px, 92vw);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        }
        .title {
            display: flex;
            gap: 14px;
            align-items: baseline;
            margin: 0 0 10px 0;
        }
        .code {
            font-size: 54px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #a78bfa;
            line-height: 1;
        }
        h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #f9fafb;
        }
        p {
            margin: 10px 0 0 0;
            color: #cbd5e1;
            line-height: 1.6;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        a.btn {
            display: inline-block;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #f9fafb;
            background: rgba(255, 255, 255, 0.06);
            transition: 0.15s ease;
        }
        a.btn:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        a.btn.primary {
            background: #7d6de3;
            border-color: #7d6de3;
        }
        a.btn.primary:hover {
            filter: brightness(1.05);
        }
        .hint {
            margin-top: 14px;
            font-size: 13px;
            color: rgba(226, 232, 240, 0.8);
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">
            <div class="code">404</div>
            <h1>Page not found</h1>
        </div>
        <p>The page you requested doesnt exist or was moved.</p>
        <div class="actions">
            <a class="btn primary" href="/Malltiverse/FrontEnd/">Go to Home</a>
            <a class="btn" href="/Malltiverse/FrontEnd/customerLogin.php">Customer Login</a>
            <a class="btn" href="/Malltiverse/FrontEnd/supplierLogin.php">Supplier Login</a>
        </div>
        <div class="hint">URL: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?></div>
    </div>
</body>
</html>
