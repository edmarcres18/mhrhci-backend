@extends('emails.layouts.base')

@section('title')
    Newsletter Subscription
@endsection

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <div style="display:inline-block; padding:6px 12px; border-radius:999px; background-color:rgba(11,94,215,0.12); color:#0b5ed7; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; font-size:12px; margin:0 0 12px 0;">
                Newsletter
            </div>
            <p style="margin:0 0 10px 0; font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">
                Welcome to {{ $appName }}
            </p>
            <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">Hi {{ $name ?: 'there' }},</p>
            <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">
                Thank you for subscribing to our newsletter. You’ll receive curated updates on new products, events, and healthcare insights from MHR Health Care Inc.
            </p>
            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#e9f2ff; border:1px solid #c7dcff;">
                <tr>
                    <td style="padding:14px 16px; font-size:15px; color:#0f172a;">
                        You’re all set. We’ll only send meaningful updates and you can unsubscribe at any time.
                    </td>
                </tr>
            </table>
            <p style="margin:14px 0 12px 0; font-size:15px; color:#111827;">If you did not request this subscription, you can ignore this email.</p>
            <p style="margin:18px 0 0 0; font-size:15px; color:#111827;">— {{ $appName }} Team</p>
        </td>
    </tr>
</table>
@endsection
