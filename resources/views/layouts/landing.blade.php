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

    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://app.telvoip.io/web-chat.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="bg-zinc-950 text-white antialiased">
    @yield('content')
    
 
<style>
  /* ===== TELVOIP BUTTON — RIGHT SIDE ===== */
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
 
  /* ===== WHATSAPP BUTTON — LEFT SIDE ===== */
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
 
<!-- TELVOIP BUTTON — RIGHT -->
<button class="floating-button" aria-label="Open chat support" onclick="toggleChat()">
<span class="badge-telvoip"></span>
<span class="material-icons-telvoip">forum</span>
</button>
 
<!-- TELVOIP CHAT CONTAINER -->
<div id="chat-container">
<iframe
    id="chat-iframe"
    src="https://app.telvoip.io/web-chat?t=4e94c913-1775-4530-aeba-dbf4787af75a"
    title="Web Chat Widget"
    frameborder="0"
    width="100%"
    height="100%">
</iframe>
<button class="close-button" onclick="toggleChat()">✖</button>
</div>
 
<!-- WHATSAPP BUTTON — LEFT -->
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
    chatContainer.classList.toggle("show");
  }
</script>
@stack('scripts')
</body>
</html>