<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Pengaturan Pesanan ──
            [
                'key'         => 'dp_percentage',
                'value'       => '50',
                'type'        => 'number',
                'label'       => 'Persentase DP (%)',
                'description' => 'Persentase Down Payment yang harus dibayar saat pemesanan',
            ],
            [
                'key'         => 'min_order_lead_time',
                'value'       => '30',
                'type'        => 'number',
                'label'       => 'Minimal Waktu Pemesanan (menit)',
                'description' => 'Waktu minimum (dalam menit) sebelum pesanan bisa diproses',
            ],

            // ── Info Perusahaan ──
            [
                'key'         => 'company_name',
                'value'       => 'Catering Markesot',
                'type'        => 'text',
                'label'       => 'Nama Perusahaan',
                'description' => 'Nama catering yang ditampilkan di sistem',
            ],
            [
                'key'         => 'company_phone',
                'value'       => '081234567890',
                'type'        => 'text',
                'label'       => 'No. Telepon',
                'description' => 'Nomor telepon / WhatsApp yang bisa dihubungi',
            ],
            [
                'key'         => 'company_address',
                'value'       => 'Jl. Contoh No. 1, Kota',
                'type'        => 'text',
                'label'       => 'Alamat',
                'description' => 'Alamat lengkap perusahaan catering',
            ],

            // ── Rekening Bank ──
            [
                'key'         => 'bank_name',
                'value'       => 'BCA',
                'type'        => 'text',
                'label'       => 'Nama Bank',
                'description' => 'Nama bank untuk pembayaran transfer',
            ],
            [
                'key'         => 'account_number',
                'value'       => '1234567890',
                'type'        => 'text',
                'label'       => 'Nomor Rekening',
                'description' => 'Nomor rekening bank tujuan transfer',
            ],
            [
                'key'         => 'account_name',
                'value'       => 'Markesot Catering',
                'type'        => 'text',
                'label'       => 'Nama Pemilik Rekening',
                'description' => 'Nama pemilik rekening bank',
            ],

            // ── QR Payment ──
            [
                'key'         => 'qr_payment_image',
                'value'       => '',
                'type'        => 'image',
                'label'       => 'QR Code Pembayaran',
                'description' => 'Gambar QR Code untuk pembayaran (QRIS, dll)',
            ],

            // ── Admin Token (Keamanan) ──
            [
                'key'         => 'admin_tokens',
                'value'       => json_encode([
                    [
                        'password' => 'admin123',
                        'token'    => strtoupper(Str::random(6)),
                    ],
                ]),
                'type'        => 'json',
                'label'       => 'Password & Token Admin',
                'description' => 'Daftar password admin dan token verifikasi untuk registrasi admin baru',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
