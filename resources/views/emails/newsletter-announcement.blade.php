@extends('emails.layouts.base')

@section('title')
    New Announcement
@endsection

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <div style="display:inline-block; padding:6px 12px; border-radius:999px; background-color:rgba(11,94,215,0.12); color:#0b5ed7; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; font-size:12px; margin:0 0 12px 0;">
                Announcement
            </div>
            <p style="margin:0 0 10px 0; font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">
                {{ $announcement->title }}
            </p>
            @if($announcement->description)
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827; white-space:pre-line;">{{ $announcement->description }}</p>
            @else
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">We have a new update to share with you.</p>
            @endif

            <p style="margin:18px 0 0 0; font-size:15px; color:#111827;">— {{ $appName }} Team</p>
        </td>
    </tr>
</table>
@endsection
