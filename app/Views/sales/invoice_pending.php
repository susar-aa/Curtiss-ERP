<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($data['invoice_num'] ?? 'Pending') ?> - <?= APP_NAME ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #F8FAFC;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0F172A;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            padding: 36px 28px;
            text-align: center;
            border: 1px solid #E2E8F0;
        }
        .icon-box {
            width: 64px;
            height: 64px;
            background: #EFF6FF;
            color: #2563EB;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            animation: pulse 2s infinite ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.85; }
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #0F172A;
        }
        .badge {
            display: inline-block;
            background: #F1F5F9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        p {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn-refresh {
            display: inline-block;
            background: #000000;
            color: #FFFFFF;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-refresh:hover {
            background: #27272A;
            transform: translateY(-1px);
        }
        .footer-note {
            font-size: 12px;
            color: #94A3B8;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">⏳</div>
        <h1>Invoice Synchronization Pending</h1>
        <div class="badge">Invoice #<?= htmlspecialchars($data['invoice_num'] ?? 'N/A') ?></div>
        <p>
            This invoice was recently issued by a field sales representative. 
            It is securely saved on the representative's mobile terminal and is scheduled to sync with cloud servers shortly.
        </p>
        <button class="btn-refresh" onclick="location.reload()">Check Again</button>
        <div class="footer-note">
            Auto-checking every 10 seconds...
        </div>
    </div>
    <script>
        setTimeout(function() {
            location.reload();
        }, 10000);
    </script>
</body>
</html>
