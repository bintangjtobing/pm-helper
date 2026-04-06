<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('Reports Overview') }}
                </h3>
                <span class="text-xs text-gray-500">{{ __('This week') }}</span>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- Daily Reports Today --}}
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">{{ __('Daily Today') }}</span>
                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $dailyToday }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-0.5">/ {{ $teamSize }}</span>
                    </div>
                    {{-- Progress bar --}}
                    <div class="mt-2">
                        <div class="w-full h-1.5 bg-blue-200 dark:bg-blue-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $dailyTodayPct >= 80 ? 'bg-green-500' : ($dailyTodayPct >= 50 ? 'bg-blue-500' : 'bg-orange-500') }}"
                                 style="width: {{ min($dailyTodayPct, 100) }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 mt-0.5">{{ $dailyTodayPct }}% {{ __('submitted') }}</span>
                    </div>
                </div>

                {{-- Weekly Reports --}}
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase">{{ __('Weekly') }}</span>
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $weeklyThisWeek }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 mb-0.5">/ {{ $teamSize }}</span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full h-1.5 bg-purple-200 dark:bg-purple-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $weeklyThisWeekPct >= 80 ? 'bg-green-500' : ($weeklyThisWeekPct >= 50 ? 'bg-purple-500' : 'bg-orange-500') }}"
                                 style="width: {{ min($weeklyThisWeekPct, 100) }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 mt-0.5">{{ $weeklyThisWeekPct }}% {{ __('submitted') }}</span>
                    </div>
                </div>

                {{-- Pending Acknowledgment --}}
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase">{{ __('Pending Review') }}</span>
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingTotal }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                        <span>{{ $pendingDaily }} {{ __('daily') }}</span>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <span>{{ $pendingWeekly }} {{ __('weekly') }}</span>
                    </div>
                </div>

                {{-- Weekly Activity Streak --}}
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-green-600 dark:text-green-400 uppercase">{{ __('Week Activity') }}</span>
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $streakPct }}%</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        {{ $daysWithReports }}/{{ $daysPassed }} {{ __('days with reports') }}
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('filament.resources.daily-reports.index') }}" class="text-xs font-medium text-blue-600 hover:underline">
                    {{ __('Daily Reports') }} →
                </a>
                <a href="{{ route('filament.resources.weekly-reports.index') }}" class="text-xs font-medium text-purple-600 hover:underline">
                    {{ __('Weekly Reports') }} →
                </a>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
