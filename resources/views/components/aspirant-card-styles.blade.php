{{-- Aspirant card styling shared with the aspirants pages.
     Include wherever aspirants.public._card renders outside those pages
     (e.g. county pages) so cards look identical everywhere. --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap');
:root { --kenya-red:#BB0000; --kenya-black:#111111; --kenya-white:#F5F5F0; --green-bright:#00A86B; --kenya-green:#006600; }
body { font-family:'Barlow',sans-serif; }
h1,h2,h3,h4 { font-family:'Oswald',sans-serif; }
.asp-card {
    background: #141414;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    transition: border-color 0.3s, transform 0.3s, box-shadow 0.3s;
    display: flex; flex-direction: column;
}
.asp-card:hover {
    border-color: rgba(0,168,107,0.35);
    transform: translateY(-4px);
    box-shadow: 0 24px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(0,168,107,0.15);
}

/* Photo area */
.asp-card-photo {
    position: relative; height: auto; aspect-ratio: 4 / 3; overflow: hidden;
}
.asp-card-photo img {
    width: 100%; height: 100%;
    object-fit: cover; object-position: top center;
    transition: transform 0.5s ease;
}
.asp-card:hover .asp-card-photo img { transform: scale(1.05); }

.asp-card-photo-placeholder {
    width: 100%; height: 100%;
    background: linear-gradient(135deg, rgba(187,0,0,0.2) 0%, rgba(0,102,0,0.2) 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 8px;
}
.asp-card-photo-placeholder .initials {
    font-family: 'Oswald', sans-serif;
    font-size: 52px; font-weight: 700;
    color: rgba(255,255,255,0.12);
    line-height: 1;
}

/* Gradient overlay on photo */
.asp-card-photo-overlay {
    position: absolute; inset: 0;
    pointer-events: none;
    background: linear-gradient(to top, rgba(20,20,20,.32) 0%, transparent 30%);
}

/* Position badge on photo */
.asp-card-position-badge {
    position: absolute; top: 14px; left: 14px;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 11px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    color: rgba(245,245,240,0.7);
}

/* County flag tag on photo */
.asp-card-county-tag {
    position: absolute; bottom: 14px; right: 14px;
    display: flex; align-items: center; gap: 6px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0,168,107,0.25);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px; color: var(--green-bright); font-weight: 600;
}

/* Card body */
.asp-card-body {
    padding: 16px;
    flex: 1; display: flex; flex-direction: column;
}
.asp-card-name {
    font-family: 'Oswald', sans-serif;
    font-size: 20px; font-weight: 700;
    line-height: 1.1; margin-bottom: 4px;
    color: var(--kenya-white);
}
.asp-card-party {
    display: flex; align-items: center; gap: 7px;
    min-height: 20px; margin: 5px 0 7px;
    color: var(--green-bright); font-size: 12px; font-weight: 700;
}
.asp-card-party i { font-size: 10px; opacity: .8; }
.asp-card-nick {
    font-size: 13px; color: rgba(0,168,107,0.8);
    font-style: italic; margin-bottom: 10px;
}
.asp-card-location {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: rgba(245,245,240,0.35);
    margin-bottom: 12px;
}
.asp-card-location i { font-size: 10px; }

/* Bottom accent line */
.asp-card-divider {
    height: 1px;
    background: linear-gradient(90deg, rgba(0,168,107,0.2), rgba(187,0,0,0.2), transparent);
    margin-bottom: 12px;
}

.asp-card-action {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s;
    margin-top: auto;
}
.asp-card-action:hover {
    background: rgba(0,168,107,0.08);
    border-color: rgba(0,168,107,0.3);
}
.asp-card-action-text {
    font-family: 'Oswald', sans-serif;
    font-size: 13px; font-weight: 600;
    letter-spacing: 1px; text-transform: uppercase;
    color: rgba(245,245,240,0.7);
}
.asp-card-action:hover .asp-card-action-text { color: var(--green-bright); }
.asp-card-action-arrow {
    width: 28px; height: 28px;
    background: var(--kenya-red);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: white;
    transition: background 0.2s, transform 0.2s;
}
.asp-card:hover .asp-card-action-arrow {
    background: var(--green-bright);
    transform: translateX(2px);
}
</style>
