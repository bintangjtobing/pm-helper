<x-filament::page>

    @if ($plaintextToken)
        <div class="p-5 mb-6 border rounded-xl border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-950/40">
            <div class="flex items-start gap-4">
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
                    <div class="flex items-center gap-2 px-3 py-2 mt-3 font-mono text-xs break-all bg-white border rounded-lg border-emerald-200 dark:bg-gray-900 dark:border-emerald-800" x-data>
                        <span class="flex-1" x-ref="tokenString">{{ $plaintextToken }}</span>
                        <button type="button"
                                x-on:click="navigator.clipboard.writeText($refs.tokenString.innerText); $el.innerText = '{{ __('Copied!') }}'; setTimeout(() => { $el.innerText = '{{ __('Copy') }}' }, 1500)"
                                class="px-2 py-1 text-xs font-medium text-white rounded shrink-0 bg-emerald-600 hover:bg-emerald-700">
                            {{ __('Copy') }}
                        </button>
                    </div>
                    <div class="grid gap-3 mt-4 md:grid-cols-2">
                        <div class="p-3 text-xs border rounded-lg bg-white/50 border-emerald-200 dark:bg-gray-900/40 dark:border-emerald-800">
                            <div class="mb-1.5 font-semibold text-emerald-900 dark:text-emerald-200">{{ __('Claude Desktop app') }}</div>
                            <div class="mb-0.5 text-[11px] font-medium text-emerald-800 dark:text-emerald-300">{{ __('Server URL') }}</div>
                            <code class="block p-2 mb-2 overflow-x-auto text-[11px] bg-white border rounded border-emerald-200 dark:bg-gray-950 dark:border-emerald-800">{{ url('/api/mcp') }}</code>
                            <div class="mb-0.5 text-[11px] font-medium text-emerald-800 dark:text-emerald-300">{{ __('Custom header') }}</div>
                            <code class="block p-2 overflow-x-auto text-[11px] break-all bg-white border rounded border-emerald-200 dark:bg-gray-950 dark:border-emerald-800">Authorization: Bearer {{ $plaintextToken }}</code>
                            <p class="mt-2 text-[11px] text-emerald-700 dark:text-emerald-400">{{ __('Settings → Connectors → Add custom connector. Paste URL and header into their own fields (do NOT put them in one line).') }}</p>
                        </div>
                        <div class="p-3 text-xs border rounded-lg bg-white/50 border-emerald-200 dark:bg-gray-900/40 dark:border-emerald-800">
                            <div class="mb-1.5 font-semibold text-emerald-900 dark:text-emerald-200">{{ __('Claude Code CLI') }}</div>
                            <code class="block p-2 overflow-x-auto text-[11px] break-all bg-white border rounded border-emerald-200 dark:bg-gray-950 dark:border-emerald-800">claude mcp add --transport http pmhelper {{ url('/api/mcp') }} --header "Authorization: Bearer {{ $plaintextToken }}"</code>
                            <p class="mt-2 text-[11px] text-emerald-700 dark:text-emerald-400">{{ __('Run in your terminal — this single command is the whole setup.') }}</p>
                        </div>
                    </div>
                    <button wire:click="dismissToken" class="mt-3 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:underline">
                        {{ __('I\'ve saved it — dismiss') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Intro panel --}}
    <div class="flex flex-col gap-3 p-5 mb-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3 min-w-0">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg shrink-0 bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Connect Claude to PMHelper via MCP') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-600 dark:text-gray-400 max-w-2xl">
                    {{ __('Each token inherits your role and project access. Comments, tickets, and reports created through Claude are attributed to you.') }}
                </div>
                <div class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-500">
                    <strong class="text-gray-700 dark:text-gray-300">{{ __('Using Claude Desktop?') }}</strong>
                    {{ __('No manual token needed — add a Custom Connector pointing to') }}
                    <code class="px-1 py-0.5 font-mono rounded bg-gray-100 dark:bg-gray-900">{{ url('/api/mcp') }}</code>
                    {{ __('and OAuth will mint the token for you.') }}
                </div>
            </div>
        </div>
        <a href="{{ route('filament.pages.docs') }}#mcp"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg shrink-0 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
            {{ __('Setup guide') }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>

    {{-- Tokens table --}}
    <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        @php $tokens = $this->getTokens(); @endphp
        @if($tokens->count() === 0)
            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700/60">
                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ __('No MCP tokens yet') }}
                </div>
                <div class="max-w-sm text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Click "New MCP token" in the top-right to generate your first token — name it after the device that will use it (e.g. "Bintang MacBook").') }}
                </div>
            </div>
        @else
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="w-1/2 px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Name') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Last used') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-500 uppercase dark:text-gray-400">{{ __('Created') }}</th>
                        <th class="px-5 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tokens as $token)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 text-xs font-semibold uppercase rounded-lg shrink-0 bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400">
                                        {{ mb_substr(ltrim(str_replace('mcp:', '', $token->name)), 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 truncate dark:text-gray-100">{{ ltrim(str_replace('mcp:', '', $token->name)) }}</div>
                                        <div class="mt-0.5 text-[11px] font-mono text-gray-400 dark:text-gray-500">mcp:*</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                @if($token->last_used_at)
                                    <span title="{{ $token->last_used_at->format('Y-m-d H:i') }}">{{ $token->last_used_at->diffForHumans() }}</span>
                                @else
                                    <span class="italic text-gray-400 dark:text-gray-500">{{ __('never') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <span title="{{ $token->created_at->format('Y-m-d H:i') }}">{{ $token->created_at->format('Y-m-d') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button wire:click="revoke({{ $token->id }})"
                                        wire:confirm="{{ __('Revoke this token? Any machine using it will lose access immediately.') }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-950/40">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 3h4a1 1 0 011 1v3H9V4a1 1 0 011-1z"/></svg>
                                    {{ __('Revoke') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-filament::page>
