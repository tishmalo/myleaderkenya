{{-- Everything here is pre-computed by App\Support\PollPresenter. No queries,
     no date or number formatting, no business rules. --}}
<section class="poll-section" id="poll">
    <div class="section-inner">
        <div class="section-header">
            <div class="section-label">{{ $poll['section_label'] }}</div>
            <h2 class="section-title">{{ $poll['question'] }}</h2>
            <p class="section-sub">{{ $poll['status_line'] }}</p>
        </div>

        @if(session('poll_notice'))
            <div class="poll-notice" role="status">{{ session('poll_notice') }}</div>
        @endif
        @if(session('poll_comment_notice'))
            <div class="poll-notice" role="status">{{ session('poll_comment_notice') }}</div>
        @endif

        @if($poll['can_vote'])
            <form method="POST" action="{{ $poll['vote_action'] }}" class="poll-form">
                @csrf
        @endif

        <div class="{{ $poll['grid_class'] }}">
            @foreach($poll['options'] as $option)
                @if($poll['can_vote'])
                    <label class="poll-option is-selectable">
                        <input type="radio" name="option_id" value="{{ $option['id'] }}" class="poll-radio" required>
                        <span class="poll-option-bar" style="--poll-percent: 0%"></span>
                        <span class="poll-option-inner">
                            <x-landing.poll-option-body :option="$option" />
                        </span>
                    </label>
                @else
                    <div class="poll-option {{ $option['is_chosen'] ? 'is-chosen' : '' }} {{ $option['show_tally'] ? 'has-result' : '' }}">
                        @if($option['show_tally'])
                            <div class="poll-option-bar" style="--poll-percent: {{ $option['percent'] }}%"></div>
                        @endif
                        <div class="poll-option-inner">
                            <x-landing.poll-option-body :option="$option" :plain="! $poll['can_vote']" />
                            @if($option['show_tally'])
                                <span class="poll-option-tally">
                                    <strong>{{ $option['percent'] }}%</strong>
                                    <small>{{ $option['votes_label'] }}</small>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @error('option_id')<p class="poll-error">{{ $message }}</p>@enderror

        @if($poll['can_vote'])
            <div class="poll-actions">
                <button type="submit" class="poll-submit">Cast your vote</button>
                <p class="poll-hint">One vote per account. You can change it until the poll closes.</p>
            </div>
        @elseif($poll['prompts_login'])
            <p class="poll-hint poll-hint-login">
                <button type="button" class="poll-login-link" onclick="window.openFrontendAuth ? window.openFrontendAuth('login') : null">Log in</button>
                to cast your vote. One vote per account.
            </p>
        @elseif($poll['confirms_vote'])
            <p class="poll-hint poll-hint-voted">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Vote recorded. Results unlock when the poll closes.
            </p>
        @endif

        @if($poll['can_vote'])</form>@endif

        <p class="poll-participation">
            <i class="fas fa-users" aria-hidden="true"></i>
            {{ $poll['total_votes_label'] }}
            @if($poll['results_locked'])
                <span class="poll-participation-lock" title="Results are hidden until the poll closes">
                    <i class="fas fa-lock" aria-hidden="true"></i> Results hidden
                </span>
            @endif
        </p>

        @if($poll['comment_count'] > 0 || $poll['can_comment'] || $poll['prompt_comment_login'])
            <div class="poll-comments">
                <div class="poll-comments-head">
                    <h3 class="poll-comments-title">Discussion</h3>
                    <span class="poll-comments-count">{{ $poll['comment_count'] }}</span>
                </div>

                @if($poll['can_comment'])
                    <form method="POST" action="{{ $poll['comment_action'] }}" class="poll-comment-form">
                        @csrf
                        <label for="poll-comment-body" class="poll-comment-label">Add your comment</label>
                        <textarea id="poll-comment-body" name="body" rows="3" maxlength="2000" required
                                  placeholder="Share why you picked this option...">{{ old('body') }}</textarea>
                        @error('body')<p class="poll-error">{{ $message }}</p>@enderror
                        <button type="submit" class="poll-comment-submit">Post comment</button>
                    </form>
                @elseif($poll['prompt_comment_login'])
                    <p class="poll-hint">Log in to join the discussion.</p>
                @endif

                @forelse($poll['comments'] as $comment)
                    <ul class="poll-comment-list">
                        <li class="poll-comment">
                            <div class="poll-comment-author">{{ $comment['author'] }}</div>
                            <p class="poll-comment-body">{{ $comment['body'] }}</p>
                            <time class="poll-comment-time">{{ $comment['created_ago'] }}</time>
                        </li>
                    </ul>
                @empty
                    <p class="poll-comments-empty">No comments yet.</p>
                @endforelse
            </div>
        @endif
    </div>
</section>
