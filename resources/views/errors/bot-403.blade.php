<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Check</title>
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #09090b;
            color: #e4e4e7;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .card {
            width: min(92vw, 420px);
            background: #18181b;
            border: 1px solid #27272a;
            border-radius: 24px;
            padding: 32px;
            text-align: center;
        }
        .icon { font-size: 44px; margin-bottom: 12px; }
        h1 { font-size: 20px; margin: 0 0 8px; color: #fff; }
        p { font-size: 14px; line-height: 1.55; color: #a1a1aa; margin: 0 0 20px; }
        .g-recaptcha { display: inline-block; }
        .error { color: #fca5a5; background: rgba(127,29,29,.35); border: 1px solid rgba(220,38,38,.4); border-radius: 12px; padding: 10px 14px; font-size: 13px; margin-top: 16px; display: none; }
        .error.show { display: block; }
        .note { font-size: 12px; color: #71717a; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#128274;</div>
        <h1>Confirm you are not a robot</h1>
        <p>Your IP address (<strong>{{ $ip }}</strong>) was flagged for suspicious activity. Complete the check below to continue.</p>

        @if($recaptchaSiteKey)
            <form method="POST" action="{{ route('bot.verify') }}" data-bot-form>
                @csrf
                <input type="hidden" name="intended" value="{{ $intended }}">
                <div class="g-recaptcha"
                     data-sitekey="{{ $recaptchaSiteKey }}"
                     data-callback="onBotCaptcha"></div>
                <button type="submit" hidden>Continue</button>
                <noscript>
                    <p style="margin-top:14px;font-size:13px;color:#fca5a5;">JavaScript is required to complete this check.</p>
                </noscript>
                @error('g-recaptcha-response')
                    <div class="error show">{{ $message }}</div>
                @enderror
                @if($errors->any() && ! $errors->has('g-recaptcha-response'))
                    <div class="error show">Verification failed. Please try again.</div>
                @endif
            </form>
        @else
            <p>This check is currently unavailable. Please contact support.</p>
        @endif

        <div class="note">Access is restricted because of suspected automated traffic.</div>
    </div>

    @if($recaptchaSiteKey)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function onBotCaptcha() {
            const form = document.querySelector('[data-bot-form]');
            if (form) form.submit();
        }
    </script>
    @endif
</body>
</html>