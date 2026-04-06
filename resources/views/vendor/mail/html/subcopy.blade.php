<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td style="padding: 20px 0 0 0;">
            <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                   style="border-top: 1px solid #e8e5de;">
                <tr>
                    <td style="padding: 16px 0 0 0;">
                        <p style="color: #a3a097; font-size: 12px; line-height: 1.6; margin: 0; word-break: break-all;">
                            {{ Illuminate\Mail\Markdown::parse($slot) }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
