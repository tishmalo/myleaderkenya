<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>@yield('title', 'My Leader Kenya - Niko Kadi, Kenya Election Campaign Software Tools, Aspirants')</title>
    <meta name="description" content="@yield('meta_description', 'Find 2027 Kenya Aspirants, Niko Kadi Voters, Campaign Software Tools for Elections Candidates Databases')">
    <meta name="keywords" content="Kenya elections, 2027 Kenya elections, Niko Kadi, Tuko Kadi, Kenya aspirants, campaign software, election tools, voter registration, Kenya politics, election candidates database">
    <meta name="author" content="My Leader Kenya">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags for Social Media -->
    <meta property="og:title" content="@yield('title', 'My Leader Kenya - Niko Kadi, Kenya Election Campaign Software Tools, Aspirants')">
    <meta property="og:description" content="@yield('meta_description', 'Find 2027 Kenya Aspirants, Niko Kadi Voters, Campaign Software Tools for Elections Candidates Databases')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/myleader.png'))">
    <meta property="og:site_name" content="My Leader Kenya">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'My Leader Kenya - Niko Kadi, Kenya Election Campaign Software Tools, Aspirants')">
    <meta name="twitter:description" content="@yield('meta_description', 'Find 2027 Kenya Aspirants, Niko Kadi Voters, Campaign Software Tools for Elections Candidates Databases')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/myleader.png'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/myleader.ico') }}" type="image/x-icon">

    <!-- Optional: Apple Touch Icon -->
    <link rel="apple-touch-icon" href="{{ asset('images/mlkfav.png') }}">
    <link rel="preload" as="video" href="{{ asset('images/kenya-flag-loader.webm') }}" type="video/webm" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('images/mlkfav.png') }}" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('images/ml1.jpg') }}" fetchpriority="high">

    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://app.telvoip.io/web-chat.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<style>
  .frontend-submit-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 9999px;
    animation: frontend-submit-spin .65s linear infinite;
  }
  [data-submit-loading="true"] {
    cursor: wait !important;
    opacity: .84;
  }
  @keyframes frontend-submit-spin { to { transform: rotate(360deg); } }
  .site-boot-loader {
    position: fixed;
    inset: 0;
    z-index: 100000;
    display: grid;
    place-items: center;
    background: #000;
    transition: opacity .28s ease, visibility .28s ease;
  }
  .site-boot-loader.is-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
  }
  .site-boot-flag-video,
  .site-boot-flag-img {
    display: block;
    width: min(560px, 78vw);
    height: auto;
    object-fit: contain;
  }
  @media (max-width: 640px) {
    .site-boot-flag-video,
  .site-boot-flag-img { width: min(380px, 82vw); }
  }
</style>
</head>
<body class="bg-zinc-950 text-white antialiased">
    <div class="site-boot-loader" id="siteBootLoader" aria-hidden="true">
        <video class="site-boot-flag-video" autoplay muted loop playsinline preload="auto" poster="{{ asset('images/kenya-flag-loader-poster.jpg') }}" aria-hidden="true">
            <source src="{{ asset('images/kenya-flag-loader.webm') }}" type="video/webm">
            <source src="{{ asset('images/kenya-flag-loader.mp4') }}" type="video/mp4">
            <img class="site-boot-flag-img" src="{{ asset('images/kenya-flag-loader-poster.jpg') }}" alt="" decoding="async">
        </video>
    </div>
    @yield('content')
    
 
<style>
  /* ===== TELVOIP BUTTON â€” RIGHT SIDE ===== */
  .floating-button {
    position: fixed !important;
    bottom: 30px !important;
    right: 24px !important;
    left: auto !important;
    z-index: 9999 !important;
    background-color: #7c3aed !important;
    border: none !important;
    border-radius: 50% !important;
    width: 56px !important;
    height: 56px !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }
 
  .material-icons-telvoip {
    color: white !important;
    font-family: 'Material Icons' !important;
    font-size: 28px !important;
  }
 
  #chat-container {
    display: none !important;
    position: fixed !important;
    bottom: 100px !important;
    right: 24px !important;
    left: auto !important;
    z-index: 9998 !important;
    width: 370px !important;
    height: 520px !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25) !important;
    background: #fff !important;
  }
 
  #chat-container.show {
    display: block !important;
  }
 
  #chat-iframe {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
  }
 
  .close-button {
    position: absolute !important;
    top: 8px !important;
    right: 10px !important;
    background: red !important;
    color: white !important;
    border: none !important;
    border-radius: 50% !important;
    width: 28px !important;
    height: 28px !important;
    font-size: 14px !important;
    cursor: pointer !important;
    z-index: 10000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }
 
  /* ===== WHATSAPP BUTTON â€” LEFT SIDE ===== */
  .whatsapp-button {
    position: fixed !important;
    bottom: 30px !important;
    left: 24px !important;
    right: auto !important;
    z-index: 9999 !important;
    background-color: #25D366 !important;
    border: none !important;
    border-radius: 50% !important;
    width: 56px !important;
    height: 56px !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
  }
 
  .whatsapp-button svg {
    width: 32px !important;
    height: 32px !important;
    fill: white !important;
  }
</style>
 
<!-- TELVOIP BUTTON â€” RIGHT -->
<button class="floating-button" aria-label="Open chat support" onclick="toggleChat()">
<span class="badge-telvoip"></span>
<span class="material-icons-telvoip">forum</span>
</button>
 
<!-- TELVOIP CHAT CONTAINER -->
<div id="chat-container">
<iframe
    id="chat-iframe"
    data-src="https://app.telvoip.io/web-chat?t=4e94c913-1775-4530-aeba-dbf4787af75a"
    title="Web Chat Widget"
    frameborder="0"
    width="100%"
    height="100%">
</iframe>
<button class="close-button" onclick="toggleChat()">âœ–</button>
</div>
 
<!-- WHATSAPP BUTTON â€” LEFT -->
<a class="whatsapp-button" 
   href="https://wa.me/254141102334" 
   target="_blank" 
   aria-label="Chat on WhatsApp">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
</svg>
</a>
 
<script>
  function toggleChat() {
    var chatContainer = document.getElementById("chat-container");
    var chatIframe = document.getElementById("chat-iframe");
    if (chatIframe && !chatIframe.src && chatIframe.dataset.src) {
      chatIframe.src = chatIframe.dataset.src;
    }
    chatContainer.classList.toggle("show");
  }
</script>
<script>
(function () {
  var loader = document.getElementById('siteBootLoader');
  if (!loader) return;

  function hideLoader() {
    loader.classList.add('is-hidden');
    window.setTimeout(function () {
      if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
    }, 500);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      window.requestAnimationFrame(hideLoader);
    }, { once: true });
    window.setTimeout(hideLoader, 1800);
  } else {
    window.requestAnimationFrame(hideLoader);
  }
})();
</script>
<script>
(function () {
  function preserveSubmitterValue(form, button) {
    if (!button || !button.name || button.disabled) return;
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = button.name;
    hidden.value = button.value;
    form.appendChild(hidden);
  }

  function setFrontendSubmitLoading(button, label) {
    if (!button || button.dataset.submitLoading === 'true') return;
    button.dataset.submitLoading = 'true';
    button.dataset.originalHtml = button.innerHTML;
    button.style.minWidth = `${button.offsetWidth}px`;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = `<span style="display:inline-flex;align-items:center;justify-content:center;gap:.5rem;"><span class="frontend-submit-spinner" aria-hidden="true"></span><span>${label}</span></span>`;
  }

  document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.noLoader === 'true') return;
    const submitter = event.submitter || document.activeElement;
    const button = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
      ? submitter
      : form.querySelector('button[type="submit"], input[type="submit"]');

    if (button instanceof HTMLButtonElement) {
      preserveSubmitterValue(form, button);
      setFrontendSubmitLoading(button, button.dataset.loadingLabel || button.dataset.loadingText || 'Submitting...');
    } else if (button instanceof HTMLInputElement) {
      preserveSubmitterValue(form, button);
      button.dataset.originalValue = button.value;
      button.value = button.dataset.loadingLabel || button.dataset.loadingText || 'Submitting...';
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
    }
  }, true);

  window.setFrontendSubmitLoading = setFrontendSubmitLoading;
})();
</script>
@stack('scripts')
</body>
</html>
