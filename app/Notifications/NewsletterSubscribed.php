<?php

namespace App\Notifications;

use App\Models\NewsletterSubscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterSubscribed extends Notification
{
    public function __construct(
        public NewsletterSubscription $subscription
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = trim(($this->subscription->first_name ?? '').' '.($this->subscription->last_name ?? ''));
        $appName = config('app.name', 'MHR Health Care Inc');
        $logoUrl = 'https://mhrpci.site/images/mhrhci.png';

        return (new MailMessage)
            ->mailer(config('mail.default'))
            ->subject('You are subscribed to '.$appName.' updates')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.newsletter-subscribed', [
                'name' => $name,
                'appName' => $appName,
                'logoUrl' => $logoUrl,
            ]);
    }
}
