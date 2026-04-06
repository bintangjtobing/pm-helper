<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f0efeb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-text-size-adjust: none;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f0efeb;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 580px;">
                    {{-- Header --}}
                    {{ $header ?? '' }}

                    {{-- Body Card --}}
                    <tr>
                        <td style="padding: 0;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                   style="background-color: #ffffff; border: 1px solid #e0ddd5; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 40px 40px 36px 40px;">
                                        {{-- Accent line --}}
                                        <div style="width: 36px; height: 3px; background-color: #c85a3a; border-radius: 2px; margin-bottom: 28px;"></div>

                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
