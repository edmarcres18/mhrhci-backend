@extends('emails.layouts.base')

@section('title')
    New Product
@endsection

@section('content')
<table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse;">
    <tr>
        <td style="padding:0; font-size:15px; color:#111827;">
            <div style="display:inline-block; padding:6px 12px; border-radius:999px; background-color:rgba(11,94,215,0.12); color:#0b5ed7; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; font-size:12px; margin:0 0 12px 0;">
                New Product
            </div>
            <p style="margin:0 0 10px 0; font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-0.2px;">
                {{ $product->name }}
            </p>
            @if($heroImage || $principalLogo)
            <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; margin:0 0 14px 0;">
                <tr>
                    <td width="112" valign="top" style="padding:0 12px 0 0;">
                        @if($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $product->name }}" width="100" style="display:block; width:100px; max-width:100%; height:auto; border:1px solid #e5e7eb; border-radius:12px;">
                        @else
                            <img src="{{ $principalLogo }}" alt="{{ $principal ? $principal->name : 'Principal' }} logo" width="100" style="display:block; width:100px; max-width:100%; height:auto; border:1px solid #e5e7eb; border-radius:12px; padding:8px; background-color:#f8fafc;">
                        @endif
                    </td>
                    <td valign="top" style="padding:0;">
                        <p style="margin:0; font-size:15px; color:#0f172a; font-weight:600;">Fresh drop from our catalog</p>
                        @if($principal)
                            <p style="margin:6px 0 0 0; font-size:14px; color:#111827;">By {{ $principal->name }}</p>
                        @endif
                        <p style="margin:10px 0 0 0; font-size:14px; color:#111827;">Explore the latest addition to our lineup.</p>
                    </td>
                </tr>
            </table>
            @endif

            @if($product->description)
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">{{ $product->description }}</p>
            @else
                <p style="margin:0 0 12px 0; font-size:15px; color:#111827;">We’ve added a new product to serve your needs. Stay tuned for more details.</p>
            @endif

            @if(!empty($product->features))
                <table role="presentation" width="100%" style="border-spacing:0; border-collapse:collapse; background-color:#e9f2ff; border:1px solid #c7dcff; margin:12px 0 0 0;">
                    <tr>
                        <td style="padding:14px 16px; font-size:14px; color:#0f172a;">
                            <strong style="display:block; margin:0 0 6px 0;">Highlights:</strong>
                            <ul style="margin:0; padding-left:18px; color:#0f172a; line-height:1.6;">
                                @foreach($product->features as $feature)
                                    @if($feature)
                                    <li>{{ $feature }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </table>
            @endif

            <p style="margin:18px 0 0 0; font-size:15px; color:#111827;">— {{ $appName }} Team</p>
        </td>
    </tr>
</table>
@endsection
