<?php

namespace App\Notifications;

use App\Models\Principal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class NewsletterPrincipalCreated extends Notification
{
    public function __construct(
        public Principal $principal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'MHR Health Care Inc');
        $logoUrl = 'https://mhrpci.site/images/mhrhci.png';
        $principalLogo = $this->principal->logo ? Storage::disk('public')->url($this->principal->logo) : null;

        return (new MailMessage)
            ->mailer(config('mail.default'))
            ->subject('New Principal Added: '.$this->principal->name)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.newsletter-principal', [
                'appName' => $appName,
                'logoUrl' => $logoUrl,
                'principal' => $this->principal,
                'principalLogo' => $principalLogo,
            ]);
    }
}
