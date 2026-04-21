<x-filament::page>
    <style>
        .changelog-header { margin-bottom: 24px; }
        .changelog-header-sub {
            font-size: 13px;
            color: #6b7280;
        }
        .dark .changelog-header-sub { color: #9ca3af; }
        .changelog-current-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #3b82f6;
            color: #fff;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
        .changelog-current-pill::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #34d399;
        }

        .changelog-timeline {
            position: relative;
            padding-left: 28px;
        }
        .changelog-timeline::before {
            content: '';
            position: absolute;
            left: 10px; top: 0; bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #3b82f6 0%, #e5e7eb 100%);
        }
        .dark .changelog-timeline::before {
            background: linear-gradient(to bottom, #3b82f6 0%, #374151 100%);
        }

        .changelog-entry {
            position: relative;
            padding-bottom: 28px;
        }
        .changelog-entry::before {
            content: '';
            position: absolute;
            left: -22px; top: 10px;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }
        .dark .changelog-entry::before {
            background: #1f2937;
        }

        .changelog-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .dark .changelog-card {
            background: #1f2937;
            border-color: #374151;
        }
        .changelog-card-head {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 8px;
        }
        .changelog-version {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }
        .dark .changelog-version { color: #f3f4f6; }

        .changelog-type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .changelog-type-major { background: #dbeafe; color: #1d4ed8; }
        .dark .changelog-type-major { background: rgba(29, 78, 216, 0.25); color: #93c5fd; }
        .changelog-type-minor { background: #dcfce7; color: #15803d; }
        .dark .changelog-type-minor { background: rgba(21, 128, 61, 0.25); color: #86efac; }
        .changelog-type-patch { background: #fef3c7; color: #a16207; }
        .dark .changelog-type-patch { background: rgba(161, 98, 7, 0.25); color: #fcd34d; }

        .changelog-date {
            font-size: 12px;
            color: #6b7280;
            margin-left: auto;
        }
        .dark .changelog-date { color: #9ca3af; }

        .changelog-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
        }
        .dark .changelog-title { color: #e5e7eb; }

        .changelog-highlights {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .changelog-highlights li {
            position: relative;
            padding-left: 22px;
            font-size: 13px;
            line-height: 1.55;
            color: #374151;
            margin-bottom: 6px;
        }
        .dark .changelog-highlights li { color: #d1d5db; }
        .changelog-highlights li::before {
            content: '✓';
            position: absolute;
            left: 0; top: 0;
            width: 16px; height: 16px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
        }
        .dark .changelog-highlights li::before {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }
    </style>

    <div class="changelog-header">
        <p class="changelog-header-sub">
            {{ __('Latest updates and improvements to PMHelper.') }}
            <span class="changelog-current-pill">v{{ $currentVersion }}</span>
        </p>
    </div>

    <div class="changelog-timeline">
        @foreach($entries as $entry)
            <div class="changelog-entry">
                <div class="changelog-card">
                    <div class="changelog-card-head">
                        <span class="changelog-version">v{{ $entry['version'] }}</span>
                        <span class="changelog-type-badge changelog-type-{{ $entry['type'] ?? 'minor' }}">
                            {{ $entry['type'] ?? 'minor' }}
                        </span>
                        <span class="changelog-date">
                            {{ \Carbon\Carbon::parse($entry['released_at'])->format('d M Y') }}
                        </span>
                    </div>
                    <div class="changelog-title">{{ $entry['title'] }}</div>
                    <ul class="changelog-highlights">
                        @foreach($entry['highlights'] as $hl)
                            <li>{{ $hl }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</x-filament::page>
