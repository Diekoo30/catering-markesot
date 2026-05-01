<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Atur Password — Markesot</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('css/markesot.css') }}" rel="stylesheet">
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
</style>
</head>
<body>

<div class="card">
  <div class="card-title">Atur Password Baru</div>
  <div class="card-sub">Karena Anda baru saja mendaftar menggunakan Google, silakan atur password untuk akun Anda terlebih dahulu.</div>

  <form action="{{ route('google.set-password.post') }}" method="POST">
    @csrf
    
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
</script>

</body>
</html>
