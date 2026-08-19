<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorAccountCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $doctor,
        public string $defaultPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Medora HMS doctor account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-account-created',
        );
    }
}
