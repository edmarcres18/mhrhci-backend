@extends('emails.layouts.base')

@section('title')
    Newsletter Preferences
@endsection

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <p style="margin:0 0 12px 0; font-size:20px; font-weight:700; color:#0f172a;">
                Newsletter update
            </p>
            <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">
                {{ $message ?? 'Your preference has been updated.' }}
            </p>
            @if(!($success ?? false))
                <p style="margin:12px 0 0 0; font-size:14px; color:#6b7280;">
                    If you believe this is an error, please contact our support team.
                </p>
            @endif
        </td>
    </tr>
</table>
@endsection
