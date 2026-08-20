<?php

namespace App\Mail;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientAccountCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Patient $patient,
        public User $user,
        public string $defaultPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Medora patient portal account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patient-account-created',
        );
    }
}
