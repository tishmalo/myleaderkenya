<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset your password</title></head>
<body style="margin:0;padding:0;background:#09090b;font-family:Arial,sans-serif;color:#f4f4f5;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#09090b;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;">
<tr><td style="height:5px;background:#08752f;border-right:200px solid #c40000;border-left:200px solid #18181b;"></td></tr>
<tr><td align="center" style="padding:30px 20px 24px;background:#18181b;border-bottom:1px solid #27272a;"><img src="{{ asset('images/myleader.png') }}" width="64" height="64" alt="My Leader Kenya" style="display:block;object-fit:contain;margin-bottom:14px;"><div style="font-size:20px;font-weight:800;letter-spacing:2px;color:#fff;">MY LEADER KENYA</div></td></tr>
<tr><td style="padding:36px 34px;background:#18181b;"><h1 style="margin:0 0 14px;font-size:26px;line-height:1.25;color:#fff;">Reset your password</h1><p style="margin:0 0 25px;font-size:16px;line-height:1.7;color:#a1a1aa;">We received a request to reset the password for your My Leader Kenya account. Use the secure button below to choose a new password.</p>
<table role="presentation" cellspacing="0" cellpadding="0"><tr><td style="border-radius:12px;background:#059669;"><a href="{{ $resetUrl }}" style="display:inline-block;padding:15px 24px;color:#fff;text-decoration:none;font-size:15px;font-weight:700;">Reset my password</a></td></tr></table>
<p style="margin:25px 0 0;font-size:14px;line-height:1.6;color:#71717a;">This link expires in {{ $expiresIn }} minutes. If you did not request a password reset, you can safely ignore this email.</p></td></tr>
<tr><td style="padding:22px 34px;background:#101012;border-radius:0 0 8px 8px;color:#71717a;font-size:12px;line-height:1.6;">For your security, never share this email or reset link with anyone.<br>&copy; {{ date('Y') }} My Leader Kenya.</td></tr>
</table></td></tr></table></body></html>
