<div class="ct-request-modal" id="{{ $modalId }}" aria-hidden="true">
    <div class="ct-request-backdrop" data-feature-request-close></div>
    <div class="ct-request-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
        <button type="button" class="ct-request-close" data-feature-request-close aria-label="Close request form"><i class="fas fa-times"></i></button>
        <div class="ct-request-kicker">Campaign Tool Request</div>
        <h2 id="{{ $modalId }}-title">Request Feature</h2>
        <p>Tell us what you need for <strong>{{ $tool->title }}</strong>.</p>

        <form method="POST" action="{{ route('campaign-tools.requests.store', $tool) }}" class="ct-request-form">
            @csrf
            <input type="hidden" name="feature_request_tool_id" value="{{ $tool->id }}">
            <label>Name
                <input type="text" name="requester_name" value="{{ old('requester_name') }}" required maxlength="255" placeholder="Your name">
            </label>
            @error('requester_name')<div class="ct-request-error">{{ $message }}</div>@enderror

            <div class="ct-request-fields">
                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" placeholder="name@example.com">
                </label>
                <label>Phone
                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="50" placeholder="+254...">
                </label>
            </div>
            @error('email')<div class="ct-request-error">{{ $message }}</div>@enderror
            @error('phone')<div class="ct-request-error">{{ $message }}</div>@enderror

            <label>Requested Feature
                <input type="text" name="requested_feature" value="{{ old('requested_feature') }}" required maxlength="255" placeholder="What feature do you want added?">
            </label>
            @error('requested_feature')<div class="ct-request-error">{{ $message }}</div>@enderror

            <label>Use Case
                <textarea name="use_case" rows="5" maxlength="2000" placeholder="How would this help your campaign?">{{ old('use_case') }}</textarea>
            </label>
            @error('use_case')<div class="ct-request-error">{{ $message }}</div>@enderror

            <button type="submit" class="ct-request-submit"><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
    </div>
</div>
