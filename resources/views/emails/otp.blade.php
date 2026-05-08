<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kode OTP</title>
</head>
<body style="margin:0; padding:0; background:#f5f1eb; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
<tr><td align="center">
<table width="100%" style="max-width:480px; background:white; border-radius:20px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.08);">
  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg, #800000, #a52a2a); padding:30px 30px 25px; text-align:center;">
      <div style="font-size:28px; font-weight:800; color:#d4af37; letter-spacing:2px;">MARKESOT</div>
      <div style="font-size:13px; color:rgba(255,255,255,0.7); margin-top:4px;">Kantin Universitas Jember</div>
    </td>
  </tr>
  <!-- Body -->
  <tr>
    <td style="padding:35px 30px;">
      <div style="font-size:16px; color:#333; margin-bottom:8px;">Halo <strong>{{ $userName }}</strong>,</div>
      <div style="font-size:14px; color:#666; line-height:1.6; margin-bottom:25px;">
        Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode OTP di bawah ini untuk melanjutkan:
      </div>

      <!-- OTP Code -->
      <div style="text-align:center; margin:25px 0;">
        <div style="display:inline-block; background:#fdf2f2; border:2px dashed #800000; border-radius:14px; padding:18px 40px;">
          <div style="font-size:36px; font-weight:800; color:#800000; letter-spacing:12px; font-family:monospace;">{{ $otpCode }}</div>
        </div>
      </div>

      <div style="font-size:13px; color:#999; text-align:center; margin-bottom:25px; line-height:1.5;">
        Kode ini berlaku selama <strong style="color:#800000;">10 menit</strong>.<br>
        Jangan bagikan kode ini kepada siapapun.
      </div>

      <div style="background:#fffbe6; border:1px solid #ffe58f; border-radius:10px; padding:12px 16px; font-size:12px; color:#8a6d3b; line-height:1.5;">
        ⚠️ Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.
      </div>
    </td>
  </tr>
  <!-- Footer -->
  <tr>
    <td style="background:#fafafa; padding:20px 30px; text-align:center; border-top:1px solid #eee;">
      <div style="font-size:12px; color:#aaa;">© {{ date('Y') }} Markesot Catering. All rights reserved.</div>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
