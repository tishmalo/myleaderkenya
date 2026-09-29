@php
    $pollOptions = collect($poll['options']);
    $isPolitical = $poll['poll_type'] === 'political';
    $closesAt = \Illuminate\Support\Carbon::parse($poll['ends_at']);
    $canVote = $poll['is_open'] && ! $poll['has_voted'];
    $showForm = $canVote && auth()->check();
@endphp

<section class="poll-section" id="poll">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-label">{{ $isPolitical ? 'Vote Your Candidate' : 'Have Your Say' }}</div>
            <h2 class="section-title">{{ $poll['question'] }}</h2>
            <p class="section-sub">
                @if(! $poll['is_open'] && ! $poll['has_started'])
                    Voting opens {{ \Illuminate\Support\Carbon::parse($poll['starts_at'])->format('j M Y, g:ia') }} and closes {{ $closesAt->format('j M Y, g:ia') }}.
                @elseif($poll['is_open'])
                    Voting closes {{ $closesAt->format('j M Y, g:ia') }}. Results unlock when the poll closes.
                @else
                    This poll closed {{ $closesAt->format('j M Y, g:ia') }}.
                @endif
            </p>
        </div>

        @if(session('poll_notice'))
            <div class="poll-notice" role="status">{{ session('poll_notice') }}</div>
        @endif
        @if(session('poll_comment_notice'))
            <div class="poll-notice" role="status">{{ session('poll_comment_notice') }}</div>
        @endif

        @if($showForm)
            <form method="POST" action="{{ route('poll.vote', $poll['id']) }}" class="poll-form">
                @csrf
        @endif

        <div class="poll-grid {{ $isPolitical ? 'is-political' : '' }}">
            @foreach($pollOptions as $option)
                @php
                    $optionResult = $poll['results'][$option['id']] ?? null;
                    $isChosen = $poll['voted_option_id'] === $option['id'];
                    $percent = $optionResult['percent'] ?? 0;
                @endphp

                @if($showForm)
                    <label class="poll-option is-selectable">
                        <input type="radio" name="option_id" value="{{ $option['id'] }}" class="poll-radio" required>
                        <span class="poll-option-bar" style="--poll-percent: 0%"></span>
                        <span class="poll-option-inner">@include('landing.partials.poll-option-body', ['option' => $option, 'isPolitical' => $isPolitical])</span>
                    </label>
                @else
                    <div class="poll-option {{ $isChosen ? 'is-chosen' : '' }} {{ $poll['reveal_results'] ? 'has-result' : '' }}">
                        @if($poll['reveal_results'])
                            <div class="poll-option-bar" style="--poll-percent: {{ $percent }}%"></div>
                        @endif
                        <div class="poll-option-inner">
                            @include('landing.partials.poll-option-body', ['option' => $option, 'isPolitical' => $isPolitical])
                            @if($poll['reveal_results'])
                                <span class="poll-option-tally">
                                    <strong>{{ $percent }}%</strong>
                                    <small>{{ number_format($optionResult['votes'] ?? 0) }} {{ \Illuminate\Support\Str::plural('vote', $optionResult['votes'] ?? 0) }}</small>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @error('option_id')<p class="poll-error">{{ $message }}</p>@enderror

        @if($showForm)
            <div class="poll-actions">
                <button type="submit" class="poll-submit">Cast your vote</button>
                <p class="poll-hint">One vote per account. You can change it until the poll closes.</p>
            </div>
        @elseif($canVote && ! auth()->check())
            <p class="poll-hint poll-hint-login">
                <button type="button" class="poll-login-link" onclick="window.openFrontendAuth ? window.openFrontendAuth('login') : null">Log in</button>
                to cast your vote. One vote per account.
            </p>
        @elseif($poll['has_voted'] && $poll['is_open'])
            <p class="poll-hint poll-hint-voted">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Vote recorded. Results unlock when the poll closes.
            </p>
        @endif

        @if($showForm)</form>@endif

        <p class="poll-participation">
            <i class="fas fa-users" aria-hidden="true"></i>
            {{ number_format($poll['total_votes']) }} {{ \Illuminate\Support\Str::plural('vote', $poll['total_votes']) }} cast
            @unless($poll['reveal_results'])
                <span class="poll-participation-lock" title="Results are hidden until the poll closes">
                    <i class="fas fa-lock" aria-hidden="true"></i> Results hidden
                </span>
            @endunless
        </p>

        @if($poll['reveal_results'] && ! $poll['is_open'])
            <div class="poll-comments">
                <div class="poll-comments-head">
                    <h3 class="poll-comments-title">Discussion</h3>
                    <span class="poll-comments-count">{{ $poll['comment_count'] }}</span>
                </div>

                @auth
                    @if($poll['has_voted'])
                        <form method="POST" action="{{ route('poll.comments.store', $poll['id']) }}" class="poll-comment-form">
                            @csrf
                            <label for="poll-comment-body" class="poll-comment-label">Add your comment</label>
                            <textarea id="poll-comment-body" name="body" rows="3" maxlength="2000" required
                                      placeholder="Share why you picked this option...">{{ old('body') }}</textarea>
                            @error('body')<p class="poll-error">{{ $message }}</p>@enderror
                            <button type="submit" class="poll-comment-submit">Post comment</button>
                        </form>
                    @endif
                @else
                    <p class="poll-hint">Log in to join the discussion.</p>
                @endauth

                @if(! empty($poll['approved_comments']))
                    <ul class="poll-comment-list">
                        @foreach($poll['approved_comments'] as $comment)
                            <li class="poll-comment">
                                <div class="poll-comment-author">{{ $comment['author'] }}</div>
                                <p class="poll-comment-body">{{ $comment['body'] }}</p>
                                <time class="poll-comment-time">{{ $comment['created_at'] }}</time>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="poll-comments-empty">No comments yet.</p>
                @endif
            </div>
        @endif
    </div>
</section>
