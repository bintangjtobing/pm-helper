{{--
    @props: $periods (Collection<GoalPeriod>)
             $periodId (int|null) — current selected, bound via wire:model
--}}
@if($periods->isEmpty())
    <div class="mb-5 rounded-lg border border-dashed border-amber-300 dark:border-amber-500/40 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-[12.5px] text-amber-800 dark:text-amber-200">
        <strong class="font-semibold">No periods configured yet.</strong>
        A Super Admin can create one at <em>Performance → Periods</em> to enable OKR tracking.
    </div>
@else
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <label for="okrPeriodSelect" class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Period</label>
        <div class="relative">
            <select id="okrPeriodSelect"
                    wire:model="periodId"
                    class="appearance-none text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg pl-3 pr-9 py-1.5 text-gray-900 dark:text-gray-100 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 min-w-[220px] transition">
                @foreach($periods as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} — {{ ucfirst($p->status) }}</option>
                @endforeach
            </select>
            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
@endif
