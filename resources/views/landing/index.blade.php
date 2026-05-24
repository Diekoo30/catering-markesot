<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Markesot — Kantin Universitas Jember</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700;800&family=Bebas+Neue&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<link href="{{ asset('css/markesot-icons.css') }}?v={{ filemtime(public_path('css/markesot-icons.css')) }}" rel="stylesheet">
<script>
    // Anti-inspect basic prevention
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.onkeydown = function (e) {
        if(e.keyCode == 123) { return false; }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 73) { return false; }
        if(e.ctrlKey && e.shiftKey && e.keyCode == 74) { return false; }
        if(e.ctrlKey && e.keyCode == 85) { return false; }
    }
</script>
<style>
/* ── Cart Badge ── */
.fab-order { position: relative; }
.cart-badge {
    position: absolute; top: -8px; right: -8px;
    background: var(--gold, #d4af37); color: var(--maroon, #800000);
    border-radius: 50%; min-width: 24px; height: 24px;
    display: none; align-items: center; justify-content: center;
    font-size: 0.85rem; font-weight: 800;
    border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.cart-badge.pop { animation: badgePop 0.4s ease; }
@keyframes badgePop {
  0% { transform: scale(1); }
  50% { transform: scale(1.45); }
  100% { transform: scale(1); }
}
.top-actions {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 100;
    display: flex;
    align-items: center;
    gap: 0.65rem;
}
.top-icon-btn {
    width: 44px;
    min-width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.92);
    box-shadow: var(--shadow-sm);
    color: var(--maroon);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    backdrop-filter: blur(5px);
    position: relative;
    font-family: inherit;
    font-weight: 800;
}
.top-order-btn {
    width: auto;
    min-width: 0;
    padding: 0 0.9rem;
    border-radius: 999px;
    gap: 0.45rem;
    font-size: 0.84rem;
    white-space: nowrap;
}
.top-icon-btn svg {
    width: 18px;
    height: 18px;
}
.top-order-label {
    line-height: 1;
}
.user-avatar-initials {
    font-size: 0.78rem;
    letter-spacing: 0.02em;
}
.footer-contact {
    background: transparent !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 0 !important;
    border-radius: 0 !important;
}
.footer-contact a {
    flex-shrink: 0;
}
@media(max-width:480px){
    .top-actions {
        top: 0.75rem;
        right: 0.75rem;
        gap: 0.5rem;
    }
    .top-icon-btn {
        width: 40px;
        height: 40px;
    }
    .top-order-btn {
        width: auto;
        height: 40px;
        padding: 0 0.75rem;
        font-size: 0.78rem;
    }
    .footer {
        padding-bottom: 7rem !important;
    }
    .footer-contact {
        width: auto !important;
        max-width: 100% !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.55rem !important;
        padding-top: 0.75rem !important;
        text-align: center !important;
    }
    .footer-contact span {
        font-size: 0.85rem !important;
        line-height: 1.35 !important;
    }
    .footer-contact a {
        width: 40px !important;
        min-width: 40px !important;
        height: 40px !important;
        flex: 0 0 40px !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
}

/* ── Category Block ── */
.menu-category-block { margin-bottom: 2.5rem; }
.menu-cat-header {
    display: flex; align-items: baseline; gap: 0.7rem;
    padding: 0 max(1.5rem, 5vw); margin-bottom: 1rem;
}
.menu-cat-title {
    font-family: var(--f-head, 'Cormorant Garamond', serif);
    font-size: 1.6rem; font-weight: 700; color: var(--maroon, #800000); margin: 0;
}
.menu-cat-count {
    font-size: 0.82rem; color: #999; font-weight: 500;
}

/* ── Horizontal Scroll ── */
.menu-scroll-wrap {
    overflow: visible; padding: 0 max(1.5rem, 5vw);
}
.menu-scroll-track {
    display: flex; gap: 1rem; overflow-x: auto;
    scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;
    padding-bottom: 1rem; scroll-padding-left: 1rem;
}
.menu-scroll-track::-webkit-scrollbar { height: 4px; }
.menu-scroll-track::-webkit-scrollbar-track { background: transparent; }
.menu-scroll-track::-webkit-scrollbar-thumb { background: #ddd; border-radius: 2px; }

/* ── Menu Card ── */
.m-card {
    flex: 0 0 220px; scroll-snap-align: start;
    background: #fff; border-radius: 16px; overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    display: flex; flex-direction: column;
    transition: transform 0.25s, box-shadow 0.25s;
}
.m-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.10);
}
.m-card-img {
    height: 150px; position: relative;
    background-size: cover; background-position: center;
    display: flex; align-items: center; justify-content: center;
}
.m-card-emoji { font-size: 4rem; }
.m-card-badge {
    position: absolute; top: 8px; left: 8px;
    background: var(--maroon, #800000); color: var(--gold, #d4af37);
    font-size: 0.65rem; font-weight: 800; letter-spacing: 0.03em;
    padding: 0.25rem 0.55rem; border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
}
.m-card-body {
    padding: 0.9rem; display: flex; flex-direction: column; flex-grow: 1;
}
.m-card-name {
    font-weight: 700; font-size: 0.95rem; color: #222;
    margin-bottom: 0.3rem; text-transform: capitalize;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.m-card-desc {
    font-size: 0.75rem; color: #888; line-height: 1.4;
    flex-grow: 1; margin-bottom: 0.7rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.m-card-bottom {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem;
}
.m-card-price {
    font-weight: 800; color: var(--maroon, #800000); font-size: 0.9rem; white-space: nowrap;
}

/* ── Stepper Controls ── */
.landing-stepper { flex: 1; display: flex; justify-content: flex-end; }
.add-btn-init {
    width: 100%; height: 32px; border-radius: 20px;
    background: var(--maroon, #800000); color: #fff;
    border: none; font-size: 0.8rem; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(128,0,0,0.25);
    touch-action: manipulation;
}
.add-btn-init:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(128,0,0,0.35); }
.add-btn-init:active { transform: scale(0.92); }
.stepper-controls {
    display: flex; align-items: center; justify-content: space-between; width: 100%;
    border: 1.5px solid var(--maroon, #800000); border-radius: 20px;
    overflow: hidden; height: 32px;
}
.st-minus, .st-plus {
    width: 26px; height: 100%; border: none; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; transition: background 0.15s;
    touch-action: manipulation;
}
.st-minus { background: #fff; color: var(--maroon, #800000); }
.st-minus:hover { background: #fef2f2; }
.st-plus { background: var(--maroon, #800000); color: #fff; }
.st-plus:hover { background: #6b0000; }
.qty-display {
    min-width: 22px; text-align: center;
    font-weight: 800; font-size: 0.9rem; color: #333;
}

/* ── Prevent double-tap zoom on all interactive elements ── */
button, a, input, select, textarea, .m-card, .btn-detail, .fab, .pay-opt, .qty-btn {
    touch-action: manipulation;
}

/* ── Responsive ── */
@media(min-width: 768px) {
    .m-card { flex: 0 0 240px; }
    .m-card-img { height: 170px; }
}
@media(min-width: 1024px) {
    .m-card { flex: 0 0 260px; }
    .m-card-img { height: 180px; }
}
</style>
</head>
<body>
@auth
  @php
    $userInitials = collect(explode(' ', trim(auth()->user()->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'U';
  @endphp
  <div class="top-actions">
    <!-- Pesanan Saya -->
    <a href="{{ route('my.orders') }}" class="top-icon-btn top-order-btn" title="Pesanan Saya" aria-label="Pesanan Saya">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
      <span class="top-order-label">Pesanan Saya</span>
      @if($activeOrderCount > 0)
        <span style="background:#ef4444;color:white;border-radius:50%;min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;border:2px solid white;position:absolute;top:-6px;right:-6px;">{{ $activeOrderCount }}</span>
      @endif
    </a>

    <!-- User Dropdown -->
    <div style="position:relative;" id="userMenuWrap">
      <button onclick="document.getElementById('userDropdown').classList.toggle('show-dropdown')" class="top-icon-btn" title="Akun Saya" aria-label="Akun Saya">
        <span class="user-avatar-initials">{{ $userInitials }}</span>
      </button>
      
      <div id="userDropdown" class="user-dropdown">
        <div style="padding: 0.8rem 1rem; border-bottom: 1px solid #f0f0f0; background: #fafafa;">
          <div style="font-weight: 700; color: var(--text);">{{ auth()->user()->name }}</div>
          <div style="font-size: 0.75rem; color: var(--text-light); word-break: break-all;">{{ auth()->user()->email }}</div>
        </div>
        <a href="{{ route('change.password') }}" style="display:flex;align-items:center;gap:6px;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg> Ubah Password</a>
        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
          @csrf
          <button type="submit" style="display:flex;align-items:center;gap:6px;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> Logout</button>
        </form>
      </div>
    </div>
  </div>

  <style>
    .user-dropdown { position: absolute; top: 115%; right: 0; background: white; border-radius: 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); width: 200px; overflow: hidden; display: none; flex-direction: column; opacity: 0; transform: translateY(-10px); transition: all 0.2s; }
    .user-dropdown.show-dropdown { display: flex; opacity: 1; transform: translateY(0); }
    .user-dropdown a, .user-dropdown button { padding: 0.8rem 1rem; color: var(--text); font-weight: 600; font-size: 0.85rem; text-decoration: none; text-align: left; background: transparent; border: none; width: 100%; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-family: inherit; margin: 0; display: block; box-sizing: border-box; }
    .user-dropdown a:hover, .user-dropdown button:hover { background: #fdf2f2; color: var(--maroon); }
    .user-dropdown form:last-child button { border-bottom: none; }
  </style>
  <script>
    document.addEventListener('click', function(e) {
      const wrap = document.getElementById('userMenuWrap');
      const drop = document.getElementById('userDropdown');
      if (wrap && drop && !wrap.contains(e.target)) {
        drop.classList.remove('show-dropdown');
      }
    });
  </script>
@else
  <div style="position:fixed;top:1rem;right:1rem;z-index:100;">
    <a href="{{ route('login') }}" style="background:white;color:var(--maroon);padding:0.6rem 1.2rem;border-radius:20px;box-shadow:var(--shadow-sm);text-decoration:none;font-weight:700;font-size:0.85rem;display:inline-block;">Masuk / Daftar</a>
  </div>
@endauth

<!-- ═══════════════════════════════════════
     LANDING PAGE
═══════════════════════════════════════ -->

<!-- HERO -->
<section class="hero">
  <div class="hero-circles"><span></span><span></span><span></span></div>
  <div class="hero-content">
    <div class="hero-eyebrow">Kantin Universitas Jember</div>
    <h1 class="hero-title">MARKESOT</h1>
    <p class="hero-subtitle">Authentic Campus Kitchen</p>
    <p class="hero-desc">"Perut kosong hati meronta, cium aroma langsung tergoda.<br>Markesot bukan nama biasa — rasa masakan bikin jatuh cinta!"</p>
    <div class="hero-btns">
      <button class="btn-gold" onclick="openOrder()" style="display:flex;align-items:center;gap:8px;justify-content:center;"><svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg> Pesan Sekarang</button>
      <button class="btn-dss-hero" onclick="openDSS()" style="display:flex;align-items:center;gap:8px;justify-content:center;">
        <div class="dss-sparkle" style="display:flex;align-items:center;justify-content:center;background:white;color:var(--gold);border-radius:50%;width:24px;height:24px;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"></path><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"></path></svg></div>
        Bingung mau makan apa?
      </button>
    </div>
  </div>
  <div class="hero-scroll">Scroll</div>
</section>

<!-- STATS -->
<div class="stats-strip">
  <div class="stat-item"><div class="stat-num">Murah</div><div class="stat-label">Harga Mahasiswa</div></div>
  <div class="stat-item"><div class="stat-num">Kenyang</div><div class="stat-label">Porsi Pas di Perut</div></div>
  <div class="stat-item"><div class="stat-num">Fresh</div><div class="stat-label">Dimasak Tiap Hari</div></div>
  <div class="stat-item"><div class="stat-num">Halal</div><div class="stat-label">Bahan Terjamin</div></div>
</div>

<!-- MENU SECTION -->
<section class="food-section" id="menu">
  <div class="section-head sr">
    <div class="section-chip">✦ Jelajahi Rasa ✦</div>
    <h2 class="section-title"><em>Menu</em> Kami</h2>
    <div class="section-rule"></div>
    <p class="section-sub">Pilihan hidangan istimewa dan minuman segar, disiapkan dengan bahan terbaik untuk kepuasan Anda.</p>
  </div>

  @php
    $grouped = $menus->groupBy('category_name');
    $catIndex = 0;
  @endphp

  @foreach($grouped as $catName => $catMenus)
    @if($catIndex === 1)
      <div id="menuMoreWrap" style="position:relative; max-height:220px; overflow:hidden; transition: max-height 0.6s ease;">
    @endif
    <div class="menu-category-block sr">
      <div class="menu-cat-header">
        <h3 class="menu-cat-title">{{ $catName }}</h3>
        <span class="menu-cat-count">{{ $catMenus->count() }} menu</span>
      </div>
      <div class="menu-scroll-wrap">
        <div class="menu-scroll-track">
          @foreach($catMenus->values() as $i => $menu)
          <div class="m-card" id="mcard-{{ $menu['id'] }}">
            <div class="m-card-img" style="
              @if($menu['image'])
                background-image: url('{{ $menu['image'] }}');
              @else
                background: linear-gradient(135deg,#f5e4be,#e8c97a);
              @endif
            ">
              @if(!$menu['image'])
                <span class="m-card-emoji">{{ $menu['emoji'] }}</span>
              @endif
              @if(!empty($menu['is_best_seller']))
                <span class="m-card-badge" style="display:flex;align-items:center;gap:4px;"><svg style="width:12px;height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg> Best Seller</span>
              @endif
            </div>
            <div class="m-card-body">
              <div class="m-card-name">{{ $menu['name'] }}</div>
              <div class="m-card-desc">{{ Str::limit($menu['desc'], 55) }}</div>
              <div class="m-card-bottom">
                <div class="m-card-price">Rp.{{ number_format($menu['price'], 2, ',', '.') }}</div>
                <div style="display:flex; gap:6px; align-items:center; width:100%; margin-top:0.4rem;">
                  <button class="btn-detail" onclick="openMenuDetail({{ $menu['id'] }})" style="background:transparent; border:1px solid var(--maroon); color:var(--maroon); flex:1; width:100%; height:32px; display:flex; align-items:center; justify-content:center; border-radius:20px; font-size:0.8rem; font-weight:700; cursor:pointer; transition:0.2s;" onmouseover="this.style.background='var(--maroon)'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='var(--maroon)';">Detail</button>
                  <div class="landing-stepper" id="stepper-{{ $menu['id'] }}">
                    <button class="add-btn-init" onclick="addLandingItem({{ $menu['id'] }})">+ Tambah</button>
                    <div class="stepper-controls" style="display:none;">
                      <button class="st-minus" onclick="chgQty({{ $menu['id'] }}, -1)">−</button>
                      <span class="qty-display" id="qty-disp-{{ $menu['id'] }}">1</span>
                      <button class="st-plus" onclick="chgQty({{ $menu['id'] }}, 1)">+</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @php $catIndex++; @endphp
  @endforeach

  @if($catIndex > 1)
    </div>
    <!-- Gradient overlay + Show More button -->
    <div id="menuFadeOverlay" style="position:relative; margin-top:-160px; padding-top:130px; background:linear-gradient(to bottom, rgba(254,252,247,0) 0%, rgba(254,252,247,0.9) 60%, rgba(254,252,247,1) 100%); text-align:center; padding-bottom:1rem; z-index:2; pointer-events:none;">
      <button id="menuToggleBtn" onclick="toggleMenuMore()" style="background:var(--maroon);color:#fff;border:none;padding:0.7rem 2rem;border-radius:25px;font-weight:700;font-size:0.9rem;cursor:pointer;box-shadow:0 4px 12px rgba(128,0,0,0.2);transition:all 0.2s; pointer-events:auto;">
        Lihat Semua Menu ▼
      </button>
    </div>
  @endif
</section>

<script>
function toggleMenuMore() {
  const wrap = document.getElementById('menuMoreWrap');
  const btn = document.getElementById('menuToggleBtn');
  const overlay = document.getElementById('menuFadeOverlay');
  if (!wrap) return;
  
  if (wrap.style.maxHeight === '220px' || wrap.style.maxHeight === '') {
    wrap.style.maxHeight = wrap.scrollHeight + 'px';
    btn.innerHTML = 'Sembunyikan Menu ▲';
    overlay.style.background = 'transparent';
    overlay.style.marginTop = '0';
    overlay.style.paddingTop = '1rem';
  } else {
    wrap.style.maxHeight = '220px';
    btn.innerHTML = 'Lihat Semua Menu ▼';
    overlay.style.background = 'linear-gradient(to bottom, rgba(254,252,247,0) 0%, rgba(254,252,247,0.9) 60%, rgba(254,252,247,1) 100%)';
    overlay.style.marginTop = '-160px';
    overlay.style.paddingTop = '130px';
    
    // Optional: scroll back up to the button
    setTimeout(() => {
        btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 400);
  }
}
</script>

<!-- DSS STRIP ← tombol khusus DSS -->
<section class="dss-strip sr">
  <div class="dss-strip-inner">
    <div class="dss-strip-left">
      <div class="dss-strip-tag">Rekomendasi Cerdas</div>
      <div class="dss-strip-title">Bingung mau<br>makan <em>apa?</em></div>
      <div class="dss-strip-sub">Jawab beberapa pertanyaan singkat dan sistem kami akan merekomendasikan menu yang paling cocok untukmu hari ini — cepat, mudah, dan akurat!</div>
    </div>
    <div class="dss-strip-right">
      <button class="btn-dss-main" onclick="openDSS()">
        <span class="brain" style="display:inline-flex;align-items:center;justify-content:center;"><svg style="width:28px;height:28px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"></path><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"></path></svg></span>
        Rekomendasiin Menu<br>
        <span style="font-size:.8rem;font-weight:500;opacity:.8">untuk saya!</span>
      </button>
    </div>
  </div>
</section>


@if($newsList->count() > 0)
<!-- BERITA & KEGIATAN -->
<section class="food-section" id="berita" style="padding-bottom: 3rem; overflow: hidden;">
  <div class="section-head sr">
    <div class="section-chip">✦ Berita & Kegiatan ✦</div>
    <h2 class="section-title">Mitra & <em>Kegiatan Kami</em></h2>
    <div class="section-rule"></div>
    <p class="section-sub">Dokumentasi kegiatan, acara, dan kerjasama kami bersama berbagai mitra.</p>
  </div>
  <div class="news-scroll-wrapper">
    <div class="news-scroll-container" id="newsScrollContainer">
      <div class="news-scroll-track" id="newsScrollTrack">
      @foreach($newsList as $news)
      @php
          $newsImages = is_array($news->image) ? $news->image : [];
          $firstImg = !empty($newsImages) ? asset('storage/' . $newsImages[0]) : '';
          $allImagesJson = !empty($newsImages) ? json_encode(array_map(fn($img) => asset('storage/' . $img), $newsImages)) : '[]';
      @endphp
      <div class="news-card">
        @if($firstImg)
        <div class="news-card-img" style="background-image:url('{{ $firstImg }}');"></div>
        @else
        <div class="news-card-img" style="background:linear-gradient(135deg, var(--maroon), #b22222); display:flex; align-items:center; justify-content:center; color:white; font-size:3rem;">
          <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="white"><path d="M160-200q-33 0-56.5-23.5T80-280v-400q0-33 23.5-56.5T160-760h640q33 0 56.5 23.5T880-680v400q0 33-23.5 56.5T800-200H160Zm0-80h640v-400H160v400Zm40-40h560L620-520 510-380l-70-86-240 306Zm-40 40v-400 400Z"/></svg>
        </div>
        @endif
        <div class="news-card-body">
          <div class="news-card-title">{{ $news->title }}</div>
          <div class="news-card-desc">{{ $news->description }}</div>
          <button class="news-detail-btn" 
                  data-id="{{ $news->id }}" 
                  data-title="{{ $news->title }}" 
                  data-desc="{{ $news->description }}" 
                  data-images="{{ $allImagesJson }}" 
                  data-date="{{ $news->created_at->translatedFormat('d F Y') }}" 
                  onclick="handleNewsDetail(this)">Detail</button>
        </div>
      </div>
      @endforeach
      </div>
    </div>
  </div>
</section>

<style>
.news-scroll-wrapper {
  position: relative;
  width: 100%;
}

.news-scroll-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  padding: 0 0 2rem 0;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.news-scroll-container::-webkit-scrollbar { display: none; }

.news-scroll-track {
  display: flex;
  gap: 1.5rem;
  width: max-content;
  padding: 0 1.5rem;
}

.news-card {
  flex: 0 0 300px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.03);
  display: flex;
  flex-direction: column;
}
.news-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}
.news-card-img {
  width: 100%;
  height: 190px;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}
.news-card-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.news-card-title {
  font-weight: 800;
  font-size: 1.1rem;
  color: #1a1a1a;
  margin-bottom: 0.6rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}
.news-card-desc {
  font-size: 0.9rem;
  color: #666;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}
.news-detail-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-top: 1rem;
  padding: 0.5rem 1.4rem;
  background: transparent;
  border: 1.5px solid var(--maroon);
  color: var(--maroon);
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
  touch-action: manipulation;
}
.news-detail-btn:hover {
  background: var(--maroon);
  color: white;
}

/* News Detail Modal */
.news-modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  z-index: 99999;
  justify-content: center;
  align-items: center;
  padding: 1.5rem;
  backdrop-filter: blur(4px);
}
.news-modal-overlay.active {
  display: flex;
  animation: fadeIn 0.25s ease;
}
.news-modal {
  background: white;
  border-radius: 20px;
  max-width: 560px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  animation: slideUp 0.3s ease;
}
.news-modal-img-container {
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  background: #111;
  border-radius: 20px 20px 0 0;
  width: 100%;
  scrollbar-width: none;
}
.news-modal-img-container::-webkit-scrollbar { display: none; }

.news-modal-img {
  flex: 0 0 100%;
  scroll-snap-align: start;
  width: 100%;
  height: 320px;
  object-fit: contain;
  background: #111;
}
.news-modal-img.placeholder {
  background: linear-gradient(135deg, var(--maroon), #b22222);
}
.news-modal-close {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  backdrop-filter: blur(4px);
}
.news-modal-close:hover { background: rgba(0,0,0,0.75); }
.news-modal-body {
  padding: 1.8rem;
}
.news-modal-date {
  font-size: 0.78rem;
  color: var(--maroon);
  font-weight: 600;
  margin-bottom: 0.5rem;
}
.news-modal-title {
  font-size: 1.3rem;
  font-weight: 800;
  color: #1a1a1a;
  line-height: 1.4;
  margin-bottom: 1rem;
}
.news-modal-desc {
  font-size: 0.92rem;
  color: #555;
  line-height: 1.8;
  white-space: pre-line;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(30px); }
  to { opacity:1; transform:translateY(0); }
}
</style>

<!-- News Detail Modal -->
<div class="news-modal-overlay" id="newsModal" onclick="if(event.target===this)closeNewsDetail()">
  <div class="news-modal">
    <div style="position:relative;">
      <div class="news-modal-img-container" id="newsModalImgContainer">
        <!-- Images injected here -->
      </div>
      <button class="news-modal-close" onclick="closeNewsDetail()">
        <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="white"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
      </button>
      <div id="newsModalDots" style="position:absolute; bottom:12px; width:100%; display:flex; justify-content:center; gap:6px; pointer-events:none;"></div>
    </div>
    <div class="news-modal-body">
      <div class="news-modal-date" id="newsModalDate"></div>
      <div class="news-modal-title" id="newsModalTitle"></div>
      <div class="news-modal-desc" id="newsModalDesc"></div>
    </div>
  </div>
</div>

<script>
(function() {
  const container = document.getElementById('newsScrollContainer');
  const track = document.getElementById('newsScrollTrack');
  if (!container || !track) return;

  const itemCount = {{ $newsList->count() }};
  let isPaused = false;
  let resumeTimer = null;

  if (itemCount >= 5) {
    // Duplicate cards for seamless infinite loop
    const cards = track.innerHTML;
    track.innerHTML = cards + cards;

    // Wait for DOM to fully render before measuring
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        const halfWidth = track.scrollWidth / 2;

        // Auto scroll
        setInterval(() => {
          if (!isPaused) {
            container.scrollLeft += 1;
            if (container.scrollLeft >= halfWidth) {
              container.scrollLeft = 0;
            }
          }
        }, 20);

        // Also reset on manual scroll so it's always infinite
        let isResetting = false;
        container.addEventListener('scroll', () => {
          if (isResetting) return;
          if (container.scrollLeft >= halfWidth) {
            isResetting = true;
            container.scrollLeft = 0;
            isResetting = false;
          }
        });
      });
    });

    // Pause on hover
    container.addEventListener('mouseenter', () => { isPaused = true; clearTimeout(resumeTimer); });
    container.addEventListener('mouseleave', () => { resumeTimer = setTimeout(() => isPaused = false, 500); });

    // Pause on touch (mobile swipe)
    container.addEventListener('touchstart', () => { isPaused = true; clearTimeout(resumeTimer); }, {passive: true});
    container.addEventListener('touchend', () => { resumeTimer = setTimeout(() => isPaused = false, 3000); });

    // Pause on card click, resume after 3s
    track.querySelectorAll('.news-card').forEach(card => {
      card.addEventListener('click', () => {
        isPaused = true;
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(() => isPaused = false, 3000);
      });
    });
  }
})();

function handleNewsDetail(btn) {
  const id = btn.getAttribute('data-id');
  const title = btn.getAttribute('data-title');
  const desc = btn.getAttribute('data-desc');
  let images = [];
  try { images = JSON.parse(btn.getAttribute('data-images') || '[]'); } catch(e){}
  const date = btn.getAttribute('data-date');
  openNewsDetail(id, title, desc, images, date);
}

function openNewsDetail(id, title, desc, images, date) {
  document.getElementById('newsModalTitle').textContent = title;
  document.getElementById('newsModalDesc').textContent = desc;
  document.getElementById('newsModalDate').textContent = date;

  const container = document.getElementById('newsModalImgContainer');
  const dotsContainer = document.getElementById('newsModalDots');
  container.innerHTML = '';
  dotsContainer.innerHTML = '';

  if (images && images.length > 0) {
    images.forEach((img, idx) => {
      const imgEl = document.createElement('img');
      imgEl.className = 'news-modal-img';
      imgEl.src = img;
      container.appendChild(imgEl);

      if (images.length > 1) {
        const dot = document.createElement('div');
        dot.style.cssText = `width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.4);transition:0.3s;`;
        if(idx === 0) dot.style.background = 'white';
        dotsContainer.appendChild(dot);
      }
    });

    if (images.length > 1) {
      // Add prev/next buttons
      const btnPrev = document.createElement('button');
      btnPrev.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="white"><path d="M560-240 320-480l240-240 56 56-184 184 184 184-56 56Z"/></svg>';
      btnPrev.style.cssText = 'position:absolute;left:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(4px);z-index:10;';
      btnPrev.onclick = () => container.scrollBy({ left: -container.clientWidth, behavior: 'smooth' });
      
      const btnNext = document.createElement('button');
      btnNext.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="white"><path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/></svg>';
      btnNext.style.cssText = 'position:absolute;right:10px;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;backdrop-filter:blur(4px);z-index:10;';
      btnNext.onclick = () => container.scrollBy({ left: container.clientWidth, behavior: 'smooth' });

      document.getElementById('newsModalImgContainer').parentElement.appendChild(btnPrev);
      document.getElementById('newsModalImgContainer').parentElement.appendChild(btnNext);

      container.onscroll = () => {
        const idx = Math.round(container.scrollLeft / container.clientWidth);
        Array.from(dotsContainer.children).forEach((d, i) => {
          d.style.background = i === idx ? 'white' : 'rgba(255,255,255,0.4)';
        });
      };
    }
  } else {
    const div = document.createElement('div');
    div.className = 'news-modal-img placeholder';
    div.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:white;"><svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="white"><path d="M160-200q-33 0-56.5-23.5T80-280v-400q0-33 23.5-56.5T160-760h640q33 0 56.5 23.5T880-680v400q0 33-23.5 56.5T800-200H160Zm0-80h640v-400H160v400Zm40-40h560L620-520 510-380l-70-86-240 306Zm-40 40v-400 400Z"/></svg></div>';
    container.appendChild(div);
  }

  document.getElementById('newsModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeNewsDetail() {
  document.getElementById('newsModal').classList.remove('active');
  document.body.style.overflow = '';
}
</script>
@endif

<!-- CTA BOTTOM -->
<section class="cta-section">
  <h2 class="cta-title">Sudah Lapar? <em>Yuk Order!</em></h2>
  <p class="cta-sub">Jangan biarkan perut kosong mengganggu harimu. Satu klik, pesanan langsung kami proses!</p>
  <div class="cta-btns">
    <button class="btn-gold" style="font-size:1.05rem;padding:1.1rem 2.8rem;display:flex;align-items:center;gap:8px;justify-content:center;" onclick="openOrder()"><svg style="width:20px;height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Keranjang</button>
    <button class="btn-dss-hero" onclick="openDSS()" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.25);display:flex;align-items:center;gap:8px;justify-content:center;">
      <div class="dss-sparkle" style="display:flex;align-items:center;justify-content:center;background:white;color:var(--gold);border-radius:50%;width:24px;height:24px;"><svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"></path><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"></path></svg></div> Masih bingung? Coba rekomendasi
    </button>
  </div>
  <div style="margin-top:1.5rem;display:flex;justify-content:center;position:relative;">
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $companyPhone) }}" target="_blank" style="background:#25D366;color:white;padding:0.75rem 1.8rem;border-radius:50px;text-decoration:none;display:inline-flex;align-items:center;gap:0.6rem;font-family:'Outfit',sans-serif;font-weight:700;font-size:0.95rem;box-shadow:0 6px 20px rgba(37,211,102,0.4);transition:all 0.3s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 28px rgba(37,211,102,0.55)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 6px 20px rgba(37,211,102,0.4)'">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
      {{ $companyPhone }}
    </a>
  </div>
</section>

<footer class="footer" style="padding: 3rem max(1.5rem, 5vw); background:#fff; border-top:1px solid #eee; display:flex; flex-direction:column; gap:2rem;">
  <div style="width:100%; display:flex; flex-wrap:wrap; justify-content:space-between; gap:2.5rem;">
    <div style="flex: 1; min-width: 250px; display:flex; flex-direction:column; align-items:center; text-align:center;">
      <div class="footer-brand" style="font-size:2rem; font-weight:800; color:var(--maroon); font-family:var(--f-head,'Cormorant Garamond',serif);">{{ $companyName }}</div>
      <div class="footer-info" style="color:#666; font-size:0.95rem; margin-top:0.5rem; line-height:1.5;">Kantin Universitas Jember</div>
      @if($companyAddress)
      <div style="color:#666; font-size:0.85rem; margin-top:0.6rem; line-height:1.5;">{{ $companyAddress }}</div>
      @endif
      <div style="color:#999; font-size:0.8rem; margin-top:1rem;">© 2026 {{ $companyName }}. All rights reserved.</div>
    </div>
    <div style="flex: 1; min-width: 260px; display:flex; flex-direction:column; align-items:center; text-align:center;">
      <div style="font-weight:700; margin-bottom:1rem; color:var(--maroon); font-size:1.1rem; border-bottom: 2px solid var(--gold); display: inline-block; padding-bottom: 0.2rem;">Jam Operasional</div>
      <div style="display:grid; grid-template-columns:80px auto; gap:0.5rem 1rem; font-size:0.9rem; color:#555; text-align:left;">
        @foreach($opHours as $hari => $jam)
        <div>{{ $hari }}</div><div style="font-weight:700;">{{ $jam }}</div>
        @endforeach
      </div>
    </div>
  </div>
  @php
    $footerWhatsappNumber = preg_replace('/[^0-9]/', '', $companyPhone);
    if (str_starts_with($footerWhatsappNumber, '0')) {
        $footerWhatsappNumber = '62' . substr($footerWhatsappNumber, 1);
    } elseif (str_starts_with($footerWhatsappNumber, '8')) {
        $footerWhatsappNumber = '62' . $footerWhatsappNumber;
    }
  @endphp
  <div class="footer-contact" style="width:100%; padding-top:0.75rem; border-top:1px solid #eee; display:flex; flex-wrap:wrap; align-items:center; justify-content:center; gap:0.55rem; font-size:0.88rem; font-weight:600; color:#444;">
    <span>Ada pertanyaan? Hubungi Kami:</span>
    <a href="https://wa.me/{{ $footerWhatsappNumber }}" target="_blank" aria-label="Hubungi WhatsApp" title="Hubungi WhatsApp" style="background:#25D366; color:white; width:40px; height:40px; border-radius:50%; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(37,211,102,0.3); transition:0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
      <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
    </a>
  </div>
</footer>

<!-- FABs -->
<div class="fabs">
  <button class="fab fab-dss" onclick="openDSS()">
    <div class="fab-dot"></div> Bingung mau makan apa?
  </button>
  <button class="fab fab-order" id="fabCart" onclick="openOrder()">
    <div class="fab-dot"></div> <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg> Keranjang
  </button>
</div>

<!-- ═══════════════════════════════════════
     ORDER MODAL
═══════════════════════════════════════ -->
<div class="overlay" id="orderOverlay" onclick="handleOverlayClick(event,'orderOverlay')">
  <div class="sheet" id="orderSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-header">
      <div class="sheet-title" id="orderTitle">Pilih Menu</div>
      <button class="sheet-close" onclick="closeOrder()">✕</button>
    </div>
    <div class="steps-row" id="orderStepsRow"></div>
    <div class="sheet-body" id="orderBody"></div>
  </div>
</div>


<!-- ═══════════════════════════════════════
     DSS MODAL
═══════════════════════════════════════ -->
<div class="overlay" id="dssOverlay" onclick="handleOverlayClick(event,'dssOverlay')">
  <div class="sheet" id="dssSheet">
    <div class="sheet-handle"></div>
    <div class="sheet-header">
      <div class="sheet-title" id="dssTitle">Rekomendasi Menu</div>
      <button class="sheet-close" onclick="closeDSS()">✕</button>
    </div>
    <div class="dss-progress-wrap" id="dssProgressWrap">
      <div class="dss-prog-header">
        <div class="dss-prog-label" id="dssPLabel">Yuk Mulai!</div>
        <div class="dss-prog-step" id="dssPStep">0 dari 3</div>
      </div>
      <div class="dss-prog-track"><div class="dss-prog-fill" id="dssPFill" style="width:0%"></div></div>
      <div class="dss-prog-dots" id="dssPDots"></div>
    </div>
    <div class="sheet-body" id="dssBody"></div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     MENU DETAIL MODAL
═══════════════════════════════════════ -->
<div class="overlay" id="menuDetailModal" onclick="if(event.target===this) document.getElementById('menuDetailModal').classList.remove('open')">
  <div class="sheet" style="max-width: 450px; max-height: 90vh; border-radius: 20px; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
    <div style="position: relative; width: 100%; height: 260px; background: #f5f5f5;" id="mdImgWrap">
      <img id="mdImg" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
      <div id="mdEmoji" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--maroon); background: linear-gradient(135deg,#f5e4be,#e8c97a); display: none;"><svg style="width:80px;height:80px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg></div>
      <button onclick="document.getElementById('menuDetailModal').classList.remove('open')" style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.5); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; backdrop-filter: blur(4px); transition: 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.5)'">✕</button>
    </div>
    <div style="padding: 1.5rem; flex: 1; overflow-y: auto;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem; gap: 1rem;">
        <div>
          <span id="mdCat" style="background: var(--gold-light); color: var(--maroon); font-size: 0.75rem; font-weight: 800; padding: 0.3rem 0.8rem; border-radius: 20px; text-transform: uppercase;">Kategori</span>
          <h2 id="mdName" style="margin: 0.6rem 0 0 0; color: var(--text); font-size: 1.6rem; font-weight: 800;">Nama Menu</h2>
        </div>
        <div id="mdPrice" style="font-weight: 800; color: var(--maroon); font-size: 1.25rem; background: #fdf2f2; padding: 0.5rem 1rem; border-radius: 12px; white-space: nowrap;">Rp 0</div>
      </div>
      <p id="mdDesc" style="color: var(--text-light); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">Deskripsi...</p>
      
      <div style="background: #fafafa; border: 1px solid #eee; border-radius: 14px; padding: 1.2rem;">
        <h4 style="margin: 0 0 1rem 0; font-size: 0.95rem; color: var(--text); display: flex; align-items: center; gap: 6px;"><svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg> Karakteristik Rasa</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;"><span style="color:var(--text-light);display:flex;align-items:center;"><svg style="width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg> Rasa</span> <span id="mdRasa" style="font-weight:800; color:var(--text);">5/5</span></div>
          <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;"><span style="color:var(--text-light);display:flex;align-items:center;"><svg style="width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg> Harga</span> <span id="mdHarga" style="font-weight:800; color:var(--text);">5/5</span></div>
          <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;"><span style="color:var(--text-light);display:flex;align-items:center;"><svg style="width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg> Sehat</span> <span id="mdSehat" style="font-weight:800; color:var(--text);">5/5</span></div>
          <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;"><span style="color:var(--text-light);display:flex;align-items:center;"><svg style="width:14px;height:14px;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg> Kenyang</span> <span id="mdKenyang" style="font-weight:800; color:var(--text);">5/5</span></div>
        </div>
        <div id="mdTags" style="margin-top: 1.2rem; display: flex; flex-wrap: wrap; gap: 0.5rem;"></div>
      </div>
      
      <div style="margin-top: 1.5rem;" id="mdActionWrap">
         <!-- Button generated by JS -->
      </div>
    </div>
  </div>
</div>


<script>
  window.APP_MENUS = {!! json_encode($menus->values()) !!};
  window.DP_PCT = {{ $dpPercentage }};
  window.MIN_ORDER_LEAD_TIME = {{ $minOrderLeadTime }};
  window.IS_LOGGED_IN = {{ auth()->check() ? 'true' : 'false' }};
  window.USER_NAME = {!! json_encode(auth()->user()->name ?? '') !!};
  window.USER_PHONE = {!! json_encode(auth()->user()->phone ?? '') !!};
  window.USER_ADDRESS = {!! json_encode(auth()->user()->address ?? '') !!};
  window.USER_EMAIL = {!! json_encode(auth()->user()->email ?? '') !!};
  window.LOGIN_URL = "{{ route('login') }}";
  window.GOOGLE_LOGIN_URL = "{{ route('google.login') }}";
  window.OP_HOURS = {!! json_encode($opHours) !!};
</script>
<script src="{{ asset('js/markesot.js') }}?v={{ filemtime(public_path('js/markesot.js')) }}"></script>
</body>
</html>