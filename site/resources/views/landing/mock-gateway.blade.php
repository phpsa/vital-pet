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
        <h1>Mock Payment Gateway</h1>
        <p>This route exists so the landing flow can be tested locally in one codebase.</p>

        <pre>{{ json_encode($postedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

        <div class="actions">
            <form method="POST">
                @csrf
                <input type="hidden" name="action" value="cancel">
                @foreach ($postedData as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES) }}">
                @endforeach
                <button type="submit">Cancel and return</button>
            </form>
        </div>
    </main>
</body>
</html>