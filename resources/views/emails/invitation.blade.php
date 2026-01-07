@extends('emails.layouts.base')

@section('title')
    Account Invitation
@endsection

@php
    $invitationUrl = $invitationUrl ?? $url ?? '#';
@endphp

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <p style="margin:0 0 14px 0; font-size:16px; font-weight:600; color:#0f172a;">Hello there!</p>
            <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">
                Great news! <strong>{{ $inviterName }}</strong> has invited you to join <strong>{{ $appName }}</strong> as a valued team member.
            </p>
            <p style="margin:0 0 18px 0; font-size:15px; color:#111827;">
                To get started, accept your invitation and complete your registration.
            </p>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:6px 0 18px 0;">
            <a href="{{ $invitationUrl }}" style="background-color:#0b5ed7; color:#ffffff; display:inline-block; padding:12px 24px; font-weight:700; font-size:15px; border-radius:6px; text-decoration:none;">
                Accept Invitation
            </a>
        </td>
    </tr>

    <tr>
        <td style="padding:0 0 18px 0;">
            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#f8fafc; border:1px solid #e5e7eb;">
                <tr>
                    <td style="padding:14px 16px;">
                        <div style="font-size:12px; letter-spacing:0.3px; color:#6b7280; text-transform:uppercase; margin:0 0 4px 0;">Your Email</div>
                        <div style="font-size:15px; font-weight:600; color:#0f172a; margin:0 0 12px 0;">{{ $email }}</div>

                        <div style="font-size:12px; letter-spacing:0.3px; color:#6b7280; text-transform:uppercase; margin:0 0 4px 0;">Assigned Role</div>
                        <div style="font-size:15px; font-weight:600; color:#0f172a; margin:0 0 12px 0;">{{ $roleDisplay }}</div>

                        <div style="font-size:12px; letter-spacing:0.3px; color:#6b7280; text-transform:uppercase; margin:0 0 4px 0;">Invited By</div>
                        <div style="font-size:15px; font-weight:600; color:#0f172a; margin:0;">{{ $inviterName }}</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="padding:0 0 14px 0;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#0f172a; font-weight:600;">What you need to know</p>
            <ul style="margin:0; padding-left:18px; color:#111827; font-size:14px; line-height:1.6;">
                <li>This invitation expires <strong>{{ $expiresIn }}</strong> ({{ $expiresAt }})</li>
                <li>The invitation link can only be used once for security</li>
                <li>You'll have <strong>{{ $roleDisplay }}</strong> access privileges</li>
                <li>Your email address is already registered in our system</li>
                <li>After registration, you'll be automatically logged in</li>
            </ul>
        </td>
    </tr>

    <tr>
        <td style="padding:0 0 14px 0;">
            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#fef5e7; border:1px solid #f3bf99;">
                <tr>
                    <td style="padding:12px 14px; font-size:13px; color:#92400e;">
                        <strong style="color:#92400e;">Security notice:</strong> This invitation was sent to {{ $email }}. If you did not expect it, please ignore this email or contact support.
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td style="padding:0;">
            <p style="margin:0 0 6px 0; font-size:13px; color:#6b7280;">Button not working?</p>
            <p style="margin:0; font-size:13px; color:#0b5ed7;">
                <a href="{{ $invitationUrl }}" style="color:#0b5ed7; text-decoration:none; font-weight:600;">Click here to open the invitation</a>
            </p>
        </td>
    </tr>
</table>
@endsection
