<?php

/**
 * PMHelper changelog.
 *
 * New entries go at the TOP of the array — the Filament page renders them
 * in order. "current" version shown on login/reset-password pages is just
 * the first entry's "version".
 *
 * Entry schema:
 *   version     — semantic version, string (e.g. "2.1.0")
 *   released_at — Y-m-d string
 *   type        — "major" | "minor" | "patch" (controls badge color)
 *   title       — short headline shown as the card title
 *   highlights  — array of bullet strings shown below the title
 */
return [

    [
        'version' => '2.1.0',
        'released_at' => '2026-04-21',
        'type' => 'major',
        'title' => 'Jitsi video meetings, self-hosted + AI summary',
        'highlights' => [
            'Self-hosted Jitsi Meet at meet.digicrats.com — no moderator lock, unlimited duration',
            'Start a video meeting from any 1-on-1 chat with the camera icon',
            'Meeting opens in a new browser tab, auto-closes on end',
            'Live transcript via Whisper (EN/ID selectable per meeting)',
            'GPT-4o auto-summarizes the meeting into key points, decisions, action items',
            'User status flips to "In a meeting" (red pulsing dot) automatically',
            'New "On lunch break" status with 1h auto-clear',
        ],
    ],

    [
        'version' => '2.0.0',
        'released_at' => '2026-04-21',
        'type' => 'major',
        'title' => 'Fullpage Messenger view + topbar shortcut',
        'highlights' => [
            'New /dm page — WhatsApp-style two-panel layout (conversation list + chat)',
            'Optional third panel for profile info and shared files',
            'Topbar chat icon with unread badge next to notifications bell',
            'Floating widget auto-hides on /dm to avoid double-mount',
            'Expand-to-fullview icon in floating widget headers',
        ],
    ],

    [
        'version' => '1.9.0',
        'released_at' => '2026-04-20',
        'type' => 'minor',
        'title' => 'Command palette + sidebar rebuild',
        'highlights' => [
            '⌘K / Ctrl+K global search palette — tickets, projects, people, goals',
            'Sidebar consolidated from 8 groups to 5 (Workspace, Reports, Performance, Team, Admin)',
            'Accordion nav: one group open at a time, collapsed by default',
            'Website Digicrats project scaffolded (1 epic, 3 sprints, 30 sub-tasks)',
        ],
    ],

    [
        'version' => '1.8.0',
        'released_at' => '2026-04-20',
        'type' => 'major',
        'title' => 'OKR / KPI module',
        'highlights' => [
            'Quarterly objectives + key results with auto progress calculation',
            'Self and supervisor review flow with notifications',
            'Personal / team / company OKR pages',
            'Dashboard widget + weekly-report integration',
            'Chatbot enriched with OKR context for goal-related questions',
        ],
    ],

    [
        'version' => '1.7.0',
        'released_at' => '2026-04-17',
        'type' => 'minor',
        'title' => 'Mobile app + realtime kanban',
        'highlights' => [
            'Expo React Native companion app with Sanctum auth',
            '6 modules: projects, tickets, daily/weekly reports, discussions, users',
            'Kanban + Scrum ticket-move broadcasts in realtime (Pusher)',
            'Column visibility toggle saved per project',
        ],
    ],

    [
        'version' => '1.6.0',
        'released_at' => '2026-04-14',
        'type' => 'minor',
        'title' => 'Messenger polish + discussion upgrades',
        'highlights' => [
            'Auto-link URLs in messages with link preview cards',
            'Video attachment support with .mov → .mp4 remuxing',
            'Raise chat thread to discussion in one click',
            'Discussion tag badges + audit trail fixes',
        ],
    ],

    [
        'version' => '1.5.0',
        'released_at' => '2026-04-13',
        'type' => 'minor',
        'title' => 'Activity log + QA reviewer',
        'highlights' => [
            'Global Activity Log page with tree view of all ticket changes',
            'QA reviewer role — dedicated workflow for test cycles',
            'Ticket auto-link (#ticket-code in any message becomes a badge)',
            'Messenger fixes (chatbot resize, comment rendering)',
        ],
    ],

    [
        'version' => '1.4.0',
        'released_at' => '2026-04-10',
        'type' => 'minor',
        'title' => 'Brand refresh + image pipeline',
        'highlights' => [
            'New login UI design',
            'Favicon + site logo',
            'Sidebar background blur',
            'Image compression on upload (45% resize for attachments)',
            'Light/dark theme setting',
        ],
    ],

    [
        'version' => '1.3.0',
        'released_at' => '2026-04-07',
        'type' => 'major',
        'title' => 'Messenger launch',
        'highlights' => [
            '1-on-1 team chat with real-time delivery (Pusher)',
            'Attachments (images, docs up to 30 MB), reactions, reply threads',
            'Manual status — Do Not Disturb / In a meeting / On leave',
            'Read receipts, typing indicator, edit/delete within 15 min',
            'Auto-presence via Pusher presence channel',
        ],
    ],

    [
        'version' => '1.2.0',
        'released_at' => '2026-04-07',
        'type' => 'minor',
        'title' => 'Roles, Requests, and people features',
        'highlights' => [
            '16 distinct roles across departments',
            'Request system — cross-team work intake with review flow',
            'Organization chart page',
            'Gender-aware default avatars',
            'Birthday tracker widget',
            'Timezone auto-detection on login',
        ],
    ],

    [
        'version' => '1.1.0',
        'released_at' => '2026-04-06',
        'type' => 'minor',
        'title' => 'Daily reports + discussions',
        'highlights' => [
            'Daily Reports module with auto-fill from yesterday',
            'Discussion threads per project/ticket',
            'Gmail SMTP for transactional emails',
            'Queue worker for background jobs (Laravel scheduler via cron)',
        ],
    ],

    [
        'version' => '1.0.0',
        'released_at' => '2026-03-27',
        'type' => 'major',
        'title' => 'Initial release',
        'highlights' => [
            'Projects, tickets (Kanban + Scrum), road map',
            'Weekly reports with AI-assisted draft',
            'Basic messenger chatbot',
            'Dashboard widgets, activity feed, search',
        ],
    ],

];
