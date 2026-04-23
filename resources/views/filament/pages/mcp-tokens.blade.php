<x-filament::page>

    @if ($plaintextToken)
        <div class="p-4 mb-6 border border-emerald-300 rounded-lg bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-950/40">
            <div class="flex items-start gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full shrink-0 bg-emerald-100 dark:bg-emerald-900">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">
                        {{ __('Token created:') }} <code class="px-1.5 py-0.5 text-xs font-mono bg-emerald-100 rounded dark:bg-emerald-900/60">mcp:{{ $plaintextTokenName }}</code>
                    </div>
                    <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-300">
                        {{ __('This is the only time you\'ll see the token string. Copy it now and store it in your Claude config.') }}
                    </p>
                    <div class="flex items-center gap-2 px-3 py-2 mt-3 font-mono text-xs break-all bg-white border rounded border-emerald-200 dark:bg-gray-900 dark:border-emerald-800" x-data>
                        <span class="flex-1" x-ref="tokenString">{{ $plaintextToken }}</span>
                        <button type="button"
                                x-on:click="navigator.clipboard.writeText($refs.tokenString.innerText); $el.innerText = '{{ __('Copied!') }}'; setTimeout(() => { $el.innerText = '{{ __('Copy') }}' }, 1500)"
                                class="px-2 py-1 text-xs font-medium rounded bg-emerald-600 hover:bg-emerald-700 text-white shrink-0">
                            {{ __('Copy') }}
                        </button>
                    </div>
                    <div class="mt-3 text-xs text-emerald-800 dark:text-emerald-300">
                        <div class="mb-1 font-semibold">{{ __('Quick setup (Claude Code CLI)') }}</div>
                        <code class="block p-2 overflow-x-auto text-[11px] bg-white border rounded border-emerald-200 dark:bg-gray-900 dark:border-emerald-800">claude mcp add --transport http pmhelper {{ url('/api/mcp') }} --header "Authorization: Bearer {{ $plaintextToken }}"</code>
                    </div>
                    <button wire:click="dismissToken" class="mt-3 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:underline">
                        {{ __('I\'ve saved it — dismiss') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('MCP tokens let you connect Claude Desktop or Claude Code to PMHelper. Comments, tickets, reports created via MCP are attributed to the token owner. Revoke any token whose device you no longer use.') }}
        <a href="{{ url('/admin/docs') }}#section-16-mcp" class="text-primary-500 hover:underline">{{ __('Full setup guide →') }}</a>
    </div>

    <div class="overflow-hidden bg-white rounded-lg shadow dark:bg-gray-800">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Last used') }}</th>
                    <th class="px-4 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Created') }}</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->getTokens() as $token)
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $token->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            @if($token->last_used_at)
                                {{ $token->last_used_at->diffForHumans() }}
                            @else
                                <span class="italic text-gray-400">{{ __('never') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ $token->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="revoke({{ $token->id }})"
                                    wire:confirm="{{ __('Revoke this token? Any machine using it will lose access immediately.') }}"
                                    class="px-2.5 py-1 text-xs font-medium text-red-600 border border-red-300 rounded hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-950/40">
                                {{ __('Revoke') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-sm text-center text-gray-500 dark:text-gray-400">
                            {{ __('No MCP tokens yet. Click "New MCP token" to create your first one.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::page>
