<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// implements ShouldQueue tells Laravel to queue this email instead of sending it inline.
class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;
    // SerializesModels matters once it’s queued. The job doesn’t store the whole order, only its id, and the worker re-fetches it fresh from the database when it runs. The email always reflects current data.

    /**
     * Create a new message instance.
     */
    // The constructor is how data gets in. Public properties are automatically passed to the template.
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    // the 'outside' of the email: subject (and optionally from, reply-to)
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->id . ' confirmed',
        );
    }

    /**
     * Get the message content definition.
     */
    // the 'inside': which template to render
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
