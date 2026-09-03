<div class="ct-request-modal" id="{{ $modalId }}" aria-hidden="true">
    <div class="ct-request-backdrop" data-feature-request-close></div>
    <div class="ct-request-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
        <button type="button" class="ct-request-close" data-feature-request-close aria-label="Close request form"><i class="fas fa-times"></i></button>
        <div class="ct-request-kicker">Campaign Tool Request</div>
        <h2 id="{{ $modalId }}-title">Request Feature</h2>
        <p>Tell us what you need for <strong>{{ $tool->title }}</strong>.</p>

        @php
            $featureRequestAction = \Illuminate\Support\Facades\Route::has('campaign-tools.requests.store')
                ? route('campaign-tools.requests.store', $tool)
                : url('/campaign-tools/' . $tool->getRouteKey() . '/requests');
            $spamClient = config('spam_filter.client', []);
            $charLimits = config('spam_filter.character_limits', []);
        @endphp
        <form method="POST" action="{{ $featureRequestAction }}" class="ct-request-form"
              data-char-limits='{{ json_encode($charLimits, JSON_HEX_APOS | JSON_HEX_QUOT) }}'
              data-client-rules='{{ json_encode($spamClient, JSON_HEX_APOS | JSON_HEX_QUOT) }}'>
            @csrf
            <input type="hidden" name="feature_request_tool_id" value="{{ $tool->id }}">
            <input type="hidden" name="g-recaptcha-response" id="{{ $modalId }}-recaptcha-token">
            <input type="hidden" name="_load_time" value="">
            <label class="ct-request-hp" aria-hidden="true">
                Leave this field empty
                <input type="text" name="company_website" value="" tabindex="-1" autocomplete="off">
            </label>

            <div class="ct-request-error" data-client-error hidden></div>

            <label>Name
                <input type="text" name="requester_name" value="{{ old('requester_name') }}" required maxlength="{{ $charLimits['requester_name'] ?? 255 }}" placeholder="Your name">
            </label>
            @error('requester_name')<div class="ct-request-error">{{ $message }}</div>@enderror

            <div class="ct-request-fields">
                <label>Email
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="{{ $charLimits['email'] ?? 255 }}" placeholder="name@example.com">
                </label>
                <label>Phone
                    <input type="text" name="phone" value="{{ old('phone') }}" maxlength="{{ $charLimits['phone'] ?? 50 }}" placeholder="+254...">
                </label>
            </div>
            @error('email')<div class="ct-request-error">{{ $message }}</div>@enderror
            @error('phone')<div class="ct-request-error">{{ $message }}</div>@enderror

            <label>Requested Feature
                <input type="text" name="requested_feature" value="{{ old('requested_feature') }}" required maxlength="{{ $charLimits['requested_feature'] ?? 255 }}" placeholder="What feature do you want added?">
            </label>
            @error('requested_feature')<div class="ct-request-error">{{ $message }}</div>@enderror

            @php
                $otherCampaignTools = collect($requestCampaignTools ?? [])->where('id', '!=', $tool->id);
                $selectedCampaignToolIds = array_map('intval', old('other_campaign_tool_ids', []));
            @endphp
            @if($otherCampaignTools->isNotEmpty())
                <fieldset class="ct-request-tool-picker">
                    <legend>Other Services</legend>
                    <span class="ct-request-tool-help">Select all other campaign tools you are interested in.</span>
                    <div class="ct-request-tool-options">
                        @foreach($otherCampaignTools as $requestCampaignTool)
                            <label class="ct-request-tool-option">
                                <input
                                    type="checkbox"
                                    name="other_campaign_tool_ids[]"
                                    value="{{ $requestCampaignTool->id }}"
                                    {{ in_array((int) $requestCampaignTool->id, $selectedCampaignToolIds, true) ? 'checked' : '' }}
                                >
                                <span>{{ $requestCampaignTool->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                @error('other_campaign_tool_ids')<div class="ct-request-error">{{ $message }}</div>@enderror
                @error('other_campaign_tool_ids.*')<div class="ct-request-error">{{ $message }}</div>@enderror
            @endif

            <label>Use Case
                <textarea name="use_case" rows="5" maxlength="{{ $charLimits['use_case'] ?? 2000 }}" placeholder="How would this help your campaign?">{{ old('use_case') }}</textarea>
            </label>
            @error('use_case')<div class="ct-request-error">{{ $message }}</div>@enderror

            <button type="submit" class="ct-request-submit" data-loading-label="Submitting..."><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>

        <div class="ct-request-success" data-fake-success hidden>
            <i class="fas fa-check-circle"></i>
            <h3>Request submitted!</h3>
            <p>Thanks — the team will review your request and get back to you.</p>
        </div>
    </div>
</div>

<style>
.ct-request-hp { position:absolute !important; left:-9999px !important; top:auto !important; width:1px !important; height:1px !important; overflow:hidden !important; clip:rect(0 0 0 0) !important; margin:0 !important; padding:0 !important; border:0 !important; white-space:nowrap !important; }
.ct-request-error { color:#fca5a5; background:rgba(127,29,29,.35); border:1px solid rgba(220,38,38,.4); border-radius:.75rem; padding:.6rem .9rem; font-size:.85rem; }
.ct-request-success { text-align:center; padding:2rem 1rem; }
.ct-request-success i { font-size:2.5rem; color:#10b981; margin-bottom:.75rem; }
.ct-request-success h3 { font-size:1.15rem; color:#fff; margin-bottom:.35rem; }
.ct-request-success p { color:#a1a1aa; font-size:.9rem; }
</style>

@php
    $recaptchaSiteKey = $recaptchaSiteKey ?? '';
    $recaptchaEnabled = $recaptchaSiteKey !== '';
@endphp
@if($recaptchaEnabled)
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}&onload={{ $modalId }}_onRecaptchaLoad" async defer></script>
@endif
<script>
(function () {
    const form = document.querySelector('#{{ $modalId }} .ct-request-form');
    if (!form) return;

    const clientRules = JSON.parse(form.dataset.clientRules || '{}');
    const charLimits = JSON.parse(form.dataset.charLimits || '{}');

    const loadTime = Date.now();
    const loadTimeInput = form.querySelector('input[name="_load_time"]');
    if (loadTimeInput) loadTimeInput.value = String(loadTime);

    const errorBox = form.querySelector('[data-client-error]');
    const successBox = form.querySelector('[data-fake-success]');

    const showError = function (message) {
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.hidden = false;
        }
        return false;
    };

    const showFakeSuccess = function () {
        if (successBox) {
            form.hidden = true;
            successBox.hidden = false;
        }
    };

    const rateLimit = clientRules.rate_limit || { max: 5, window_minutes: 60 };
    const RL_KEY = 'ct_request_rate';
    const rlWindowMs = (rateLimit.window_minutes || 60) * 60000;

    const readRate = function () {
        let rec = [];
        try { rec = JSON.parse(localStorage.getItem(RL_KEY) || '[]'); } catch (e) { rec = []; }
        const now = Date.now();
        rec = rec.filter(function (t) { return now - t < rlWindowMs; });
        localStorage.setItem(RL_KEY, JSON.stringify(rec));
        return rec;
    };

    const rateLimitOk = function () {
        return readRate().length < (rateLimit.max || 5);
    };

    const consumeRateLimit = function () {
        const rec = readRate();
        rec.push(Date.now());
        localStorage.setItem(RL_KEY, JSON.stringify(rec));
    };

    const isObviousSpam = function () {
        if (!clientRules.enabled) return false;

        const values = ['requester_name', 'requested_feature', 'use_case']
            .map(function (name) {
                const input = form.querySelector('[name="' + name + '"]');
                return input ? (input.value || '') : '';
            })
            .join(' ')
            .toLowerCase()
            .trim();

        if (!values) return false;

        if (clientRules.block_html && /<\/?[a-z][^>]*>/i.test(values)) return true;
        if (clientRules.block_url && /(https?:\/\/|www\.)/i.test(values)) return true;

        const domains = clientRules.blocked_domains || [];
        for (let i = 0; i < domains.length; i++) {
            if (values.includes(String(domains[i]).toLowerCase())) return true;
        }

        const keywords = clientRules.blocked_keywords || [];
        for (let i = 0; i < keywords.length; i++) {
            if (values.includes(String(keywords[i]).toLowerCase())) return true;
        }

        return false;
    };

    const validate = function () {
        const fields = ['requester_name', 'email', 'phone', 'requested_feature', 'use_case'];
        const labels = { requester_name: 'name', email: 'email', phone: 'phone', requested_feature: 'requested feature', use_case: 'use case' };

        for (let i = 0; i < fields.length; i++) {
            const field = fields[i];
            const input = form.querySelector('[name="' + field + '"]');
            if (!input) continue;

            const value = (input.value || '').trim();
            const limit = charLimits[field];

            if (limit && value.length > limit) {
                return showError('The ' + labels[field] + ' field is limited to ' + limit + ' characters.');
            }

            if (input.required && value === '') {
                return showError('Please fill in the required fields.');
            }

            if (field === 'email' && value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return showError('Please enter a valid email address.');
            }

            if (field === 'phone' && value !== '' && !/^[0-9+() .-]+$/.test(value)) {
                return showError('Please enter a valid phone number.');
            }
        }

        return true;
    };

    const siteKey = @json($recaptchaSiteKey);
    const tokenInput = form.querySelector('input[name="g-recaptcha-response"]');

    let recaptchaReady = !siteKey;
    window['{{ $modalId }}_onRecaptchaLoad'] = function () { recaptchaReady = true; };

    form.addEventListener('submit', function (event) {
        if (!validate()) {
            event.preventDefault();
            return;
        }

        if (!rateLimitOk()) {
            event.preventDefault();
            showError('Too many submissions from this browser. Please try again later.');
            return;
        }

        consumeRateLimit();

        if (isObviousSpam()) {
            event.preventDefault();
            showFakeSuccess();
            return;
        }

        if (!siteKey) return;
        if (tokenInput && tokenInput.value) return;

        event.preventDefault();

        const execute = function () {
            if (typeof grecaptcha === 'undefined' || !recaptchaReady) {
                showError('Unable to load security check. Please refresh and try again.');
                return;
            }
            grecaptcha.execute(siteKey, { action: 'campaign_tool_request' }).then(function (token) {
                if (tokenInput) tokenInput.value = token;
                form.submit();
            });
        };

        if (typeof grecaptcha !== 'undefined' && recaptchaReady) {
            execute();
        } else {
            const onReady = function () { recaptchaReady = true; execute(); };
            if (window['{{ $modalId }}_onRecaptchaLoad']) {
                const original = window['{{ $modalId }}_onRecaptchaLoad'];
                window['{{ $modalId }}_onRecaptchaLoad'] = function () { original(); onReady(); };
            } else {
                window['{{ $modalId }}_onRecaptchaLoad'] = onReady;
            }
        }
    });
})();
</script>