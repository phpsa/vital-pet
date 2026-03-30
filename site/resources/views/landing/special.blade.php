<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Special Landing</title>
    <style>
        :root {
            --bg: #f4f5f7;
            --card: #ffffff;
            --text: #101828;
            --muted: #475467;
            --accent: #0f172a;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            background: linear-gradient(160deg, #f7f8fb 0%, #eef2f7 100%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .panel {
            width: 100%;
            max-width: 760px;
            background: var(--card);
            border: 1px solid #eaecf0;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(16, 24, 40, 0.08);
        }
        h1 { margin: 0 0 12px; font-size: clamp(1.75rem, 3vw, 2.5rem); letter-spacing: -0.02em; }
        p { margin: 0; color: var(--muted); line-height: 1.6; }
        .meta, .stack { margin-top: 20px; display: grid; gap: 12px; }
        .meta code {
            background: #f2f4f7;
            border: 1px solid #eaecf0;
            border-radius: 6px;
            padding: 2px 6px;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            color: #0f172a;
        }
        pre {
            margin: 0;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            font-size: 0.85rem;
            line-height: 1.5;
            overflow: auto;
        }
        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid #e5e7eb;
            border-top-color: #0f172a;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <main class="panel">
        @if (($mode ?? 'handoff') === 'handoff')
            <div class="spinner"></div>
            <div class="stack">
                <h1>Redirecting to payment gateway</h1>
                <p>Your payment request is being prepared with our payment provider and you will be redirected automatically.</p>
            </div>

           
            <script src="{{ $airwallexJsUrl }}"></script>
            <script>
                window.addEventListener('load', async function () {
                    try {
                        const sdk = window.AirwallexComponentsSDK;

                        if (!sdk) {
                            throw new Error('Airwallex SDK is not loaded.');
                        }

                        const env = String(@json($airwallexEnv) || 'demo').toLowerCase() === 'prod' ? 'prod' : 'demo';
                        const initResult = await sdk.init({
                            env,
                            origin: window.location.origin,
                            enabledElements: ['payments'],
                        });

                        if (!initResult?.payments?.redirectToCheckout) {
                            throw new Error('Airwallex hosted checkout is unavailable.');
                        }

                        initResult.payments.redirectToCheckout({
                            intent_id: @json($airwallexIntentId),
                            client_secret: @json($airwallexClientSecret),
                            currency: @json($airwallexCurrency),
                            country_code: @json($airwallexCountryCode),
                            successUrl: @json($landingRequest->gateway_return_url),
                            cancelUrl: @json($landingRequest->gateway_return_url),
                        });
                    } catch (error) {
                        const target = document.getElementById('handoff-error');
                        if (target) {
                            target.textContent = error?.message || 'Unable to redirect to Airwallex.';
                            target.style.display = 'block';
                        }
                    }
                });
            </script>
            <p id="handoff-error" style="display:none;color:#b42318;margin-top:16px;"></p>
        @elseif (($mode ?? '') === 'postback')
            <div class="spinner"></div>
            <div class="stack">
                <h1>Returning to merchant&hellip;</h1>
                <p>Payment confirmed. You will be redirected back automatically.</p>
            </div>

            <form id="postback-form" method="POST" action="{{ $landingRequest->return_url }}" style="display:none">
                <input type="hidden" name="payload" value="{{ json_encode($landingRequest->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}">
            </form>
            <script>
                window.addEventListener('load', function () {
                    document.getElementById('postback-form').submit();
                });
            </script>

        @elseif (($mode ?? '') === 'error')
            <h1>Payment Error</h1>
            <p>We encountered a problem starting your payment. Please try again or contact support if the issue persists.</p>

        @else
            <h1>Payment Processing</h1>
            <p>Your payment is being processed. You will be redirected shortly.</p>
        @endif
    </main>
</body>
</html>
