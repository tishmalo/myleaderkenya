<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $ticket->registration->event->title }} — Ticket</title>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 24px; font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; background: #0b0b0b; color: #111; }
    .page { max-width: 560px; margin: 0 auto; }
    .no-print { text-align: right; margin-bottom: 16px; }
    .no-print button { background: #00A86B; color: #fff; border: 0; border-radius: 10px; padding: 10px 18px; font-size: 14px; font-weight: 600; cursor: pointer; }
    .ticket { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.5); }
    .band { height: 8px; background: linear-gradient(90deg, #006600 33%, #111 33% 66%, #BB0000 66%); }
    .body { padding: 28px; }
    .brand { font-weight: 800; letter-spacing: 2px; color: #006600; text-transform: uppercase; font-size: 13px; }
    h1 { font-size: 26px; margin: 8px 0 8px; line-height: 1.2; }
    .meta { color: #555; font-size: 14px; line-height: 1.5; }
    .grid { display: flex; gap: 24px; margin-top: 24px; align-items: center; flex-wrap: wrap; }
    .details { flex: 1; min-width: 220px; }
    .row { margin-bottom: 12px; }
    .label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; }
    .value { font-size: 16px; font-weight: 600; }
    .code { font-family: ui-monospace, Menlo, Consolas, monospace; letter-spacing: 2px; }
    .qr { border: 1px solid #eee; border-radius: 12px; padding: 8px; line-height: 0; }
    .note { margin-top: 20px; font-size: 12px; color: #777; border-top: 1px dashed #ddd; padding-top: 14px; }
    @media print { body { background: #fff; padding: 0; } .no-print { display: none; } .ticket { box-shadow: none; } }
</style>
</head>
<body>
<div class="page">
    <div class="no-print"><button onclick="window.print()">Print / Save as PDF</button></div>
    <div class="ticket">
        <div class="band"></div>
        <div class="body">
            <div class="brand">My Leader Kenya</div>
            <h1>{{ $ticket->registration->event->title }}</h1>
            <div class="meta">
                <div>{{ $ticket->registration->event->date->format('l, F d, Y') }} at {{ $ticket->registration->event->date->format('h:i A') }}</div>
                <div>{{ $ticket->registration->event->location }}</div>
            </div>
            <div class="grid">
                <div class="details">
                    <div class="row">
                        <div class="label">Attendee</div>
                        <div class="value">{{ $ticket->attendee_name }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Ticket code</div>
                        <div class="value code">{{ $ticket->code }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Status</div>
                        <div class="value">{{ $ticket->isCheckedIn() ? 'Checked in' : 'Valid' }}</div>
                    </div>
                </div>
                <div class="qr"><img src="{{ $ticket->qrCodeDataUri() }}" alt="Ticket QR code" width="180" height="180"></div>
            </div>
            <div class="note">Present this ticket (printed or on your phone) at the entrance for scanning.</div>
        </div>
    </div>
</div>
</body>
</html>
