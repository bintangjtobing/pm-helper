<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body { width: 100% !important; }
            .footer { width: 100% !important; }
            .content-cell { padding: 24px 16px !important; }
        }
        @media only screen and (max-width: 500px) {
            .button { width: 100% !important; }
        }

        body {
            background-color: #f4f5f7;
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            -webkit-text-size-adjust: none;
        }

        h1 { font-size: 20px; font-weight: 700; color: #1a202c; margin: 0 0 16px 0; }
        p { font-size: 15px; line-height: 1.7; color: #4a5568; margin: 0 0 16px 0; }
        strong { color: #2d3748; }
        blockquote {
            border-left: 3px solid #4299e1;
            margin: 16px 0;
            padding: 8px 16px;
            background-color: #ebf8ff;
            border-radius: 0 6px 6px 0;
        }
        blockquote p { color: #2c5282; margin: 0; font-size: 14px; }

        a { color: #4299e1; text-decoration: none; }

        .wrapper { background-color: #f4f5f7; margin: 0; padding: 0; width: 100%; }
        .content { margin: 0; padding: 0; width: 100%; }

        .header { padding: 32px 0 16px 0; text-align: center; }

        .body { margin: 0; padding: 0; width: 100%; }

        .inner-body {
            background-color: #ffffff;
            border-radius: 8px;
            margin: 0 auto;
            padding: 0;
            width: 600px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .content-cell { padding: 36px 40px; }

        .footer { margin: 0 auto; padding: 24px; text-align: center; width: 600px; }
        .footer p { color: #a0aec0; font-size: 12px; text-align: center; margin: 0; }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding: 16px 0;">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
