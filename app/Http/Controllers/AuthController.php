<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            if (Auth::user()->role === 'admin') {
                return redirect()->intended('/admin');
            }
            
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'required|string|max:20',
            'address'               => 'required|string',
            'email'                 => 'required|string|email|max:255|unique:users,email',
            'password'              => 'required|string|min:4|confirmed',
            'admin_token'           => 'nullable|string',
        ], [
            'email.unique' => 'email atau akun sudah terdaftar',
        ]);

        $adminEntries = $this->getAdminEntries();
        $isAdmin = false;

        // Cek apakah password cocok dengan salah satu password admin
        $matchedEntry = null;
        foreach ($adminEntries as $entry) {
            if ($entry['password'] === $validated['password']) {
                $matchedEntry = $entry;
                break;
            }
        }

        if ($matchedEntry) {
            // Password cocok → cek token dari popup
            $submittedToken = $validated['admin_token'] ?? '';
            if ($submittedToken !== $matchedEntry['token']) {
                return back()->withErrors(['admin_token' => 'Token verifikasi tidak valid.'])->withInput();
            }
            $isAdmin = true;
        }

        $user = User::create([
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'address'  => $validated['address'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => $isAdmin ? 'admin' : 'user',
        ]);

        Auth::login($user);

        if ($isAdmin) {
            return redirect('/admin');
        }

        return redirect('/');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();
            $isNewUser = false;

            if (!$user) {
                $user = User::create([
                    'name'      => $googleUser->getName() ?? 'User',
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password'  => bcrypt(str()->random(16)),
                    'role'      => 'user',
                ]);
                $isNewUser = true;
            } else {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user, true);

            if ($isNewUser) {
                return redirect()->route('google.set-password');
            }

            if ($user->role === 'admin') {
                return redirect('/admin');
            }
            
            return redirect('/');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login dengan Google gagal. Pastikan Client ID dan Secret Google telah dikonfigurasi.');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    // ─── Ubah Password ───────────────────────────────────────────────────────

    public function showChangePassword()
    {
        return view('auth.change_password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:4|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        // Cek password baru tidak boleh sama dengan yang lama
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'tidak boleh sama seperti password sebelumnya']);
        }

        $user->update(['password' => bcrypt($request->password)]);

        return redirect('/')->with('success', 'Password berhasil diubah!');
    }

    public function showSetGooglePassword()
    {
        return view('auth.set_google_password');
    }

    public function setGooglePassword(Request $request)
    {
        $request->validate([
            'password'    => 'required|string|min:4|confirmed',
            'admin_token' => 'nullable|string',
        ]);

        $user = Auth::user();
        $adminEntries = $this->getAdminEntries();
        $isAdmin = false;

        // Cek apakah password cocok dengan salah satu password admin
        $matchedEntry = null;
        foreach ($adminEntries as $entry) {
            if ($entry['password'] === $request->password) {
                $matchedEntry = $entry;
                break;
            }
        }

        if ($matchedEntry) {
            // Password cocok → cek token dari popup
            $submittedToken = $request->admin_token ?? '';
            if ($submittedToken && $submittedToken === $matchedEntry['token']) {
                $isAdmin = true;
            } elseif ($submittedToken && $submittedToken !== $matchedEntry['token']) {
                return back()->withErrors(['admin_token' => 'Token verifikasi tidak valid.'])->withInput();
            }
            // Jika submittedToken kosong, user memilih daftar sebagai user biasa
        }

        $user->update([
            'password' => bcrypt($request->password),
            'role'     => $isAdmin ? 'admin' : $user->role,
        ]);

        if ($isAdmin) {
            return redirect('/admin')->with('success', 'Password berhasil diatur! Selamat datang, Admin.');
        }

        return redirect('/')->with('success', 'Password berhasil diatur!');
    }

    // ─── API: Cek apakah password cocok dengan password admin ────────────────

    public function checkAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $adminEntries = $this->getAdminEntries();

        foreach ($adminEntries as $entry) {
            if ($entry['password'] === $request->password) {
                return response()->json(['is_token' => true]);
            }
        }

        return response()->json(['is_token' => false]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    /**
     * Ambil data admin entries: [{password: "xxx", token: "YYY"}, ...]
     */
    private function getAdminEntries(): array
    {
        $raw = Setting::where('key', 'admin_tokens')->value('value');
        if (!$raw) {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $entry) {
            if (is_string($entry)) {
                // Format lama — tidak punya token, skip
                continue;
            }
            if (is_array($entry) && isset($entry['password'], $entry['token'])) {
                $result[] = $entry;
            }
        }
        return $result;
    }

    // ─── Lupa Password (OTP via Email) ───────────────────────────────────────

    public function showForgotPassword(Request $request)
    {
        $step = $request->session()->get('fp_step', 1);
        $email = $request->session()->get('fp_email', '');
        $otp = $request->session()->get('fp_otp', '');

        return view('auth.forgot_password', compact('step', 'email', 'otp'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar.'])->withInput();
        }

        // Generate OTP 6 digit
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Kirim email OTP
        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->name));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email. Pastikan konfigurasi SMTP sudah benar.')->withInput();
        }

        $request->session()->put('fp_step', 2);
        $request->session()->put('fp_email', $request->email);

        return redirect()->route('forgot.password')->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || (string) $user->otp_code !== (string) $request->otp) {
            $request->session()->put('fp_step', 2);
            $request->session()->put('fp_email', $request->email);
            return redirect()->route('forgot.password')->with('error', 'Kode OTP tidak valid.');
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            $request->session()->put('fp_step', 2);
            $request->session()->put('fp_email', $request->email);
            return redirect()->route('forgot.password')->with('error', 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang.');
        }

        $request->session()->put('fp_step', 3);
        $request->session()->put('fp_otp', $request->otp);

        return redirect()->route('forgot.password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || (string) $user->otp_code !== (string) $request->otp) {
            return redirect()->route('forgot.password')->with('error', 'Sesi tidak valid. Silakan ulangi proses.');
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return redirect()->route('forgot.password')->with('error', 'Kode OTP sudah kedaluwarsa. Silakan ulangi.');
        }

        $user->update([
            'password'       => bcrypt($request->password),
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        // Clear session
        $request->session()->forget(['fp_step', 'fp_email', 'fp_otp']);

        return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.');
    }
}
