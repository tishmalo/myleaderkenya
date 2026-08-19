@extends('layouts.landing')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');

:root {
    --kenya-red:    #BB0000;
    --kenya-black:  #111111;
    --kenya-white:  #F5F5F0;
    --green-bright: #00A86B;
    --kenya-green:  #006600;
}

body { font-family: 'Barlow', sans-serif; background: #0a0a0a; color: var(--kenya-white); }
h1,h2,h3,h4 { font-family: 'Oswald', sans-serif; }

.flag-stripe {
    height: 4px;
    background: linear-gradient(90deg, var(--kenya-green) 33%, #1a1a1a 33% 66%, var(--kenya-red) 66%);
}

.details-container {
    max-width: 1100px; margin: 0 auto;
    padding: 64px 32px 80px;
}

.back-link {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: rgba(255,255,255,0.4); text-decoration: none;
    transition: color 0.2s; margin-bottom: 32px;
}
.back-link:hover { color: var(--green-bright); }

.details-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 48px;
}

/* ── LEFT COLUMN: EVENT DETAILS ── */
.event-info-panel h1 {
    font-size: clamp(32px, 4vw, 48px);
    font-weight: 700; line-height: 1.1;
    color: white; margin-bottom: 24px;
}
.event-meta-strip {
    display: flex; flex-wrap: wrap; gap: 24px;
    padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 32px;
}
.meta-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 14px; color: rgba(245,245,240,0.65);
}
.meta-item i { font-size: 16px; color: var(--green-bright); }

.event-poster-large {
    width: 100%; max-height: 460px; object-fit: cover;
    border-radius: 20px; margin-bottom: 24px;
    border: 1px solid rgba(255,255,255,0.08);
    background: #111;
}
.event-video {
    width: 100%; aspect-ratio: 16 / 9; border-radius: 20px; margin-top: 32px;
    border: none; background: #111; display: block;
}
.event-description-text {
    font-size: 16px; line-height: 1.75;
    color: rgba(245,245,240,0.6);
}
.event-description-text p { margin-bottom: 1.5rem; }

/* ── RIGHT COLUMN: REGISTRATION CARD ── */
.registration-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 28px; padding: 36px;
    position: sticky; top: 100px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.6);
}
.registration-card h2 {
    font-size: 24px; font-weight: 700; color: white;
    margin-bottom: 8px;
}
.price-display {
    font-size: 28px; font-weight: 700; color: var(--green-bright);
    margin-bottom: 24px; display: flex; align-items: baseline; gap: 6px;
}
.price-display span { font-size: 14px; color: rgba(245,245,240,0.4); font-weight: 400; }

.form-group {
    display: flex; flex-col; gap: 8px; margin-bottom: 20px;
    flex-direction: column;
}
.form-group label {
    font-size: 13px; font-weight: 600; color: rgba(245,245,240,0.7);
}
.form-input {
    background: #1c1c1c; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px; padding: 12px 16px;
    color: white; font-size: 14px; width: 100%;
    transition: all 0.2s;
}
.form-input:focus {
    outline: none; border-color: var(--green-bright);
    box-shadow: 0 0 0 2px rgba(0, 168, 107, 0.15);
}
.form-helper {
    font-size: 11px; color: rgba(245,245,240,0.35); margin-top: 4px;
}

.submit-btn {
    display: block; width: 100%; padding: 15px;
    background: var(--green-bright); color: white;
    border: none; border-radius: 14px;
    font-size: 15px; font-weight: 700; cursor: pointer;
    transition: all 0.2s; text-align: center;
    text-decoration: none; margin-top: 28px;
}
.submit-btn:hover { background: #00be7a; transform: translateY(-1px); }
.submit-btn:active { transform: translateY(0); }

.payment-notice {
    font-size: 12px; color: rgba(245,245,240,0.35); text-align: center;
    line-height: 1.5; margin-top: 16px;
}

.concluded-panel {
    background: rgba(255,255,255,0.02);
    border: 1px dashed rgba(255,255,255,0.08);
    border-radius: 20px; p-6: 24px; text-align: center;
    padding: 32px 24px;
    color: rgba(245,245,240,0.4);
}

@media (max-width: 900px) {
    .details-grid { grid-template-columns: 1fr; gap: 40px; }
    .registration-card { position: static; }
}
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<div class="details-container">
    <a href="{{ route('events.public') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Events
    </a>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl p-5 mb-8 flex items-center gap-3">
            <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl p-5 mb-8 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-xl flex-shrink-0"></i>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl p-5 mb-8">
            <div class="flex items-center gap-2 mb-2 font-semibold">
                <i class="fas fa-exclamation-circle text-red-500"></i> Please correct the errors below:
            </div>
            <ul class="list-disc pl-5 space-y-1 text-sm text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="details-grid">
        <!-- LEFT: EVENT DESCRIPTION -->
        <div class="event-info-panel">
            @if($event->poster)
                <img src="{{ asset('storage/' . $event->poster) }}" alt="{{ $event->title }}" class="event-poster-large">
            @endif
            <h1>{{ $event->title }}</h1>
            
            <div class="event-meta-strip">
                <div class="meta-item">
                    <i class="far fa-calendar-alt"></i>
                    <span>{{ $event->date->format('l, F d, Y') }} at {{ $event->date->format('h:i A') }}</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $event->location }}</span>
                </div>
            </div>

            <div class="event-description-text">
                {!! nl2br(e($event->description)) !!}

                @if($event->promo_video_embed_url)
                    <iframe class="event-video" src="{{ $event->promo_video_embed_url }}"
                            title="{{ $event->title }} promo video"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                @endif
            </div>
        </div>

        <!-- RIGHT: REGISTRATION FORM -->
        <div>
            @if($event->date->isPast())
                <div class="concluded-panel">
                    <i class="fas fa-calendar-times text-3xl mb-3 opacity-30"></i>
                    <h3 class="text-lg font-semibold text-zinc-300 mb-1">Event Completed</h3>
                    <p class="text-sm">Registration for this assembly has closed as the event date has passed.</p>
                </div>
            @else
                <div class="registration-card">
                    <h2>Book Your Seat</h2>
                    <div class="price-display">
                        KES {{ number_format($event->price) }}
                        <span>/ person</span>
                    </div>

                    <form method="POST" action="{{ route('events.register', $event->slug) }}">
                        @csrf
                        
                        <div class="form-group row">
                            <label for="name">Full Names</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="e.g. johndoe@example.com" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 0712345678" class="form-input">
                            <span class="form-helper">Enter number to receive mobile money (M-Pesa) payment prompt.</span>
                        </div>

                        <div class="form-group">
                            <label for="user_type">Audience Type</label>
                            <select id="user_type" name="user_type" required class="form-input">
                                <option value="" disabled {{ old('user_type') ? '' : 'selected' }}>Select your role...</option>
                                <option value="Aspirant" {{ old('user_type') == 'Aspirant' ? 'selected' : '' }}>Aspirant</option>
                                <option value="Campaign Manager" {{ old('user_type') == 'Campaign Manager' ? 'selected' : '' }}>Campaign Manager</option>
                                <option value="Voter" {{ old('user_type') == 'Voter' ? 'selected' : '' }}>Voter</option>
                                <option value="Party Representative" {{ old('user_type') == 'Party Representative' ? 'selected' : '' }}>Party Representative</option>
                                <option value="Trainers" {{ old('user_type') == 'Trainers' ? 'selected' : '' }}>Trainers</option>
                                <option value="Ambassador" {{ old('user_type') == 'Ambassador' ? 'selected' : '' }}>Ambassador</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="position">Aspirancy Position</label>
                            <select id="position" name="position" required class="form-input">
                                <option value="" disabled {{ old('position') ? '' : 'selected' }}>Select targeted position...</option>
                                <option value="President" {{ old('position') == 'President' ? 'selected' : '' }}>President</option>
                                <option value="Governor" {{ old('position') == 'Governor' ? 'selected' : '' }}>Governor</option>
                                <option value="Senator" {{ old('position') == 'Senator' ? 'selected' : '' }}>Senator</option>
                                <option value="Women Rep" {{ old('position') == 'Women Rep' ? 'selected' : '' }}>Women Rep</option>
                                <option value="MPs" {{ old('position') == 'MPs' ? 'selected' : '' }}>MPs</option>
                                <option value="MCAs" {{ old('position') == 'MCAs' ? 'selected' : '' }}>MCAs</option>
                                <option value="Other" {{ old('position') == 'Other' ? 'selected' : '' }}>Other / None</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Number of Seats</label>
                            <select id="quantity" name="quantity" required class="form-input">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('quantity') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div id="attendee-names" class="form-group" style="display: none;">
                            <label>Attendee Names (optional)</label>
                            <div id="attendee-names-list" class="space-y-3"></div>
                            <span class="form-helper">Leave blank to use "Guest 2, Guest 3...".</span>
                        </div>

                        <button type="submit" class="submit-btn">
                            Proceed to Pay &amp; Register &nbsp;<i class="fas fa-external-link-alt text-xs opacity-80"></i>
                        </button>

                        <p class="payment-notice">
                            You will be redirected to the secure iPay gateway to complete your payment of <strong>KES <span id="total-amount">{{ number_format($event->price) }}</span></strong>.
                        </p>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    const eventPrice = {{ $event->price }};
    const quantity = document.getElementById('quantity');
    const total = document.getElementById('total-amount');
    const namesWrap = document.getElementById('attendee-names');
    const namesList = document.getElementById('attendee-names-list');

    function renderTotal() {
        const qty = parseInt(quantity.value, 10) || 1;
        total.textContent = new Intl.NumberFormat('en-KE').format(eventPrice * qty);
    }

    function renderAttendees() {
        const qty = parseInt(quantity.value, 10) || 1;
        namesWrap.style.display = qty > 1 ? '' : 'none';
        namesList.innerHTML = '';
        for (let i = 2; i <= qty; i++) {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'attendee_names[]';
            input.className = 'form-input';
            input.placeholder = 'Attendee ' + i + ' name';
            namesList.appendChild(input);
        }
    }

    quantity.addEventListener('change', function () {
        renderTotal();
        renderAttendees();
    });

    renderTotal();
    renderAttendees();
</script>
@endsection
