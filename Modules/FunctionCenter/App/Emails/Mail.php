<?php

namespace Modules\FunctionCenter\App\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;

class Mail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData['subject']
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->mailData['content']
        );
    }

    public function attachments(): array
    {
        $attach = $this->mailData['attach'] ?? null;
        if (!is_string($attach) || $attach === '' || !file_exists($attach)) {
            return [];
        }

        return [
            Attachment::fromPath($attach)
        ];
    }
}
