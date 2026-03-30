<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mock Gateway</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; background: #f8fafc; color: #0f172a; }
        .panel { max-width: 760px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 14px; border: 1px solid #e2e8f0; }
        pre { background: #0f172a; color: #e2e8f0; padding: 16px; border-radius: 10px; overflow: auto; }
        .actions { display: flex; gap: 12px; margin-top: 20px; }
        button { padding: 12px 18px; border-radius: 10px; border: 1px solid #0f172a; background: #0f172a; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Payment Processing</h1>
        <p>Your payment is being processed. Please review and complete your payment below.</p>

        <div class="actions">
            <form method="POST">
                @csrf
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="return_url" value="{{ $postedData['return_url'] ?? '' }}">
                <input type="hidden" name="request_id" value="{{ $postedData['request_id'] ?? '' }}">
                <button type="submit">Cancel Payment</button>
            </form>
        </div>
    </main>
</body>
</html>