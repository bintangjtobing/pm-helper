@php
    $illustrationIndex = (now()->dayOfYear % 4) + 1;
    $illustration = asset("images/birthday/{$illustrationIndex}.png");
@endphp

<x-filament::widget>
    <div class="relative overflow-hidden rounded-lg" style="background: #1e1b2e; border: 1px solid rgba(124, 58, 237, 0.15); height: 100px;">
        <div class="relative z-10 flex items-center h-full" style="padding: 0 24px;">
            {{-- Text content --}}
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-white">Happy Birthday!</h3>
                <p class="mt-0.5 text-xs text-gray-400">
                    Let's celebrate
                    @foreach($birthdayUsers as $index => $bUser)
                        <strong class="text-white">{{ $bUser->name }}</strong>@if($index < $birthdayUsers->count() - 2), @elseif($index === $birthdayUsers->count() - 2) & @endif
                    @endforeach
                    today!
                </p>
                <div class="flex items-center -space-x-2 mt-2">
                    @foreach($birthdayUsers as $bUser)
                    @php
                        $av = $bUser->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($bUser->name) . '&size=128&background=' . substr(md5($bUser->id), 0, 6) . '&color=ffffff');
                    @endphp
                    <img src="{{ $av }}" alt="{{ $bUser->name }}" class="w-7 h-7 rounded-full object-cover border-2 border-[#1e1b2e]" title="{{ $bUser->name }}" />
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Illustration pinned to right --}}
        <img src="{{ $illustration }}" alt="" class="absolute right-4 bottom-0 h-[90px] object-contain opacity-80 pointer-events-none" />

        {{-- Subtle glow --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);"></div>
    </div>
</x-filament::widget>
