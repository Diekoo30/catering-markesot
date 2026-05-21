<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Selesai</title>
</head>
<body style="margin:0; padding:0; background:#f5f1eb; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
<tr><td align="center">
<table width="100%" style="max-width:520px; background:white; border-radius:20px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.08);">
  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg, #800000, #a52a2a); padding:30px 30px 25px; text-align:center;">
      <div style="font-size:28px; font-weight:800; color:#d4af37; letter-spacing:2px;">MARKESOT</div>
      <div style="font-size:13px; color:rgba(255,255,255,0.7); margin-top:4px;">Kantin Universitas Jember</div>
    </td>
  </tr>
  <!-- Success Icon -->
  <tr>
    <td style="text-align:center; padding:30px 30px 10px;">
      <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
        <tr>
          <td style="width:70px; height:70px; background:#e6f9f0; border-radius:50%; text-align:center; vertical-align:middle; font-size:36px; color:#48752C; line-height:70px;">
            &#10003;
          </td>
        </tr>
      </table>
      <div style="font-size:20px; font-weight:800; color:#1e7f51; margin-top:14px;">Pesanan Anda Sudah Selesai!</div>
    </td>
  </tr>
  <!-- Body -->
  <tr>
    <td style="padding:15px 30px 30px;">
      <div style="font-size:14px; color:#666; line-height:1.6; margin-bottom:20px; text-align:center;">
        Halo <strong>{{ $order->customer_name }}</strong>, pesanan Anda telah selesai diproses dan siap diambil/diantar. Terima kasih telah memesan di Markesot!
      </div>

      <!-- Order Info -->
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#fafafa; border:1px solid #eee; border-radius:14px; overflow:hidden; margin-bottom:20px;">
        <!-- Order Number & Date -->
        <tr>
          <td style="padding:16px 20px; border-bottom:1px solid #eee; width:50%; vertical-align:top;">
            <div style="font-size:11px; color:#999; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">No. Pesanan</div>
            <div style="font-size:15px; font-weight:800; color:#800000;">{{ $order->order_number }}</div>
          </td>
          <td style="padding:16px 20px; border-bottom:1px solid #eee; width:50%; vertical-align:top; text-align:right;">
            <div style="font-size:11px; color:#999; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Tanggal Acara</div>
            <div style="font-size:14px; font-weight:700; color:#333;">{{ \Carbon\Carbon::parse($order->event_date)->translatedFormat('d F Y') }}</div>
          </td>
        </tr>
        <!-- Items Header -->
        <tr>
          <td colspan="2" style="padding:14px 20px 8px;">
            <div style="font-size:13px; font-weight:700; color:#555;">
              Detail Pesanan
            </div>
          </td>
        </tr>
        <!-- Items -->
        <tr>
          <td colspan="2" style="padding:0 20px 12px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; color:#444;">
              @foreach($order->orderItems as $item)
              <tr>
                <td style="padding:8px 0; border-bottom:1px solid #f0f0f0;">{{ $item->menu_name }}</td>
                <td style="padding:8px 0; border-bottom:1px solid #f0f0f0; text-align:center; width:50px; color:#888;">&times;{{ $item->quantity }}</td>
                <td style="padding:8px 0; border-bottom:1px solid #f0f0f0; text-align:right; font-weight:600;">Rp.{{ number_format($item->subtotal, 0, ',', '.') }}</td>
              </tr>
              @endforeach
            </table>
          </td>
        </tr>
        <!-- Total -->
        <tr>
          <td colspan="2" style="padding:12px 20px 16px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-weight:800; font-size:14px; color:#333;">Total</td>
                <td style="text-align:right; font-weight:800; font-size:18px; color:#800000;">Rp.{{ number_format($order->total_amount, 0, ',', '.') }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <!-- CTA -->
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="text-align:center; padding-top:10px;">
            <div style="font-size:13px; color:#888; line-height:1.6;">
              Jika ada pertanyaan, hubungi kami via WhatsApp<br>atau datang langsung ke kantin.
            </div>
            @if($companyWhatsappUrl)
            <div style="margin-top:14px;">
              <a href="{{ $companyWhatsappUrl }}" target="_blank" style="display:inline-block; background:#25D366; color:#ffffff; text-decoration:none; font-size:13px; font-weight:800; padding:10px 18px; border-radius:999px;">
                Hubungi WhatsApp Markesot
              </a>
            </div>
            @endif
            <div style="font-size:13px; color:#888; margin-top:8px;">
              Terima kasih telah memilih <strong style="color:#800000;">Markesot</strong>!
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- Footer -->
  <tr>
    <td style="background:#fafafa; padding:20px 30px; text-align:center; border-top:1px solid #eee;">
      <div style="font-size:12px; color:#aaa;">&copy; {{ date('Y') }} Markesot Catering. All rights reserved.</div>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
