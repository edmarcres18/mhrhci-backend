<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class NewsletterProductCreated extends Notification
{
    public function __construct(
        public Product $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'MHR Health Care Inc');
        $logoUrl = 'https://mhrpci.site/images/mhrhci.png';
        $principal = $this->product->principal;
        $principalLogo = $principal && $principal->logo ? Storage::disk('public')->url($principal->logo) : null;

        $images = [];
        if (is_array($this->product->images)) {
            foreach ($this->product->images as $path) {
                if ($path) {
                    $images[] = Storage::disk('public')->url($path);
                }
            }
        }
        $heroImage = $images[0] ?? null;

        return (new MailMessage)
            ->mailer(config('mail.default'))
            ->subject('New Product: '.$this->product->name)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.newsletter-product', [
                'appName' => $appName,
                'logoUrl' => $logoUrl,
                'product' => $this->product,
                'principal' => $principal,
                'principalLogo' => $principalLogo,
                'heroImage' => $heroImage,
                'gallery' => $images,
            ]);
    }
}
