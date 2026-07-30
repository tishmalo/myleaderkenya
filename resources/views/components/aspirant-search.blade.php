@props([
    'name' => 'candidate_id',
    'searchUrl',
    'label' => 'Search for an existing aspirant',
    'placeholder' => 'Type an aspirant name...',
    'selectedId' => null,
    'selectedCandidate' => null,
    'locked' => false,
])

@php($searchInputId = 'aspirant-search-' . md5($name . $searchUrl . uniqid('', true)))

<div class="aspirant-search" data-aspirant-search data-search-url="{{ $searchUrl }}" @if($locked) data-aspirant-search-locked @endif>
    <label class="aspirant-field-label" for="{{ $searchInputId }}">{{ $label }}</label>
    <div class="aspirant-search-control" @if($locked) hidden @endif>
        <i class="fas fa-search" aria-hidden="true"></i>
        <input id="{{ $searchInputId }}"
               type="search"
               autocomplete="off"
               placeholder="{{ $placeholder }}"
               aria-autocomplete="list"
               aria-expanded="false"
               data-aspirant-search-input>
        <span class="aspirant-search-spinner" data-aspirant-search-spinner aria-hidden="true"></span>
    </div>
    <input type="hidden" name="{{ $name }}" value="{{ $selectedCandidate['id'] ?? $selectedId }}" data-aspirant-search-value>
    <div class="aspirant-search-results" role="listbox" data-aspirant-search-results hidden></div>
    <div class="aspirant-search-selection" data-aspirant-search-selection @if(!$selectedCandidate) hidden @endif>
        <div class="aspirant-search-avatar" data-aspirant-search-avatar>
            @if($selectedCandidate)
                @if($selectedCandidate['image_url'])
                    <img src="{{ $selectedCandidate['image_url'] }}" alt="">
                @else
                    {{ mb_strtoupper(mb_substr(trim($selectedCandidate['name']), 0, 1)) }}
                @endif
            @endif
        </div>
        <div class="aspirant-search-selected-copy">
            <strong data-aspirant-search-name>{{ $selectedCandidate['name'] ?? '' }}</strong>
            <span data-aspirant-search-meta>{{ $selectedCandidate ? collect([$selectedCandidate['position'], $selectedCandidate['party'], $selectedCandidate['jurisdiction']])->filter()->implode(' • ') : '' }}</span>
            <small><i class="fas fa-lock"></i> Existing profile details are protected and cannot be edited here.</small>
        </div>
        @unless($locked)
            <button type="button" data-aspirant-search-clear aria-label="Choose a different aspirant">
                Change
            </button>
        @endunless
    </div>
    <p class="aspirant-search-help" data-aspirant-search-help>
        {{ $locked ? 'This aspirant is preselected for your access request.' : 'Select a match to request access, or continue below to create a new profile.' }}
    </p>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-aspirant-search]').forEach(function (root) {
        if (root.hasAttribute('data-aspirant-search-locked')) return;

        var input = root.querySelector('[data-aspirant-search-input]');
        var value = root.querySelector('[data-aspirant-search-value]');
        var results = root.querySelector('[data-aspirant-search-results]');
        var selection = root.querySelector('[data-aspirant-search-selection]');
        var spinner = root.querySelector('[data-aspirant-search-spinner]');
        var clear = root.querySelector('[data-aspirant-search-clear]');
        var timer = null;
        var request = null;
        var activeIndex = -1;

        function closeResults() {
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function setMessage(message) {
            results.replaceChildren();
            var element = document.createElement('div');
            element.className = 'aspirant-search-message';
            element.textContent = message;
            results.appendChild(element);
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function choose(item) {
            value.value = String(item.id);
            root.querySelector('[data-aspirant-search-name]').textContent = item.name;
            root.querySelector('[data-aspirant-search-meta]').textContent =
                [item.position, item.party, item.jurisdiction].filter(Boolean).join(' â€¢ ');

            var avatar = root.querySelector('[data-aspirant-search-avatar]');
            avatar.replaceChildren();
            if (item.image_url) {
                var image = document.createElement('img');
                image.src = item.image_url;
                image.alt = '';
                avatar.appendChild(image);
            } else {
                avatar.textContent = (item.name || '?').trim().charAt(0).toUpperCase();
            }

            selection.hidden = false;
            input.closest('.aspirant-search-control').hidden = true;
            closeResults();
            root.dispatchEvent(new CustomEvent('aspirant:selected', { bubbles: true, detail: item }));
        }

        function render(items) {
            results.replaceChildren();
            if (!items.length) {
                setMessage('No matching public aspirant found. Continue below to create a new profile.');
                return;
            }

            items.forEach(function (item, index) {
                var option = document.createElement('button');
                option.type = 'button';
                option.className = 'aspirant-search-option';
                option.setAttribute('role', 'option');
                option.dataset.index = String(index);

                var avatar = document.createElement('span');
                avatar.className = 'aspirant-search-option-avatar';
                if (item.image_url) {
                    var image = document.createElement('img');
                    image.src = item.image_url;
                    image.alt = '';
                    avatar.appendChild(image);
                } else {
                    avatar.textContent = (item.name || '?').trim().charAt(0).toUpperCase();
                }

                var copy = document.createElement('span');
                copy.className = 'aspirant-search-option-copy';
                var title = document.createElement('strong');
                title.textContent = item.name + (item.nickname ? ' (' + item.nickname + ')' : '');
                var meta = document.createElement('small');
                meta.textContent = [item.position, item.party, item.jurisdiction].filter(Boolean).join(' â€¢ ');
                copy.append(title, meta);
                option.append(avatar, copy);
                option.addEventListener('click', function () { choose(item); });
                results.appendChild(option);
            });
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function search() {
            var query = input.value.trim();
            if (query.length < 2) {
                closeResults();
                return;
            }
            if (request) request.abort();
            request = new AbortController();
            spinner.classList.add('is-active');
            setMessage('Searching...');

            fetch(root.dataset.searchUrl + '?q=' + encodeURIComponent(query), {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                signal: request.signal
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Search failed');
                    return response.json();
                })
                .then(function (data) { render(data.results || []); })
                .catch(function (error) {
                    if (error.name !== 'AbortError') setMessage('Search is unavailable. Please try again.');
                })
                .finally(function () { spinner.classList.remove('is-active'); });
        }

        input.addEventListener('input', function () {
            value.value = '';
            clearTimeout(timer);
            timer = setTimeout(search, 300);
        });
        input.addEventListener('keydown', function (event) {
            var options = Array.from(results.querySelectorAll('[role="option"]'));
            if (!options.length) return;
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = event.key === 'ArrowDown'
                    ? Math.min(activeIndex + 1, options.length - 1)
                    : Math.max(activeIndex - 1, 0);
                options.forEach(function (option, index) {
                    option.classList.toggle('is-active', index === activeIndex);
                });
                options[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                options[activeIndex].click();
            } else if (event.key === 'Escape') {
                closeResults();
            }
        });
        clear.addEventListener('click', function () {
            value.value = '';
            input.value = '';
            selection.hidden = true;
            input.closest('.aspirant-search-control').hidden = false;
            input.focus();
            root.dispatchEvent(new CustomEvent('aspirant:cleared', { bubbles: true }));
        });
        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) closeResults();
        });
    });
});
</script>
@endpush
@endonce
