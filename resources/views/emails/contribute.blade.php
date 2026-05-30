@php
    $replySubject = rawurlencode('Re: MiniLicensePlates.com Contribution');
    $replyHref = 'mailto:'.$senderEmail.'?subject='.$replySubject;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contribute form message</title>
</head>
<body style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #222;">
    <p style="margin: 0 0 16px;">
        <a href="{{ $replyHref }}"
           style="display: inline-block; padding: 10px 18px; background: #4079a5; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 4px;">
            Reply to {{ $senderName }}
        </a>
    </p>

    <p style="margin: 0 0 8px;"><strong>From:</strong> {{ $senderName }}</p>
    <p style="margin: 0 0 16px;"><strong>Email:</strong> <a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></p>

    <p style="margin: 0 0 8px;"><strong>Message:</strong></p>
    <p style="margin: 0 0 16px; white-space: pre-wrap;">{{ $messageText }}</p>

    <p style="margin: 0; font-size: 12px; color: #666;">Sent from the MiniLicensePlates.com contribute form · IP {{ $ip }}</p>
</body>
</html>
