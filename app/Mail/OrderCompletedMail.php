<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load('orderItems');
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
}
