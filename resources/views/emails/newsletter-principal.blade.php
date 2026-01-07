@extends('emails.layouts.base')

@section('title')
    New Principal Added
@endsection

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <div style="display:inline-block; padding:6px 12px; border-radius:999px; background-color:rgba(11,94,215,0.12); color:#0b5ed7; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; font-size:12px; margin:0 0 12px 0;">
                New Principal
            </div>
            <p style="margin:0 0 10px 0; font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">
                {{ $principal->name }}
            </p>
            @if($principalLogo)
            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; margin:0 0 14px 0;">
                <tr>
                    <td width="96" valign="top" style="padding:0 12px 0 0;">
                        <img src="{{ $principalLogo }}" alt="{{ $principal->name }} logo" width="80" style="display:block; width:80px; max-width:100%; height:auto; border:1px solid #e5e7eb; border-radius:12px; padding:8px; background-color:#f8fafc;">
                    </td>
                    <td valign="top" style="padding:0;">
                        <p style="margin:0; font-size:15px; color:#0f172a; font-weight:600;">Meet our new partner</p>
                        <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">We’re excited to welcome {{ $principal->name }} to our portfolio.</p>
                    </td>
                </tr>
            </table>
            @endif

            @if($principal->description)
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">{{ $principal->description }}</p>
            @else
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">We’ve added a new principal to expand our offerings and bring you more choice.</p>
            @endif

            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#e9f2ff; border:1px solid #c7dcff; margin:12px 0 0 0;">
                <tr>
                    <td style="padding:14px 16px; font-size:14px; color:#0f172a;">
                        Look out for products from {{ $principal->name }} in our catalog. We’ll share featured items and updates soon.
                    </td>
                </tr>
            </table>

            <p style="margin:18px 0 0 0; font-size:15px; color:#111827;">— {{ $appName }} Team</p>
        </td>
    </tr>
</table>
@endsection
