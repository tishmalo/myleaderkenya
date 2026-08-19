<div style="font-family: Arial, Helvetica, sans-serif; color: #1f2937; max-width: 560px; margin: 0 auto;">
    {!! $body !!}

    @if($registration->tickets->isNotEmpty())
        <div style="margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <h3 style="margin: 0 0 12px; font-size: 16px;">Your Tickets</h3>
            @foreach($registration->tickets as $ticket)
                <div style="border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px;">
                    <div style="font-weight: bold;">{{ $ticket->attendee_name }}</div>
                    <div style="color: #6b7280; font-size: 13px; margin-top: 4px;">{{ $ticket->code }}</div>
                    <div style="margin-top: 8px;">
                        <a href="{{ $ticket->verificationUrl() }}" style="color: #006600; font-weight: bold; text-decoration: none;">View / Download Ticket</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
