<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submission received</title>
</head>
<body style="margin:0;background:#09090b;color:#fff;font-family:Arial,sans-serif">
    <main style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center">
        <div>
            <p>Your submission was received. Returning to the homepage...</p>
            <p><a href="{{ $redirectUrl }}" target="_top" style="color:#34d399">Continue to homepage</a></p>
        </div>
    </main>
    <script>
        window.top.location.replace(@json($redirectUrl));
    </script>
</body>
</html>
