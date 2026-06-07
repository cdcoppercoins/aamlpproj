<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f8fb;font-family:Georgia,'Times New Roman',serif;color:#1a3344;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f8fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #d8e4ec;border-radius:8px;">
                    <tr>
                        <td style="padding:24px 28px 12px;border-bottom:1px solid #e8eef3;">
                            <p style="margin:0 0 6px;font-size:13px;color:#556677;letter-spacing:0.04em;text-transform:uppercase;">
                                MiniLicensePlates.com
                            </p>
                            <h1 style="margin:0;font-size:22px;line-height:1.35;color:#264a62;">
                                {{ $campaign->subject }}
                            </h1>
                            @if ($isTest)
                                <p style="margin:12px 0 0;padding:10px 12px;background:#fff8e8;border:1px solid #e8d8a8;color:#7a5b00;font-size:14px;">
                                    This is a test message sent only to you. Members will not see this banner.
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;font-size:16px;line-height:1.6;">
                            <p style="margin:0 0 16px;">Hello {{ $recipient->name }},</p>
                            <div>{!! $campaign->body_html !!}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 24px;border-top:1px solid #e8eef3;font-size:13px;line-height:1.5;color:#667788;">
                            <p style="margin:0 0 8px;">
                                You are receiving this because you have a member account at
                                <a href="{{ url('/') }}" style="color:#4079a5;">MiniLicensePlates.com</a>.
                            </p>
                            <p style="margin:0;">
                                <a href="{{ $unsubscribeUrl }}" style="color:#4079a5;">Unsubscribe from member emails</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
