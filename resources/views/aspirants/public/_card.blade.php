<div class="asp-card">
    <div class="asp-card-photo">
        @if($candidate->profile_picture)
            <img src="{{ Storage::url($candidate->profile_picture) }}"
                 alt="{{ $candidate->name }}" loading="lazy">
        @else
            <div class="asp-card-photo-placeholder">
                <span class="initials">
                    {{ strtoupper(substr($candidate->name, 0, 1)) }}{{ strtoupper(substr(strrchr($candidate->name, ' ') ?: '', 1, 1)) }}
                </span>
            </div>
        @endif
        <div class="asp-card-photo-overlay"></div>

        @if($candidate->position)
            <div class="asp-card-position-badge">{{ $candidate->position->name }}</div>
        @endif

        @if($candidate->county)
            <div class="asp-card-county-tag">
                <i class="fas fa-map-marker-alt" style="font-size:9px"></i>
                {{ $candidate->county }}
            </div>
        @endif
    </div>

    <div class="asp-card-body">
        <div class="asp-card-name">{{ $candidate->name }}</div>

        @if($candidate->nick_name)
            <div class="asp-card-nick">"{{ $candidate->nick_name }}"</div>
        @endif

        @if($candidate->constituency)
            <div class="asp-card-location">
                <i class="fas fa-circle" style="font-size:4px;color:var(--green-bright)"></i>
                {{ $candidate->constituency }}
                @if($candidate->ward)
                    &nbsp;&middot;&nbsp; {{ $candidate->ward }}
                @endif
            </div>
        @endif

        <div class="asp-card-divider"></div>

        <a href="{{ route('aspirants.show', $candidate) }}" class="asp-card-action">
            <span class="asp-card-action-text">View Profile</span>
            <span class="asp-card-action-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</div>
