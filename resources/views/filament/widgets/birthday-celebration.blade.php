@php
    $illustrationIndex = (now()->dayOfYear % 4) + 1;
    $illustration = asset("images/birthday/{$illustrationIndex}.png");
    // Calculate ages and get age-appropriate wish
    $ages = $birthdayUsers->map(fn($u) => $u->birthday ? $u->birthday->age : null)->filter();
    $avgAge = $ages->isNotEmpty() ? (int) $ages->avg() : null;
    $wish = \App\Models\BirthdayWish::randomForAge($avgAge);
    $balloonColors = ['#f87171', '#fb923c', '#facc15', '#4ade80', '#60a5fa', '#a78bfa', '#f472b6'];
@endphp

<x-filament::widget>
    <div class="relative overflow-hidden rounded-lg" style="background: #1e1b2e; border: 1px solid rgba(124, 58, 237, 0.15); min-height: 110px;">
        <div class="relative z-10 flex items-center" style="padding: 16px 24px; min-height: 110px;">
            <div class="flex-1 min-w-0" style="max-width: 65%;">
                <h3 class="text-base font-bold text-white">Happy Birthday!</h3>
                <p class="mt-0.5 text-xs text-gray-400">
                    Let's celebrate
                    @foreach($birthdayUsers as $index => $bUser)
                        @php $age = $bUser->birthday ? $bUser->birthday->age : null; @endphp
                        <strong class="text-white">{{ $bUser->name }}</strong>@if($age) <span class="text-gray-500">(turns {{ $age }})</span>@endif@if($index < $birthdayUsers->count() - 2), @elseif($index === $birthdayUsers->count() - 2) & @endif
                    @endforeach
                    today!
                </p>
                @if($wish)
                <p class="mt-1.5 text-xs italic text-gray-500 leading-relaxed">"{{ Str::limit($wish->wish, 100) }}"</p>
                @endif

                {{-- Avatars with balloon animation --}}
                <div class="flex items-center mt-2" style="gap: 16px;">
                    @foreach($birthdayUsers as $bUser)
                    @php
                        $av = $bUser->getAttributes()['avatar_url']
                            ?? ('https://ui-avatars.com/api/?name=' . urlencode($bUser->name) . '&size=128&background=' . substr(md5($bUser->id), 0, 6) . '&color=ffffff');
                    @endphp
                    <div class="relative" style="width:36px;height:48px;">
                        {{-- Balloons floating up around avatar --}}
                        @for($i = 0; $i < 5; $i++)
                        @php
                            $color = $balloonColors[($loop->parent->index * 5 + $i) % count($balloonColors)];
                            $left = rand(-4, 28);
                            $delay = $i * 0.7 + ($loop->parent->index * 0.3);
                            $duration = rand(25, 40) / 10;
                            $size = rand(5, 8);
                        @endphp
                        <div style="position:absolute;left:{{ $left }}px;bottom:0;width:{{ $size }}px;height:{{ $size * 1.2 }}px;background:{{ $color }};border-radius:50% 50% 50% 50% / 40% 40% 60% 60%;opacity:0;animation:balloonFloat {{ $duration }}s {{ $delay }}s ease-in-out infinite;pointer-events:none;z-index:1;">
                            <div style="position:absolute;bottom:-{{ $size * 0.5 }}px;left:50%;width:1px;height:{{ $size * 0.5 }}px;background:{{ $color }}80;transform:translateX(-50%);"></div>
                        </div>
                        @endfor

                        {{-- Avatar --}}
                        <img src="{{ $av }}" alt="{{ $bUser->name }}"
                             class="rounded-full object-cover border-2 border-[#1e1b2e]"
                             style="width:32px;height:32px;position:absolute;bottom:0;left:2px;z-index:2;"
                             title="{{ $bUser->name }}" />
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Illustration --}}
        <img src="{{ $illustration }}" alt="" style="position:absolute;right:16px;bottom:0;height:135px;width:auto;object-fit:contain;opacity:1;pointer-events:none;" />

        {{-- Subtle glow --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);"></div>
    </div>

    <style>
        @keyframes balloonFloat {
            0% { transform: translateY(0) scale(0.3); opacity: 0; }
            8% { opacity: 1; transform: translateY(-5px) scale(1); }
            70% { opacity: 1; }
            100% { transform: translateY(-70px) translateX({{ rand(-8, 8) }}px) scale(0.6); opacity: 0; }
        }
    </style>
</x-filament::widget>
