<x-filament::page>
    <div class="space-y-8">
        @if($projects->count() > 0)
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($projects as $project)
            @php
                $coverUrl = $project->getFirstMediaUrl('cover') ?: $project->getFirstMediaUrl('default');
                $gradients = [
                    'linear-gradient(135deg, #3b82f6, #9333ea)',
                    'linear-gradient(135deg, #10b981, #0d9488)',
                    'linear-gradient(135deg, #f97316, #e11d48)',
                    'linear-gradient(135deg, #6366f1, #2563eb)',
                    'linear-gradient(135deg, #ec4899, #7c3aed)',
                    'linear-gradient(135deg, #06b6d4, #2563eb)',
                ];
                $gradient = $gradients[$project->id % count($gradients)];
                $totalTickets = $project->tickets_count;
                $doneCount = $totalTickets > 0
                    ? $project->tickets()->whereHas('status', fn($q) => $q->whereIn('name', ['QA Passed', 'Released', 'Approved']))->count()
                    : 0;
                $progressPct = $totalTickets > 0 ? round(($doneCount / $totalTickets) * 100) : 0;
                $members = $project->users->merge(collect([$project->owner]))->unique('id');
                $desc = trim(preg_replace('#https?://\S+#', '', strip_tags($project->description ?? '')));
            @endphp
            <div wire:click="selectProject({{ $project->id }})"
                class="overflow-hidden transition-all duration-200 bg-white border rounded-xl cursor-pointer group border-gray-200 dark:border-gray-700 dark:bg-gray-800 hover:shadow-lg hover:border-primary-400 dark:hover:border-primary-500 hover:-translate-y-0.5">

                {{-- Cover / Gradient --}}
                <div class="relative overflow-hidden" style="height:148px;{{ $coverUrl ? '' : 'background:' . $gradient . ';' }}">
                    @if($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $project->name }}" class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105">
                    @endif
                    {{-- Bottom gradient overlay for text readability --}}
                    <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 60%);"></div>

                    {{-- Type Badge --}}
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide" style="background:rgba(0,0,0,0.45);color:#fff;backdrop-filter:blur(4px);">
                            {{ ucfirst($project->type ?? 'kanban') }}
                        </span>
                    </div>

                    {{-- Status Badge --}}
                    @if($project->status)
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide" style="background:{{ $project->status->color }}CC;color:#fff;backdrop-filter:blur(4px);">
                            <span class="w-1.5 h-1.5 rounded-full mr-1.5" style="background:#fff;"></span>
                            {{ $project->status->name }}
                        </span>
                    </div>
                    @endif

                    {{-- Project Name on cover --}}
                    <div class="absolute bottom-0 left-0 right-0 px-5 pb-3">
                        <h3 class="text-lg font-bold text-white drop-shadow-sm">{{ $project->name }}</h3>
                    </div>
                </div>

                {{-- Content --}}
                <div class="px-5 pt-3 pb-4">
                    {{-- Description --}}
                    @if($desc)
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ Str::limit($desc, 120) }}</p>
                    @endif

                    {{-- Progress Bar --}}
                    @if($totalTickets > 0)
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400">{{ __('Progress') }}</span>
                            <span class="text-[10px] font-bold {{ $progressPct >= 80 ? 'text-green-500' : ($progressPct >= 40 ? 'text-blue-500' : 'text-gray-500') }}">{{ $progressPct }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $progressPct >= 80 ? 'bg-green-500' : ($progressPct >= 40 ? 'bg-blue-500' : 'bg-gray-400') }}" style="width:{{ $progressPct }}%"></div>
                        </div>
                    </div>
                    @endif

                    {{-- Meta Row: Members + Stats --}}
                    <div class="flex items-center justify-between mt-3">
                        {{-- Member Avatars --}}
                        <div class="flex items-center">
                            <div class="flex -space-x-2">
                                @foreach($members->take(5) as $member)
                                @php
                                    $memberAvatar = $member->getAttributes()['avatar_url']
                                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&size=32&background=' . substr(md5($member->id), 0, 6) . '&color=ffffff');
                                @endphp
                                <img src="{{ $memberAvatar }}" alt="{{ $member->name }}" title="{{ $member->name }}"
                                     class="w-7 h-7 rounded-full ring-2 ring-white dark:ring-gray-800 object-cover" loading="lazy">
                                @endforeach
                                @if($members->count() > 5)
                                <span class="flex items-center justify-center w-7 h-7 text-[9px] font-bold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-full ring-2 ring-white dark:ring-gray-800">+{{ $members->count() - 5 }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">{{ $totalTickets }} {{ __('tickets') }}</span>
                            @if($doneCount > 0)
                            <span class="font-semibold text-green-500">{{ $doneCount }} {{ __('done') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ __('Click to open board') }}</span>
                        <div class="flex items-center gap-1 text-xs font-medium text-gray-400 transition-colors group-hover:text-primary-500">
                            <span>{{ ucfirst($project->type ?? 'kanban') }} Board</span>
                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 space-x-2 text-sm rounded-lg text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span>Select a project to access its {{ __('Scrum') }} or {{ __('Kanban') }} board</span>
            </div>
        </div>
        @else
        <div class="py-12 text-center">
            <div class="flex items-center justify-center w-24 h-24 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">No projects found</h3>
            <p class="mb-6 text-gray-500 dark:text-gray-400">You don't have access to any projects yet.</p>
            <a href="{{ route('filament.resources.projects.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-colors bg-primary-600 border border-transparent rounded-md hover:bg-primary-700">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                Create New Project
            </a>
        </div>
        @endif
    </div>
</x-filament::page>
