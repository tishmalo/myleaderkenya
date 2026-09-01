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
        @endphp
        <form method="POST" action="{{ $featureRequestAction }}" class="ct-request-form">
            @csrf
            <input type="hidden" name="feature_request_tool_id" value="{{ $tool->id }}">
            <input type="hidden" name="g-recaptcha-response" id="{{ $modalId }}-recaptcha-token">
            <input type="hidden" name="_load_time" value="">
            <label class="ct-request-hp" aria-hidden="true">
                Leave this field empty
                <input type="text" name="company_website" value="" tabindex="-1" autocomplete="off">
            </label>
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
                <textarea name="use_case" rows="5" maxlength="2000" placeholder="How would this help your campaign?">{{ old('use_case') }}</textarea>
            </label>
            @error('use_case')<div class="ct-request-error">{{ $message }}</div>@enderror

            <button type="submit" class="ct-request-submit" data-loading-label="Submitting..."><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
    </div>
</div>

<style>
.ct-request-hp { position:absolute !important; left:-9999px !important; top:auto !important; width:1px !important; height:1px !important; overflow:hidden !important; clip:rect(0 0 0 0) !important; margin:0 !important; padding:0 !important; border:0 !important; white-space:nowrap !important; }
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

    const loadTime = Date.now();
    const loadTimeInput = form.querySelector('input[name="_load_time"]');
    if (loadTimeInput) loadTimeInput.value = String(loadTime);

    const siteKey = @json($recaptchaSiteKey);
    const tokenInput = form.querySelector('input[name="g-recaptcha-response"]');

    let recaptchaReady = !siteKey;
    window['{{ $modalId }}_onRecaptchaLoad'] = function () { recaptchaReady = true; };

    form.addEventListener('submit', function (event) {
        if (!siteKey) return;
        if (tokenInput && tokenInput.value) return;

        event.preventDefault();

        const execute = () => {
            if (typeof grecaptcha === 'undefined' || !recaptchaReady) {
                alert('Unable to load security check. Please refresh and try again.');
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
