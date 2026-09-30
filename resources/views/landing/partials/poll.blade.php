{{-- Everything here is pre-computed by App\Support\PollPresenter. No queries,
     no date or number formatting, no business rules. The options, actions and
     discussion live in landing.partials.poll-options so the homepage section
     and the shareable poll page render them identically. --}}
<section class="poll-section" id="poll">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-label">{{ $poll['section_label'] }}</div>
            <h2 class="section-title">{{ $poll['question'] }}</h2>
            <p class="section-sub">{{ $poll['status_line'] }}</p>
        </div>

        @include('landing.partials.poll-options', ['poll' => $poll])
    </div>
</section>