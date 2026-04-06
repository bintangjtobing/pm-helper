@php
    $illustrationIndex = (now()->dayOfYear % 4) + 1;
    $illustration = asset("images/birthday/{$illustrationIndex}.png");
    $balloonColors = ['#f87171', '#fb923c', '#facc15', '#4ade80', '#60a5fa', '#a78bfa', '#f472b6'];

    // Prepare per-user data with personal wish
    $userData = $birthdayUsers->map(function ($u) {
        $age = $u->birthday ? $u->birthday->age : null;
        $wish = \App\Models\BirthdayWish::randomForAge($age);
        $avatar = $u->getAttributes()['avatar_url']
            ?? ('https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&size=128&background=' . substr(md5($u->id), 0, 6) . '&color=ffffff');
        return (object) ['user' => $u, 'age' => $age, 'wish' => $wish, 'avatar' => $avatar];
    });
@endphp

<x-filament::widget>
    <div class="relative overflow-hidden rounded-lg" style="background: #1e1b2e; border: 1px solid rgba(124, 58, 237, 0.15); min-height: 120px;">
        <div class="relative z-10" style="padding: 16px 24px; max-width: 65%;">
            <h2 class="text-xl font-bold text-white">Happy Birthday!</h2>

            <div class="mt-2 space-y-3">
                @foreach($userData as $idx => $bd)
                <div class="flex items-start gap-3">
                    {{-- Avatar with balloons --}}
                    <div class="relative shrink-0" style="width:40px;height:52px;">
                        @for($i = 0; $i < 5; $i++)
                        @php
                            $color = $balloonColors[($idx * 5 + $i) % count($balloonColors)];
                            $left = rand(-4, 30);
                            $delay = $i * 0.7 + ($idx * 0.3);
                            $duration = rand(25, 40) / 10;
                            $size = rand(5, 8);
                        @endphp
                        <div style="position:absolute;left:{{ $left }}px;bottom:0;width:{{ $size }}px;height:{{ $size * 1.2 }}px;background:{{ $color }};border-radius:50% 50% 50% 50% / 40% 40% 60% 60%;opacity:0;animation:balloonFloat {{ $duration }}s {{ $delay }}s ease-in-out infinite;pointer-events:none;z-index:1;">
                            <div style="position:absolute;bottom:-{{ $size * 0.5 }}px;left:50%;width:1px;height:{{ $size * 0.5 }}px;background:{{ $color }};transform:translateX(-50%);"></div>
                        </div>
                        @endfor
                        <img src="{{ $bd->avatar }}" alt="{{ $bd->user->name }}"
                             class="rounded-full object-cover border-2 border-[#1e1b2e]"
                             style="width:34px;height:34px;position:absolute;bottom:0;left:3px;z-index:2;" />
                    </div>

                    {{-- Name + age + personal wish --}}
                    <div class="min-w-0 pt-0.5">
                        <p class="text-sm text-gray-300">
                            <strong class="text-white">{{ $bd->user->name }}</strong>
                            @if($bd->age)
                                <span class="text-gray-500">turns {{ $bd->age }}</span>
                            @endif
                        </p>
                        @if($bd->wish)
                        <p class="text-[11px] italic text-gray-500 leading-relaxed mt-0.5">"{{ Str::limit($bd->wish->wish, 120) }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
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
            100% { transform: translateY(-70px) scale(0.6); opacity: 0; }
        }
    </style>
</x-filament::widget>
