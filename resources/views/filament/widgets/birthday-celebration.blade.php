<x-filament::widget>
    <div class="relative overflow-hidden rounded-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); padding: 24px 28px;">
        <div class="flex items-center gap-6">
            {{-- Illustration placeholder --}}
            <div class="shrink-0 hidden sm:block">
                @if(file_exists(public_path('images/birthday-illustration.png')))
                    <img src="{{ asset('images/birthday-illustration.png') }}" alt="Birthday" class="w-24 h-24 object-contain" />
                @else
                    <div class="flex items-center justify-center w-20 h-20 text-4xl bg-white/10 rounded-2xl backdrop-blur-sm">
                        🎂
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-white">
                    Happy Birthday! 🎉
                </h3>
                <p class="mt-1 text-sm text-white/85">
                    Let's celebrate
                    @foreach($birthdayUsers as $index => $bUser)
                        <strong>{{ $bUser->name }}</strong>@if($index < $birthdayUsers->count() - 2), @elseif($index === $birthdayUsers->count() - 2) & @endif
                    @endforeach
                    today!
                </p>
            </div>

            {{-- Birthday avatars --}}
            <div class="flex items-center -space-x-3 shrink-0">
                @foreach($birthdayUsers as $bUser)
                @php
                    $av = $bUser->getAttributes()['avatar_url']
                        ?? ('https://ui-avatars.com/api/?name=' . urlencode($bUser->name) . '&size=128&background=' . substr(md5($bUser->id), 0, 6) . '&color=ffffff');
                @endphp
                <img src="{{ $av }}" alt="{{ $bUser->name }}"
                     class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-lg"
                     title="{{ $bUser->name }}" />
                @endforeach
            </div>
        </div>

        {{-- Decorative dots --}}
        <div class="absolute top-2 right-8 w-2 h-2 rounded-full bg-yellow-300/40"></div>
        <div class="absolute top-6 right-16 w-1.5 h-1.5 rounded-full bg-pink-300/50"></div>
        <div class="absolute bottom-3 right-24 w-2.5 h-2.5 rounded-full bg-white/20"></div>
        <div class="absolute top-4 left-2 w-1.5 h-1.5 rounded-full bg-yellow-200/30"></div>
    </div>
</x-filament::widget>
