<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterAnnouncementCreated extends Notification
{
    public function __construct(
        public Announcement $announcement
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'MHR Health Care Inc');
        $logoUrl = 'https://mhrpci.site/images/mhrhci.png';

        return (new MailMessage)
            ->mailer(config('mail.default'))
            ->subject('New Announcement: '.$this->announcement->title)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.newsletter-announcement', [
                'appName' => $appName,
                'logoUrl' => $logoUrl,
                'announcement' => $this->announcement,
            ]);
    }
}
