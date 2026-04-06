@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="{{ $align }}">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td>
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           style="display: inline-block;
                                  background-color: {{ $color === 'success' || $color === 'green' ? '#48bb78' : ($color === 'error' || $color === 'red' ? '#f56565' : '#4299e1') }};
                                  color: #ffffff;
                                  padding: 12px 28px;
                                  border-radius: 6px;
                                  font-size: 15px;
                                  font-weight: 600;
                                  text-decoration: none;
                                  text-align: center;
                                  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
