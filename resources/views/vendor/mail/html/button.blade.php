@props([
    'url',
    'color' => 'primary',
    'align' => 'left',
])
@php
$bgColor = match(true) {
    in_array($color, ['success', 'green']) => '#2f855a',
    in_array($color, ['error', 'red']) => '#c53030',
    default => '#1a1a1a',
};
@endphp
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="{{ $align }}" style="padding: 8px 0 16px 0;">
            <a href="{{ $url }}" target="_blank" rel="noopener"
               style="display: inline-block;
                      background-color: {{ $bgColor }};
                      color: #ffffff;
                      padding: 10px 22px;
                      border-radius: 4px;
                      font-size: 14px;
                      font-weight: 600;
                      text-decoration: none;
                      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
