@php
    use App\Models\SiteInformation;

    $appName = $appName ?? config('app.name');
    $logoUrl = $logoUrl ?? 'https://mhrpci.site/images/mhrhci.png';
    $siteInfo = $siteInfo ?? SiteInformation::first();

    $contactEmail = $siteInfo?->email_address;
    $contactTel = $siteInfo?->tel_no;
    $contactPhone = $siteInfo?->phone_no;
    $contactAddress = $siteInfo?->address;
    $contactTelegram = $siteInfo?->telegram;
    $contactFacebookRaw = $siteInfo?->facebook;
    $contactViber = $siteInfo?->viber;
    $contactWhatsapp = $siteInfo?->whatsapp;

    $contactFacebookUrl = $contactFacebookRaw
        ? (filter_var($contactFacebookRaw, FILTER_VALIDATE_URL)
            ? $contactFacebookRaw
            : 'https://facebook.com/' . ltrim($contactFacebookRaw, '@/'))
        : null;

    $contactTelegramUrl = $contactTelegram
        ? 'https://t.me/' . ltrim($contactTelegram, '@')
        : null;

    $contactViberUrl = $contactViber
        ? 'viber://chat?number=' . preg_replace('/[^0-9+]/', '', $contactViber)
        : null;

    $contactWhatsappUrl = $contactWhatsapp
        ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $contactWhatsapp)
        : null;

    $iconSize = 18;
    $chipSize = 40;

    $hasContacts = $contactEmail || $contactTel || $contactPhone || $contactAddress || $contactTelegram || $contactFacebookRaw || $contactViber || $contactWhatsapp;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $appName)</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            color: #0f172a;
            line-height: 1.6;
        }
        a {
            color: #0b5ed7;
            text-decoration: none;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        img {
            border: 0;
            line-height: 100%;
            text-decoration: none;
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color:#0f172a; line-height:1.6;">
    <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#f4f6f9;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="600" style="width:100%; max-width:600px; border-spacing:0; border-collapse:collapse; background-color:#ffffff; border:1px solid #e5e7eb;">
                    <tr>
                        <td align="center" style="padding:28px 24px 16px 24px; border-bottom:1px solid #e5e7eb;">
                            <img src="{{ $logoUrl }}" alt="{{ $appName }} logo" width="180" style="display:block; width:180px; max-width:100%; height:auto;">
                            @hasSection('title')
                                <div style="margin-top:18px; font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">
                                    @yield('title')
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px; font-size:15px; color:#111827;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px 8px 24px; border-top:1px solid #e5e7eb; text-align:center; color:#6b7280; font-size:12px;">
                            <p style="margin:0 0 6px 0;">This is an automated message from {{ $appName }}.</p>
                        </td>
                    </tr>
                    @if($hasContacts)
                    <tr>
                        <td style="padding:6px 20px 10px 20px;">
                            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; text-align:center;">
                                <tr>
                                    <td style="padding:6px 0 4px 0; font-size:12px; color:#0f172a; font-weight:700; letter-spacing:0.1px;">
                                        Contact us
                                    </td>
                                </tr>
                                @if($contactAddress)
                                <tr>
                                    <td style="padding:0 12px 6px 12px; font-size:12px; color:#0f172a; line-height:1.5;">
                                        {{ $contactAddress }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0 10px 0;">
                                        <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; text-align:center;">
                                            <tr>
                                                @if($contactEmail)
                                                <td style="padding:4px 6px;">
                                                    <a href="mailto:{{ $contactEmail }}" aria-label="Email" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://static.vecteezy.com/system/resources/previews/042/165/820/non_2x/gmail-icon-transparent-free-png.png" alt="Email" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactTel)
                                                <td style="padding:4px 6px;">
                                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactTel) }}" aria-label="Telephone" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://www.freeiconspng.com/uploads/phone-icon-7.png" alt="Telephone" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactPhone)
                                                <td style="padding:4px 6px;">
                                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" aria-label="Phone" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://t4.ftcdn.net/jpg/03/12/57/97/360_F_312579728_JztO9YzcpOwnjuPpnh7i3pxfH1HDbX2l.jpg" alt="Phone" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactTelegramUrl)
                                                <td style="padding:4px 6px;">
                                                    <a href="{{ $contactTelegramUrl }}" aria-label="Telegram" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/1200px-Telegram_logo.svg.png" alt="Telegram" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactFacebookUrl)
                                                <td style="padding:4px 6px;">
                                                    <a href="{{ $contactFacebookUrl }}" aria-label="Facebook" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b8/2021_Facebook_icon.svg/1200px-2021_Facebook_icon.svg.png" alt="Facebook" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactViberUrl)
                                                <td style="padding:4px 6px;">
                                                    <a href="{{ $contactViberUrl }}" aria-label="Viber" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://www.vhv.rs/dpng/d/557-5573613_viber-logo-png-logo-viber-icon-png-transparent.png" alt="Viber" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                                @if($contactWhatsappUrl)
                                                <td style="padding:4px 6px;">
                                                    <a href="{{ $contactWhatsappUrl }}" aria-label="WhatsApp" style="display:inline-block; width:{{ $chipSize }}px; height:{{ $chipSize }}px; border:1px solid #dbeafe; border-radius:999px; background-color:#f8fbff; text-decoration:none; line-height:{{ $chipSize }}px; text-align:center;">
                                                        <img src="https://cdn-icons-png.flaticon.com/512/3670/3670051.png" alt="WhatsApp" width="{{ $iconSize }}" height="{{ $iconSize }}" style="vertical-align:middle; border:0;">
                                                    </a>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:0 24px 20px 24px; text-align:center; color:#6b7280; font-size:12px;">
                            <p style="margin:6px 0 0 0;">© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
