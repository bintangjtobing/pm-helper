@php
    // Rotate illustrations based on day
    $illustrationIndex = (now()->dayOfYear % 4) + 1;
    $illustration = asset("images/birthday/{$illustrationIndex}.png");
@endphp

<x-filament::widget>
    <div class="relative overflow-hidden rounded-lg" style="background: #1e1b2e; border: 1px solid rgba(124, 58, 237, 0.15);">
        <div class="flex items-center" style="padding: 20px 24px;">
            {{-- Text content --}}
            <div class="flex-1 min-w-0 z-10">
                <h3 class="text-base font-bold text-white">
                    Happy Birthday!
                </h3>
                <p class="mt-1 text-sm text-gray-300">
                    Let's celebrate
                    @foreach($birthdayUsers as $index => $bUser)
                        <strong class="text-white">{{ $bUser->name }}</strong>@if($index < $birthdayUsers->count() - 2), @elseif($index === $birthdayUsers->count() - 2) & @endif
                    @endforeach
                    today!
                </p>

                {{-- Birthday avatars --}}
                <div class="flex items-center -space-x-2 mt-3">
                    @foreach($birthdayUsers as $bUser)
                    @php
                        $av = $bUser->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($bUser->name) . '&size=128&background=' . substr(md5($bUser->id), 0, 6) . '&color=ffffff');
                    @endphp
                    <img src="{{ $av }}" alt="{{ $bUser->name }}"
                         class="w-10 h-10 rounded-full object-cover border-2 border-[#1e1b2e] shadow-lg"
                         title="{{ $bUser->name }}" />
                    @endforeach
                </div>
            </div>

            {{-- Illustration --}}
            <div class="shrink-0 hidden sm:block z-10">
                <img src="{{ $illustration }}" alt="Birthday celebration" class="h-28 object-contain opacity-90" />
            </div>
        </div>

        {{-- Subtle glow effect --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full" style="background: radial-gradient(circle, rgba(124, 58, 237, 0.12) 0%, transparent 70%);"></div>
        <div class="absolute -bottom-8 -left-8 w-32 h-32 rounded-full" style="background: radial-gradient(circle, rgba(20, 184, 166, 0.08) 0%, transparent 70%);"></div>
    </div>
</x-filament::widget>
