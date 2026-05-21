<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Atur Password — Markesot</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('css/markesot.css') }}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  body {
    background: var(--bg);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 2rem;
  }
  .card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    width: 100%;
    max-width: 450px;
    box-shadow: var(--shadow);
  }
  .card-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--maroon);
    margin-bottom: 0.3rem;
  }
  .card-sub {
    color: var(--text-light);
    font-size: 0.88rem;
    margin-bottom: 1.8rem;
  }
  .form-group {
    margin-bottom: 1.1rem;
  }
  .form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.3rem;
  }
  .form-control {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-family: inherit;
    font-size: 0.9rem;
    background: #fafafa;
    box-sizing: border-box;
    transition: border-color 0.2s;
  }
  .form-control:focus {
    border-color: var(--gold);
    outline: none;
    background: white;
  }
  
  .password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }
  .password-wrapper .form-control {
    padding-right: 2.5rem;
  }
  .pwd-toggle {
    position: absolute;
    right: 0.8rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    color: var(--text-light);
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
  }
  .pwd-toggle:hover {
    color: var(--maroon);
  }
  .form-control.is-invalid {
    border-color: #ef4444;
    background: #fff5f5;
  }
  .invalid-feedback {
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    display: block;
  }
  .alert {
    padding: 1rem;
    background: #fdf4ec;
    color: var(--maroon);
    border-radius: 10px;
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
    text-align: left;
  }

  /* ── Token Modal ── */
  .token-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(4px);
  }
  .token-overlay.active { display: flex; }
  .token-modal {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    width: 90%;
    max-width: 380px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: fadeIn 0.25s ease;
    text-align: center;
  }
  @keyframes fadeIn { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }
  .token-modal-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
  .token-modal-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--maroon);
    margin-bottom: 0.3rem;
  }
  .token-modal-sub {
    font-size: 0.82rem;
    color: var(--text-light);
    margin-bottom: 1.2rem;
    line-height: 1.5;
  }
  .token-modal .form-control { margin-bottom: 0.5rem; }
  .token-error {
    color: #ef4444;
    font-size: 0.8rem;
    margin-bottom: 0.8rem;
    display: none;
  }
  .token-btns {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
  }
  .token-btns button {
    flex: 1;
    padding: 0.7rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    border: none;
    font-family: inherit;
    transition: 0.2s;
  }
  .btn-token-cancel { background: #f0f0f0; color: var(--text-light); }
  .btn-token-cancel:hover { background: #e5e5e5; }
  .btn-token-confirm { background: var(--maroon); color: white; }
  .btn-token-confirm:hover { opacity: 0.9; }
</style>
</head>
<body>

<div class="card">
  <div class="card-title">Atur Password Baru</div>
  <div class="card-sub">Karena Anda baru saja mendaftar menggunakan Google, silakan atur password untuk akun Anda terlebih dahulu.</div>

  @if($errors->any())
    <div class="alert">
      <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form id="googlePasswordForm" action="{{ route('google.set-password.post') }}" method="POST">
    @csrf
    <input type="hidden" name="admin_token" id="adminTokenField" value="">
    
    <div class="form-group">
      <label for="password">Password</label>
      <div class="password-wrapper">
        <input
          type="password"
          name="password"
          id="password"
          class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
          required
          minlength="4"
        >
        <button type="button" class="pwd-toggle" onclick="togglePassword(this)" title="Tampilkan/Sembunyikan Password">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>
      </div>
      @error('password')
        <span class="invalid-feedback">{{ $message }}</span>
      @enderror
    </div>

    <div class="form-group">
      <label for="password_confirmation">Konfirmasi Password</label>
      <div class="password-wrapper">
        <input
          type="password"
          name="password_confirmation"
          id="password_confirmation"
          class="form-control"
          required
          minlength="4"
        >
        <button type="button" class="pwd-toggle" onclick="togglePassword(this)" title="Tampilkan/Sembunyikan Password">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-primary" style="width:100%;margin-top:0.5rem;">
      Simpan Password & Lanjutkan
    </button>
  </form>
</div>

<!-- ═══ Token Modal ═══ -->
<div class="token-overlay" id="tokenOverlay">
  <div class="token-modal">
    <div class="token-modal-icon" style="color:var(--maroon);"><svg style="width:40px;height:40px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg></div>
    <div class="token-modal-title">Verifikasi Admin</div>
    <div class="token-modal-sub">Password yang Anda masukkan terdeteksi sebagai <strong>Password Admin</strong>.<br>Masukkan <strong>Token Verifikasi</strong> yang terdaftar di panel admin untuk melanjutkan sebagai Admin.</div>
    <input type="text" class="form-control" id="tokenInput" placeholder="Masukkan token verifikasi..." style="text-transform:uppercase;letter-spacing:2px;font-weight:700;text-align:center;">
    <div class="token-error" id="tokenError">Token tidak valid. Silakan coba lagi.</div>
    <div class="token-btns">
      <button class="btn-token-cancel" onclick="closeTokenModal()">Lanjut Sebagai User</button>
      <button class="btn-token-confirm" onclick="submitToken()">Konfirmasi Admin</button>
    </div>
  </div>
</div>

<script>
function togglePassword(btn) {
  const wrapper = btn.closest('.password-wrapper');
  const input = wrapper.querySelector('input');
  
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
    </svg>`;
  } else {
    input.type = 'password';
    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>`;
  }
}

// ── Token modal logic ──
const googlePasswordForm = document.getElementById('googlePasswordForm');
let skipTokenCheck = false;

function setButtonLoading(btn) {
  btn.disabled = true;
  btn.dataset.originalText = btn.innerHTML;
  btn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50" style="width:20px;height:20px;animation:rotate 2s linear infinite;margin-right:8px;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" style="stroke-dasharray:1,200;stroke-dashoffset:0;animation:dash 1.5s ease-in-out infinite;stroke-linecap:round;"></circle></svg> Memproses...';
  btn.style.display = 'flex';
  btn.style.alignItems = 'center';
  btn.style.justifyContent = 'center';
}

function resetButtonLoading(btn) {
  btn.disabled = false;
  if (btn.dataset.originalText) {
    btn.innerHTML = btn.dataset.originalText;
  }
}

googlePasswordForm.addEventListener('submit', async function(e) {
  // Jika sudah diset skip (user pilih lanjut sebagai user), submit langsung
  if (skipTokenCheck) {
    skipTokenCheck = false;
    return;
  }

  // Jika token sudah dikonfirmasi via popup, submit langsung
  if (document.getElementById('adminTokenField').value) {
    return;
  }

  const pwd = document.getElementById('password').value;
  const pwdConfirm = document.getElementById('password_confirmation').value;

  // Cek apakah password cocok & konfirmasi sama
  if (pwd.length >= 4 && pwd === pwdConfirm) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    setButtonLoading(submitBtn);

    // Cek ke server apakah password = password admin
    try {
      const res = await fetch('{{ route("check.admin.password") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ password: pwd }),
      });
      const data = await res.json();
      
      if (data.is_token) {
        // Password = password admin → munculkan popup minta token
        resetButtonLoading(submitBtn);
        openTokenModal();
      } else {
        // Password biasa → submit langsung
        googlePasswordForm.submit();
      }
    } catch (err) {
      googlePasswordForm.submit();
    }
  }
});

function openTokenModal() {
  document.getElementById('tokenOverlay').classList.add('active');
  document.getElementById('tokenInput').value = '';
  document.getElementById('tokenError').style.display = 'none';
  setTimeout(() => document.getElementById('tokenInput').focus(), 100);
}

function closeTokenModal() {
  // User memilih lanjut sebagai User biasa → submit tanpa token
  document.getElementById('tokenOverlay').classList.remove('active');
  document.getElementById('adminTokenField').value = '';
  skipTokenCheck = true;
  googlePasswordForm.submit();
}

function submitToken() {
  const token = document.getElementById('tokenInput').value.trim().toUpperCase();
  if (!token) {
    document.getElementById('tokenError').textContent = 'Token tidak boleh kosong.';
    document.getElementById('tokenError').style.display = 'block';
    return;
  }
  
  // Set token dan submit form
  document.getElementById('adminTokenField').value = token;
  document.getElementById('tokenOverlay').classList.remove('active');
  const confirmBtn = document.querySelector('.btn-token-confirm');
  setButtonLoading(confirmBtn);
  googlePasswordForm.submit();
}

// Enter key di token input
document.getElementById('tokenInput').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    submitToken();
  }
});

document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    if (this.id === 'googlePasswordForm') return; // Handled specially
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
      if (submitBtn.disabled) {
        e.preventDefault();
        return;
      }
      setButtonLoading(submitBtn);
    }
  });
});
</script>
<style>
@keyframes rotate { 100% { transform: rotate(360deg); } }
@keyframes dash {
  0% { stroke-dasharray: 1, 200; stroke-dashoffset: 0; }
  50% { stroke-dasharray: 90, 200; stroke-dashoffset: -35px; }
  100% { stroke-dasharray: 90, 200; stroke-dashoffset: -124px; }
}
</style>
</body>
</html>
