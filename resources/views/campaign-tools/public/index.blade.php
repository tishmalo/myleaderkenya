@extends('layouts.landing')

@section('title', 'Campaign Tools - My Leader Kenya')
@section('meta_description', 'Explore campaign tools for call centers, bulk SMS, bulk WhatsApp, websites, databases, opinion polls, and aspirant profile management.')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');
:root { --kenya-red:#BB0000; --kenya-black:#111111; --kenya-white:#F5F5F0; --green-bright:#00A86B; --kenya-green:#006600; }
* { box-sizing: border-box; }
body { font-family: 'Barlow', sans-serif; background:#0a0a0a; color:var(--kenya-white); }
h1,h2,h3 { font-family:'Oswald', sans-serif; }
.flag-stripe { height:4px; background:linear-gradient(90deg, var(--kenya-green) 33%, #111 33% 66%, var(--kenya-red) 66%); }
.ct-hero { padding:78px 32px 58px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.06); background:radial-gradient(ellipse at top, rgba(187,0,0,0.16), transparent 50%), #0a0a0a; }
.ct-eyebrow { display:inline-flex; align-items:center; gap:8px; color:#ff6666; font-size:11px; font-weight:700; letter-spacing:2.4px; text-transform:uppercase; margin-bottom:18px; }
.ct-eyebrow .dot { width:7px; height:7px; border-radius:50%; background:var(--green-bright); }
.ct-hero h1 { font-size:clamp(42px,6vw,72px); line-height:1; margin-bottom:18px; }
.ct-hero h1 em { color:var(--green-bright); font-style:normal; }
.ct-hero p { max-width:720px; margin:0 auto; color:rgba(245,245,240,0.62); font-size:18px; line-height:1.7; }
.ct-grid { max-width:1280px; margin:0 auto; padding:64px 32px 90px; display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:22px; }
.ct-card { position:relative; display:flex; flex-direction:column; min-height:360px; background:#151515; border:1px solid rgba(255,255,255,0.07); border-radius:18px; overflow:hidden; text-decoration:none; transition:border-color .25s, transform .25s, box-shadow .25s; }
.ct-card:hover { border-color:rgba(0,168,107,0.35); transform:translateY(-4px); box-shadow:0 24px 60px rgba(0,0,0,0.45); }
.ct-card-img { height:190px; background:#0f0f0f; overflow:hidden; }
.ct-card-img img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
.ct-card:hover .ct-card-img img { transform:scale(1.04); }
.ct-card-no-img { height:190px; display:flex; align-items:center; justify-content:center; color:rgba(245,245,240,0.18); font-size:48px; background:linear-gradient(135deg, rgba(187,0,0,0.14), rgba(0,102,0,0.12)); }
.ct-card-body { padding:24px; display:flex; flex-direction:column; flex:1; }
.ct-card-kicker { color:var(--green-bright); font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:12px; }
.ct-card-title { color:white; font-family:'Oswald',sans-serif; font-size:24px; font-weight:700; line-height:1.15; margin-bottom:12px; }
.ct-card-excerpt { color:rgba(245,245,240,0.55); line-height:1.65; font-size:14px; }
.ct-card-actions { margin-top:auto; padding-top:22px; display:flex; flex-wrap:wrap; gap:10px; }
.ct-card-link,.ct-card-request { display:inline-flex; align-items:center; gap:8px; border:0; border-radius:8px; padding:11px 13px; font-size:12px; font-weight:800; letter-spacing:1.2px; text-transform:uppercase; text-decoration:none; cursor:pointer; }
.ct-card-link { color:#ff6666; background:transparent; padding-left:0; }
.ct-card-request { color:#fff; background:#006600; }
.ct-request-modal { position:fixed; inset:0; z-index:10020; display:none; align-items:center; justify-content:center; padding:20px; }
.ct-request-modal.is-open { display:flex; }
.ct-request-backdrop { position:absolute; inset:0; background:rgba(0,0,0,.72); }
.ct-request-dialog { position:relative; width:min(560px,100%); max-height:calc(100vh - 40px); overflow:auto; border:1px solid rgba(255,255,255,.12); border-radius:16px; background:#121212; padding:28px; box-shadow:0 24px 80px rgba(0,0,0,.55); }
.ct-request-close { position:absolute; top:14px; right:14px; border:1px solid rgba(255,255,255,.12); border-radius:999px; width:38px; height:38px; background:#0b0b0b; color:#fff; cursor:pointer; }
.ct-request-kicker { color:var(--green-bright); font-size:10px; font-weight:800; letter-spacing:2px; text-transform:uppercase; }
.ct-request-dialog h2 { margin:8px 0 8px; font-size:34px; }
.ct-request-dialog p { color:rgba(245,245,240,.62); line-height:1.55; margin:0 0 18px; }
.ct-request-form { display:grid; gap:14px; }
.ct-request-form label { display:grid; gap:7px; color:rgba(245,245,240,.68); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
.ct-request-form input,.ct-request-form textarea { width:100%; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:#0b0b0b; color:#fff; padding:12px 13px; font:inherit; }
.ct-request-fields { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.ct-request-submit { display:inline-flex; align-items:center; justify-content:center; gap:8px; width:100%; min-height:48px; border:0; border-radius:8px; padding:13px 16px; background:#006600; color:#fff; font-weight:900; text-transform:uppercase; letter-spacing:.08em; cursor:pointer; }
.ct-request-error { border:1px solid rgba(239,68,68,.3); border-radius:8px; background:rgba(239,68,68,.1); color:#fca5a5; padding:10px 12px; font-size:13px; }
body.ct-modal-open { overflow:hidden; }
.ct-empty { grid-column:1/-1; text-align:center; padding:80px 20px; color:rgba(245,245,240,0.35); }
.ct-flash { max-width:1280px; margin:24px auto 0; padding:0 32px; }
.ct-flash div { border:1px solid rgba(34,197,94,.3); border-radius:12px; background:rgba(34,197,94,.12); color:#bbf7d0; padding:14px 16px; }
.ct-pagination { max-width:1280px; margin:-50px auto 80px; padding:0 32px; display:flex; justify-content:center; }
@media (max-width:768px) { .ct-hero { padding:58px 18px 42px; } .ct-grid { padding:42px 18px 72px; } .ct-request-fields { grid-template-columns:1fr; } .ct-card-actions,.ct-card-link,.ct-card-request { width:100%; justify-content:center; } .ct-card-link { padding-left:13px; } }
</style>

<div class="flag-stripe"></div>
@include('components.frontend-nav')

<section class="ct-hero">
    <div class="ct-eyebrow"><span class="dot"></span> Campaign Infrastructure</div>
    <h1>Campaign <em>Tools</em></h1>
    <p>Digital tools for serious campaigns: communication, websites, voter data, opinion polling, and aspirant profile management.</p>
</section>

<section class="ct-grid">
    @forelse($campaignTools as $tool)
        @php($modalId = 'campaign-tool-request-' . $tool->id)
        <article class="ct-card">
            @if($tool->featured_image)
                <div class="ct-card-img"><img src="{{ Storage::url($tool->featured_image) }}" alt="{{ $tool->title }}" loading="lazy"></div>
            @else
                <div class="ct-card-no-img"><i class="fas fa-bullhorn"></i></div>
            @endif
            <div class="ct-card-body">
                <div class="ct-card-kicker">Campaign Tool</div>
                <div class="ct-card-title">{{ $tool->title }}</div>
                @if($tool->excerpt)<p class="ct-card-excerpt">{{ $tool->excerpt }}</p>@endif
                <div class="ct-card-actions">
                    <a href="{{ route('campaign-tools.show', $tool->slug) }}" class="ct-card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                    <button type="button" class="ct-card-request" data-feature-request-open="{{ $modalId }}"><i class="fas fa-lightbulb"></i> Request Feature</button>
                </div>
            </div>
        </article>
        @include('campaign-tools.public._request-modal', ['tool' => $tool, 'modalId' => $modalId])
    @empty
        <div class="ct-empty">
            <h3>No campaign tools published yet.</h3>
            <p>Published campaign tools will appear here automatically.</p>
        </div>
    @endforelse
</section>

@if($campaignTools->hasPages())
<div class="ct-pagination">{{ $campaignTools->links() }}</div>
@endif
<script>
const openFeatureRequestModal = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('ct-modal-open');
    modal.querySelector('input, textarea, button')?.focus();
};

const closeFeatureRequestModal = (modal) => {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ct-modal-open');
};

document.querySelectorAll('[data-feature-request-open]').forEach((button) => {
    button.addEventListener('click', () => openFeatureRequestModal(button.dataset.featureRequestOpen));
});

document.querySelectorAll('[data-feature-request-close]').forEach((button) => {
    button.addEventListener('click', () => closeFeatureRequestModal(button.closest('.ct-request-modal')));
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeFeatureRequestModal(document.querySelector('.ct-request-modal.is-open'));
});

const featureRequestToolId = '{{ old('feature_request_tool_id') }}';
@if($errors->any())
if (featureRequestToolId) openFeatureRequestModal(`campaign-tool-request-${featureRequestToolId}`);
@endif
</script>@endsection
