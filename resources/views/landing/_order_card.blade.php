<div class="order-card">
  @php
    $firstPayment = $order->payments->first();
    $paymentLabel = 'Tidak Diketahui';
    $isFullPayment = $order->dp_percentage == 100;

    if ($firstPayment) {
        if ($firstPayment->payment_method === 'cash') {
            $paymentLabel = 'Tunai';
        } elseif ($firstPayment->payment_method === 'transfer') {
            $paymentLabel = $isFullPayment ? 'Transfer (Lunas)' : 'Transfer (DP ' . round($order->dp_percentage) . '%)';
        }
    }
    
    $isDpFlow = (!$isFullPayment && $firstPayment && $firstPayment->payment_method === 'transfer');
    $isAdminRejection = $order->status === 'cancelled' && $order->payments->where('status', 'rejected')->isNotEmpty();
  @endphp
  <div class="order-header">
    <div>
      <div class="order-id">{{ $order->order_number }}</div>
      <div class="order-date">Tanggal Acara: {{ \Carbon\Carbon::parse($order->event_date)->translatedFormat('d F Y') }}</div>
      <div class="order-date">Dipesan: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }}</div>
    </div>
    <div class="status-badge status-{{ $order->status }}">
      @if($order->status == 'pending')
        <svg style="width:14px;height:14px;margin-right:4px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Nunggu Verifikasi
      @elseif($order->status == 'dp_paid')
        <svg style="width:14px;height:14px;margin-right:4px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg> DP Diterima
      @elseif($order->status == 'confirmed')
        <svg style="width:14px;height:14px;margin-right:4px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg> Sedang Dimasak
      @elseif($order->status == 'completed')
        <svg style="width:14px;height:14px;margin-right:4px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Selesai
      @else
        <svg style="width:14px;height:14px;margin-right:4px;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Dibatalkan
      @endif
    </div>
  </div>

  <div class="items-list">
    @foreach($order->orderItems as $item)
      <div class="item-row">
        <span>{{ $item->menu_name }} ×{{ $item->quantity }}</span>
        <span>Rp.{{ number_format($item->subtotal, 2, ',', '.') }}</span>
      </div>
    @endforeach
    <div class="total-row">
      <div style="display: flex; flex-direction: column;">
        <span>Total Bayar</span>
        <span style="font-size: 0.75rem; color: var(--text-light); font-weight: 500; margin-top: 2px;">Metode: {{ $paymentLabel }}</span>
      </div>
      <span>Rp.{{ number_format($order->total_amount, 2, ',', '.') }}</span>
    </div>
  </div>

  @if($isAdminRejection && $order->cancellation_reason)
    <div style="margin-top: 1rem; padding: 0.75rem; background-color: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #b91c1c; margin-bottom: 0.2rem;">Pesanan Ditolak oleh Admin</div>
      <div style="font-size: 0.8rem; color: #991b1b;">Alasan: {{ $order->cancellation_reason }}</div>
    </div>
  @endif

  @if($order->status === 'cancelled' && !$isAdminRejection && $order->cancellation_reason)
    <div style="margin-top: 1rem; padding: 0.75rem; background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #4b5563; margin-bottom: 0.2rem;">Dibatalkan oleh Anda</div>
      <div style="font-size: 0.8rem; color: #4b5563;">Alasan: {{ $order->cancellation_reason }}</div>
    </div>
  @endif

  @if($firstPayment && $firstPayment->payment_method === 'cash' && $order->status !== 'cancelled' && $order->status !== 'completed')
    <div style="margin-top: 1.2rem; text-align: right; border-top: 1px solid #f0f0f0; padding-top: 1rem; position: relative; z-index: 10;">
      @if($order->status === 'pending')
        <button type="button" onclick="batalPesanan({{ $order->id }})" style="background: white; border: 1px solid #ef4444; color: #ef4444; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: 0.2s; pointer-events: auto; position: relative; z-index: 20;">Batalkan Pesanan</button>
      @else
        <button type="button" disabled style="background: #f5f5f5; border: 1px solid #ddd; color: #aaa; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: not-allowed;">Batalkan Pesanan</button>
        <div style="font-size: 0.75rem; color: #888; margin-top: 0.4rem;">Pesanan sedang diproses dan tidak bisa dibatalkan</div>
      @endif
    </div>
  @endif

  @if(isset($showTracker) && $showTracker && $order->status !== 'cancelled')
    <div class="tracker">
      <div class="track-step {{ in_array($order->status, ['pending', 'dp_paid', 'confirmed', 'completed']) ? 'active' : '' }}">
        <div class="track-icon">1</div>
        <div class="track-label">Verifikasi</div>
      </div>
      @if($isDpFlow)
        <div class="track-step {{ in_array($order->status, ['dp_paid', 'confirmed', 'completed']) ? 'active' : '' }}">
          <div class="track-icon">2</div>
          <div class="track-label">DP Diterima</div>
        </div>
      @endif
      <div class="track-step {{ in_array($order->status, ['confirmed', 'completed']) ? 'active' : '' }}">
        <div class="track-icon">{{ $isDpFlow ? '3' : '2' }}</div>
        <div class="track-label">Dimasak</div>
      </div>
      <div class="track-step {{ $order->status === 'completed' ? 'active' : '' }}">
        <div class="track-icon">{{ $isDpFlow ? '4' : '3' }}</div>
        <div class="track-label">Selesai</div>
      </div>
    </div>
  @endif
</div>
