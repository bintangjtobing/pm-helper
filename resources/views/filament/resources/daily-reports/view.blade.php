<x-filament::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Header --}}
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ __('Daily Report') }} — {{ $record->date_label }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $record->project?->name ?? __('General Report') }}
                        </p>
                    </div>
                    <div>{!! $record->status_badge !!}</div>
                </div>
            </x-filament::card>

            {{-- Accomplished --}}
            <x-filament::card>
                <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-900 dark:text-white">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('What was accomplished today') }}
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! $record->accomplished ? Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($record->accomplished)) : '<span class="text-gray-400 italic">' . __('No content') . '</span>' !!}
                </div>
            </x-filament::card>

            {{-- Plans --}}
            <x-filament::card>
                <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-900 dark:text-white">
                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('Plans for tomorrow') }}
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! $record->plans ? Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($record->plans)) : '<span class="text-gray-400 italic">' . __('No content') . '</span>' !!}
                </div>
            </x-filament::card>

            {{-- Blockers --}}
            @if($record->blockers)
            <x-filament::card>
                <h3 class="flex items-center gap-2 mb-3 text-base font-semibold text-gray-900 dark:text-white">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('Blockers / Issues') }}
                </h3>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    {!! Str::markdown(\App\Helpers\CodeBlockHelper::autoDetectCodeBlocks($record->blockers)) !!}
                </div>
            </x-filament::card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <x-filament::card>
                <dl class="space-y-4">
                    {{-- Author --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Author') }}</dt>
                        <dd class="flex items-center gap-2 mt-1">
                            @php
                                $avatar = $record->user->getAttributes()['avatar_url']
                                    ?? ('https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&size=64&background=' . substr(md5($record->user->id), 0, 6) . '&color=ffffff');
                            @endphp
                            <img src="{{ $avatar }}" class="object-cover w-6 h-6 rounded-full" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</span>
                        </dd>
                    </div>

                    {{-- Project --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Project') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->project?->name ?? __('General') }}
                        </dd>
                    </div>

                    {{-- Date --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Report Date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->report_date->format('l, d F Y') }}
                        </dd>
                    </div>

                    {{-- Status --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</dt>
                        <dd class="mt-1">{!! $record->status_badge !!}</dd>
                    </div>

                    {{-- Submitted --}}
                    @if($record->submitted_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Submitted') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->submitted_at->format('d M Y H:i') }}
                            <span class="text-xs text-gray-500">({{ $record->submitted_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                    @endif

                    {{-- Acknowledged --}}
                    @if($record->acknowledged_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Acknowledged By') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $record->acknowledgedByUser?->name }}
                            <span class="text-xs text-gray-500">({{ $record->acknowledged_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                    @endif

                    {{-- Created --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Created') }}</dt>
                        <dd class="mt-1 text-sm text-gray-500">
                            {{ $record->created_at->format('d M Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </x-filament::card>
        </div>
    </div>
</x-filament::page>
