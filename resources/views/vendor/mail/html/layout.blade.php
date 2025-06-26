<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ config('app.name') }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        /* Base styles */
        body {
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .wrapper {
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .content {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .header {
            padding: 25px 0;
            text-align: center;
        }

        .header a {
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
        }

        .body {
            background-color: #ffffff;
            border-bottom: 1px solid #edeff2;
            border-top: 1px solid #edeff2;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            background-color: #ffffff;
            border-color: #e8e5ef;
            border-radius: 12px;
            border-width: 1px;
            box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015);
            margin: 0 auto;
            padding: 0;
            width: 600px;
        }

        .content-cell {
            max-width: 100vw;
            padding: 32px;
        }

        .greeting {
            margin-top: 0;
            color: #3d4852;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .intro {
            color: #74787e;
            font-size: 16px;
            line-height: 1.5em;
            margin-bottom: 20px;
        }

        .outro {
            color: #74787e;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 20px;
        }

        .button {
            background-color: #4299e1;
            border-radius: 8px;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
            margin: 20px 0;
        }

        .button-inner {
            background-color: #4299e1;
            border-radius: 8px;
            color: #fff;
            display: inline-block;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.5em;
            padding: 12px 25px;
            text-decoration: none;
        }

        .button-inner:hover {
            background-color: #3182ce;
        }

        .footer {
            margin: 0 auto;
            padding: 32px;
            text-align: center;
            width: 600px;
        }

        .footer p {
            color: #b0adc5;
            font-size: 12px;
            text-align: center;
        }

        .notification-box {
            background: linear-gradient(145deg, #f8fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .notification-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .notification-badge {
            display: inline-block;
            background: linear-gradient(145deg, #4299e1 0%, #3182ce 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
        }

        .decorative-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4299e1 0%, #3182ce 50%, #2b77cb 100%);
        }

        .subcopy {
            background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #cbd5e0;
            margin-top: 20px;
        }

        .info-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .info-icon {
            width: 20px;
            height: 20px;
            background: linear-gradient(145deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 50%;
            margin-right: 12px;
            display: inline-block;
        }

        .info-title {
            color: #4a5568;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0"
                            style="border: hidden !important;">
                            <table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0"
                                role="presentation">
                                <!-- Body content -->
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
