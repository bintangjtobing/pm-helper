<x-filament::page>
    @php
        $primaryTone = fn($v) => $v >= 70 ? 'emerald' : ($v >= 40 ? 'amber' : 'rose');
    @endphp

    @if(! $review)
        @include('filament.pages.partials.okr-hero', [
            'label' => 'My Review',
            'title' => 'No reviews yet',
            'subtitle' => 'Reviews are generated when a period closes, or manually by an admin.',
            'accent' => 'indigo',
        ])
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-8 text-center">
            <h3 class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">Nothing to review at the moment</h3>
            <p class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-1.5">Your reviews will appear here when a period enters the closed state.</p>
        </div>
    @else
        @php
            $sysTone = $primaryTone($review->system_score);
            [$statusLabel, $statusClasses] = $review->statusBadge();
        @endphp

        @include('filament.pages.partials.okr-hero', [
            'label' => 'My Review — ' . ($review->period?->name ?? ''),
            'title' => 'Performance Review',
            'subtitle' => $review->period
                ? $review->period->start_date->format('d M Y') . ' — ' . $review->period->end_date->format('d M Y')
                : null,
            'accent' => 'indigo',
            'primary' => [
                'label' => 'System Score',
                'value' => number_format((float) $review->system_score, 1) . '%',
                'tone' => $sysTone,
            ],
            'secondary' => [
                'label' => 'Status',
                'value' => $statusLabel,
            ],
        ])

        {{-- Review selector --}}
        @if($reviews->count() > 1)
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <label for="myReviewSelect" class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Review</label>
                <div class="relative">
                    <select id="myReviewSelect" wire:model="reviewId"
                            class="appearance-none text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg pl-3 pr-9 py-1.5 text-gray-900 dark:text-gray-100 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 min-w-[260px]">
                        @foreach($reviews as $r)
                            <option value="{{ $r->id }}">{{ $r->period?->name ?? 'Period #' . $r->period_id }} — {{ ucfirst(str_replace('_', ' ', $r->status)) }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
        @endif

        {{-- Status banner --}}
        <div class="mb-5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3 flex flex-wrap items-center gap-3">
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded {{ $statusClasses }}">{{ $statusLabel }}</span>

            @if($review->self_submitted_at)
                <span class="text-[11.5px] text-gray-500 dark:text-gray-400">Self-submitted {{ $review->self_submitted_at->format('d M Y H:i') }}</span>
            @endif

            @if($review->supervisor_reviewed_at)
                <span class="text-[11.5px] text-gray-500 dark:text-gray-400">· Reviewed {{ $review->supervisor_reviewed_at->format('d M Y H:i') }}</span>
            @endif

            @if($review->supervisor)
                <span class="text-[11.5px] text-gray-500 dark:text-gray-400 ml-auto">Reviewer: <strong class="text-gray-700 dark:text-gray-200 font-semibold">{{ $review->supervisor->name }}</strong></span>
            @endif
        </div>

        {{-- Score summary trio --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
            @php
                $scoreCards = [
                    ['System',   (float) $review->system_score, $primaryTone((float) $review->system_score)],
                    ['Self',     $review->self_score !== null ? (float) $review->self_score : null, $review->self_score !== null ? $primaryTone((float) $review->self_score) : 'indigo'],
                    ['Final',    $review->final_score !== null ? (float) $review->final_score : null, $review->final_score !== null ? $primaryTone((float) $review->final_score) : 'indigo'],
                ];
                $toneClasses = [
                    'emerald' => 'text-emerald-600 dark:text-emerald-400',
                    'amber'   => 'text-amber-600 dark:text-amber-400',
                    'rose'    => 'text-rose-600 dark:text-rose-400',
                    'indigo'  => 'text-gray-400 dark:text-gray-500',
                ];
            @endphp
            @foreach($scoreCards as [$label, $value, $tone])
                <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-[10.5px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $label }}</div>
                    <div class="text-[28px] font-extrabold tabular-nums leading-none mt-1.5 {{ $toneClasses[$tone] }}">{{ $value === null ? '—' : number_format($value, 1) . '%' }}</div>
                </div>
            @endforeach
        </div>

        {{-- Per-KR rows --}}
        <h2 class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-3 mt-6">Key Results · {{ $review->keyResultReviews->count() }}</h2>

        <div class="space-y-3">
            @foreach($review->keyResultReviews as $krReview)
                @php
                    $kr = $krReview->keyResult;
                    $goal = $kr?->goal;
                    $sysPct = (float) $krReview->system_score;
                    $selfPct = $krReview->self_score !== null ? (float) $krReview->self_score : null;
                    $finalPct = $krReview->final_score !== null ? (float) $krReview->final_score : null;
                @endphp
                <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-start gap-2 flex-wrap">
                        @if($goal?->code)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-700 dark:bg-slate-600 text-slate-100 text-[10.5px] font-bold tabular-nums tracking-wide shadow-sm shrink-0 mt-0.5 whitespace-nowrap">{{ $goal->code }}{{ $kr?->code ? '.' . $kr->code : '' }}</span>
                        @elseif($kr?->code)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-700 dark:bg-slate-600 text-slate-100 text-[10.5px] font-bold tabular-nums tracking-wide shadow-sm shrink-0 mt-0.5 whitespace-nowrap">{{ $kr->code }}</span>
                        @endif
                        <div class="flex-1 min-w-[180px] text-[13px] font-medium text-gray-900 dark:text-gray-100 leading-snug">{{ $kr?->title ?? 'Key Result #' . $krReview->key_result_id }}</div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums whitespace-nowrap">Weight {{ $kr ? number_format((float) $kr->weight, 1) : '0' }}%</span>
                    </div>

                    @if($kr?->how_to_measure)
                        <p class="text-[11.5px] text-gray-500 dark:text-gray-400 mt-1.5 italic">How: {{ $kr->how_to_measure }}</p>
                    @endif

                    @if($krReview->snapshot_current !== null)
                        <div class="text-[11.5px] text-gray-500 dark:text-gray-400 mt-1.5 tabular-nums">
                            Snapshot: <strong class="text-gray-700 dark:text-gray-200">{{ number_format((float) $krReview->snapshot_current, 2) }}</strong> / <strong class="text-gray-700 dark:text-gray-200">{{ $krReview->snapshot_target !== null ? number_format((float) $krReview->snapshot_target, 2) : '—' }}</strong>{{ $kr?->unit ? ' ' . $kr->unit : '' }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                        {{-- System column --}}
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-900/40 px-3 py-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">System</div>
                            @php $t = $primaryTone($sysPct); @endphp
                            <div class="text-[20px] font-bold tabular-nums mt-0.5 {{ $toneClasses[$t] }}">{{ number_format($sysPct, 1) }}%</div>
                        </div>

                        {{-- Self column --}}
                        <div class="rounded-lg bg-indigo-50 dark:bg-indigo-500/10 px-3 py-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-300">Self</div>
                            @if($review->isSelfSubmittable())
                                <input type="number" step="0.01" min="0" max="100"
                                       wire:model.defer="krScores.{{ $krReview->id }}"
                                       class="mt-1 w-full rounded-md border border-indigo-300 dark:border-indigo-500/40 bg-white dark:bg-gray-800 text-[14px] font-semibold text-gray-900 dark:text-gray-100 px-2 py-1 tabular-nums focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            @else
                                @php $t = $selfPct !== null ? $primaryTone($selfPct) : 'indigo'; @endphp
                                <div class="text-[20px] font-bold tabular-nums mt-0.5 {{ $toneClasses[$t] }}">{{ $selfPct === null ? '—' : number_format($selfPct, 1) . '%' }}</div>
                            @endif
                        </div>

                        {{-- Final column --}}
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 px-3 py-2.5">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Final</div>
                            @php $t = $finalPct !== null ? $primaryTone($finalPct) : 'indigo'; @endphp
                            <div class="text-[20px] font-bold tabular-nums mt-0.5 {{ $toneClasses[$t] }}">{{ $finalPct === null ? '—' : number_format($finalPct, 1) . '%' }}</div>
                        </div>
                    </div>

                    @if($krReview->notes)
                        <div class="mt-3 rounded-md bg-gray-50 dark:bg-gray-900/40 px-3 py-2 text-[12px] text-gray-600 dark:text-gray-300">
                            <strong class="text-gray-700 dark:text-gray-200 font-semibold">Reviewer note:</strong> {{ $krReview->notes }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Narrative --}}
        <div class="mt-6 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
            <label class="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Your narrative</label>
            @if($review->isSelfSubmittable())
                <textarea wire:model.defer="narrative" rows="5"
                          placeholder="Write about what went well, what didn't, and the reasoning behind your self-ratings…"
                          class="mt-2 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-[13px] text-gray-900 dark:text-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 leading-relaxed"></textarea>
            @else
                <div class="mt-2 text-[13px] text-gray-700 dark:text-gray-200 leading-relaxed whitespace-pre-line">{{ $review->self_narrative ?: '—' }}</div>
            @endif
        </div>

        {{-- Supervisor feedback (if completed) --}}
        @if($review->supervisor_feedback)
            <div class="mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 p-4">
                <div class="text-[11px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300 mb-2">Supervisor Feedback</div>
                <div class="text-[13px] text-gray-800 dark:text-gray-100 leading-relaxed whitespace-pre-line">{{ $review->supervisor_feedback }}</div>
            </div>
        @endif

        {{-- Action buttons --}}
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if($review->isSelfSubmittable())
                <button wire:click="submitSelfReview" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[13px] font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Self-Review
                </button>
                <span class="text-[11.5px] text-gray-500 dark:text-gray-400">Once submitted, your supervisor will review.</span>
            @elseif($review->isCompleted() && ! $review->acknowledged_at)
                <button wire:click="acknowledge" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Acknowledge
                </button>
                <button wire:click="dispute" type="button"
                        onclick="return confirm('Mark this review as disputed? Your supervisor will be notified.')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-rose-300 dark:border-rose-500/40 text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-[13px] font-semibold transition">
                    Dispute
                </button>
            @elseif($review->acknowledged_at)
                <span class="text-[12px] text-emerald-700 dark:text-emerald-300 font-medium inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Acknowledged {{ $review->acknowledged_at->format('d M Y') }}
                </span>
            @endif
        </div>
    @endif
</x-filament::page>
