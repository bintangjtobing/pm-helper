@props(['url'])
<tr>
    <td align="center" style="padding: 0 0 24px 0;">
        <a href="{{ $url }}" style="text-decoration: none;">
            @if(config('app.logo') && !str_ends_with(config('app.logo'), 'favicon.ico'))
                <img src="{{ config('app.logo') }}" alt="{{ config('app.name') }}" style="max-height: 40px; width: auto; display: inline-block;">
            @else
                <span style="color: #1a1a1a; font-size: 18px; font-weight: 700; letter-spacing: -0.3px;">{{ config('app.name') }}</span>
            @endif
        </a>
    </td>
</tr>
