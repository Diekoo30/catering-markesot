<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $companyPhone;
    public ?string $companyWhatsappUrl;

    public function __construct(Order $order)
    {
        $this->order = $order->load('orderItems');
        $this->companyPhone = (string) app(SettingService::class)->get('company_phone', '');
        $this->companyWhatsappUrl = $this->makeWhatsappUrl($this->companyPhone);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Anda Sudah Selesai! — Markesot',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_completed',
        );
    }

    protected function makeWhatsappUrl(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return "https://wa.me/{$digits}";
    }
}

