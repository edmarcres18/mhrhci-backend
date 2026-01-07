🎉 YOU'RE INVITED!
Welcome to {{ $appName }}
Logo: {{ $logoUrl ?? 'N/A' }}

================================================================================

Hello there!

Great news! {{ $inviterName }} has invited you to join {{ $appName }} as a valued team member.

We're excited to have you on board. To get started with your new account, complete your registration using the link below.

================================================================================

YOUR INVITATION DETAILS

Your Email: {{ $email }}
Assigned Role: {{ $roleDisplay }}
Invited By: {{ $inviterName }}

================================================================================

CREATE YOUR ACCOUNT

Click or copy this link to register:
{{ $url }}

================================================================================

WHAT YOU NEED TO KNOW

✓ This invitation expires {{ $expiresIn }} ({{ $expiresAt }})
✓ The invitation link can only be used once for security
✓ You'll have {{ $roleDisplay }} access privileges
✓ Your email address is already registered in our system
✓ After registration, you'll be automatically logged in

================================================================================

🔒 SECURITY NOTICE

This invitation was sent to {{ $email }}. If you did not expect this invitation or believe it was sent in error, please disregard this email and contact our support team.

================================================================================

NEED HELP?

If you have any questions, please contact our support team.

This is an automated message from {{ $appName }}.

© {{ date('Y') }} {{ $appName }}. All rights reserved.
