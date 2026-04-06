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
            .inner-body { width: 100% !important; border-radius: 0 !important; }
            .footer { width: 100% !important; }
            .content-cell { padding: 28px 20px !important; }
        }
        @media only screen and (max-width: 500px) {
            .button { width: 100% !important; }
        }

        body {
            background-color: #1a1a2e;
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #e2e8f0;
            -webkit-text-size-adjust: none;
        }

        h1 { font-size: 22px; font-weight: 700; color: #f7fafc; margin: 0 0 20px 0; line-height: 1.3; }
        p { font-size: 15px; line-height: 1.75; color: #cbd5e0; margin: 0 0 16px 0; }
        strong { color: #f7fafc; }
        blockquote {
            border-left: 3px solid #4299e1;
            margin: 16px 0;
            padding: 10px 16px;
            background-color: rgba(66, 153, 225, 0.1);
            border-radius: 0 6px 6px 0;
        }
        blockquote p { color: #90cdf4; margin: 0; font-size: 14px; font-style: italic; }

        a { color: #63b3ed; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .wrapper { background-color: #1a1a2e; margin: 0; padding: 0; width: 100%; }
        .content { margin: 0; padding: 0; width: 100%; }

        .header { padding: 40px 0 24px 0; text-align: center; }

        .body { margin: 0; padding: 0; width: 100%; }

        .inner-body {
            background-color: #16213e;
            border-radius: 8px;
            margin: 0 auto;
            padding: 0;
            width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .accent-line {
            height: 3px;
            width: 40px;
            background-color: #4299e1;
            border-radius: 2px;
            margin-bottom: 24px;
        }

        .content-cell { padding: 40px 44px; }

        .footer { margin: 0 auto; padding: 24px; text-align: center; width: 600px; }
        .footer p { color: #4a5568; font-size: 12px; text-align: center; margin: 0; }
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
                                        <div class="accent-line"></div>
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
