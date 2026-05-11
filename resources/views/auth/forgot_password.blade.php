<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password — Markesot</title>
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
    line-height: 1.5;
  }
  .form-group { margin-bottom: 1.1rem; }
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
    border-radius: 10px;
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
    text-align: left;
  }
  .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }
  .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
  .password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }
  .password-wrapper .form-control { padding-right: 2.5rem; }
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
  .pwd-toggle:hover { color: var(--maroon); }

  /* Steps indicator */
  .steps-row {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1.8rem;
  }
  .step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 700;
    background: #f0f0f0; color: #aaa;
    transition: all 0.3s;
    flex-shrink: 0;
  }
  .step-dot.active { background: var(--maroon); color: white; }
  .step-dot.done { background: #10b981; color: white; }
  .step-line {
    flex: 1; height: 2px;
    background: #e5e5e5;
    transition: background 0.3s;
  }
  .step-line.done { background: #10b981; }

  /* OTP input */
  .otp-wrap {
    display: flex; gap: 0.5rem; justify-content: center;
    margin: 1.5rem 0;
  }
  .otp-input {
    width: 48px; height: 56px;
    text-align: center; font-size: 1.5rem; font-weight: 800;
    border: 2px solid #ddd; border-radius: 12px;
    font-family: monospace; color: var(--maroon);
    background: #fafafa;
    transition: border-color 0.2s;
  }
  .otp-input:focus { border-color: var(--maroon); outline: none; background: white; }

  .resend-link {
    font-size: 0.82rem; color: var(--text-light);
    text-align: center; margin-top: 1rem;
  }
  .resend-link a {
    color: var(--maroon); font-weight: 600; text-decoration: underline; cursor: pointer;
  }
  .resend-link a.disabled { color: #ccc; pointer-events: none; text-decoration: none; }

  .back-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.85rem;
  }
  .back-link:hover { color: var(--maroon); }

  /* Step visibility */
  .step-panel { display: none; }
  .step-panel.active { display: block; animation: fadeIn 0.3s ease; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
</style>
</head>
<body>

<div class="card">
  <div class="card-title">Lupa Password</div>

  @if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-error">
      <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Steps indicator -->
  <div class="steps-row">
    <div class="step-dot {{ $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' }}">{{ $step > 1 ? '✓' : '1' }}</div>
    <div class="step-line {{ $step > 1 ? 'done' : '' }}"></div>
    <div class="step-dot {{ $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' }}">{{ $step > 2 ? '✓' : '2' }}</div>
    <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
    <div class="step-dot {{ $step >= 3 ? 'active' : '' }}">3</div>
  </div>

  <!-- STEP 1: Email Input -->
  <div class="step-panel {{ $step == 1 ? 'active' : '' }}">
    <div class="card-sub">Masukkan alamat email yang terdaftar. Kami akan mengirimkan kode OTP untuk reset password.</div>
    <form action="{{ route('forgot.password.send') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" required value="{{ old('email', $email ?? '') }}" placeholder="contoh@email.com">
        @error('email')
          <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>
      <button type="submit" class="btn-primary" style="width:100%;margin-top:0.5rem;">Kirim Kode OTP</button>
    </form>
  </div>

  <!-- STEP 2: OTP Verification -->
  <div class="step-panel {{ $step == 2 ? 'active' : '' }}">
    <div class="card-sub">Kode OTP telah dikirim ke <strong>{{ $email ?? '' }}</strong>. Masukkan 6 digit kode untuk melanjutkan.</div>
    <form action="{{ route('forgot.password.verify') }}" method="POST" id="otpForm">
      @csrf
      <input type="hidden" name="email" value="{{ $email ?? '' }}">
      <input type="hidden" name="otp" id="otpHidden">

      <div class="otp-wrap">
        @for($i = 0; $i < 6; $i++)
          <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="{{ $i }}" autocomplete="off">
        @endfor
      </div>

      <button type="submit" class="btn-primary" style="width:100%" id="verifyBtn" disabled>Verifikasi OTP</button>
    </form>

    <div class="resend-link">
      Tidak menerima kode? 
      <form action="{{ route('forgot.password.send') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? '' }}">
        <a href="#" onclick="this.closest('form').submit(); return false;" id="resendLink">Kirim ulang</a>
      </form>
    </div>
  </div>

  <!-- STEP 3: New Password -->
  <div class="step-panel {{ $step == 3 ? 'active' : '' }}">
    <div class="card-sub">Buat password baru untuk akun Anda.</div>
    <form action="{{ route('forgot.password.reset') }}" method="POST">
      @csrf
      <input type="hidden" name="email" value="{{ $email ?? '' }}">
      <input type="hidden" name="otp" value="{{ $otp ?? '' }}">

      <div class="form-group">
        <label for="password">Password Baru</label>
        <div class="password-wrapper">
          <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required minlength="4">
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
        <label for="password_confirmation">Konfirmasi Password Baru</label>
        <div class="password-wrapper">
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="4">
          <button type="button" class="pwd-toggle" onclick="togglePassword(this)" title="Tampilkan/Sembunyikan Password">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;margin-top:0.5rem;">Simpan Password Baru</button>
    </form>
  </div>

  <a href="{{ route('login') }}" class="back-link">← Kembali ke halaman Login</a>
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

// OTP input auto-focus & combine logic
const otpInputs = document.querySelectorAll('.otp-input');
const otpHidden = document.getElementById('otpHidden');
const verifyBtn = document.getElementById('verifyBtn');

function updateOtpValue() {
  let val = '';
  otpInputs.forEach(inp => val += inp.value);
  if (otpHidden) otpHidden.value = val;
  if (verifyBtn) verifyBtn.disabled = val.length < 6;
}

otpInputs.forEach((inp, idx) => {
  inp.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value && idx < otpInputs.length - 1) {
      otpInputs[idx + 1].focus();
    }
    updateOtpValue();
  });

  inp.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && !this.value && idx > 0) {
      otpInputs[idx - 1].focus();
      otpInputs[idx - 1].value = '';
      updateOtpValue();
    }
  });

  inp.addEventListener('paste', function(e) {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
    pasted.split('').forEach((ch, i) => {
      if (otpInputs[i]) otpInputs[i].value = ch;
    });
    const focusIdx = Math.min(pasted.length, otpInputs.length - 1);
    otpInputs[focusIdx].focus();
    updateOtpValue();
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
<script>
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
      if (submitBtn.disabled && submitBtn.id !== 'verifyBtn') { // exception for disabled logic in verify btn
        e.preventDefault();
        return;
      }
      submitBtn.disabled = true;
      submitBtn.dataset.originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50" style="width:20px;height:20px;animation:rotate 2s linear infinite;margin-right:8px;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" style="stroke-dasharray:1,200;stroke-dashoffset:0;animation:dash 1.5s ease-in-out infinite;stroke-linecap:round;"></circle></svg> Memproses...';
      submitBtn.style.display = 'flex';
      submitBtn.style.alignItems = 'center';
      submitBtn.style.justifyContent = 'center';
    }
  });
});
</script>
</body>
</html>
