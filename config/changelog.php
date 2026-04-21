<?php

/**
 * PMHelper changelog.
 *
 * New entries go at the TOP of the array — the Filament page renders them
 * in order. "current" version shown on login/reset-password pages and the
 * sidebar pill is just the first entry's "version".
 *
 * Entry schema:
 *   version     — semantic version, string (e.g. "1.1.0")
 *   released_at — Y-m-d string
 *   type        — "major" | "minor" | "patch" (controls badge color)
 *   title       — short headline shown as the card title
 *   highlights  — array of bullet strings shown below the title
 */
return [

    [
        'version' => '1.0.0',
        'released_at' => '2026-04-21',
        'type' => 'major',
        'title' => 'PMHelper v1.0 — first labeled release',
        'highlights' => [
            'Project management core — Projects, Tickets (Kanban + Scrum), Road map, Epics, Sprints',
            'Reports — Daily reports (auto-fill yesterday), Weekly reports (AI-assisted draft)',
            'Performance — OKR / KPI module with auto progress, self + supervisor review flow, team and company OKR pages, dashboard widget',
            'Team — 16 roles across departments, Organization chart, Request system with cross-team intake, birthday widget, gender-aware default avatars',
            'Discussions — threads per project / ticket, raise chat to discussion in one click',
            'Messenger — 1-on-1 team chat with real-time delivery (Pusher), attachments up to 30 MB, reactions, reply threads, edit / delete within 15 min, read receipts, typing indicator, presence',
            'Messenger statuses — Online (auto), Do Not Disturb, In a meeting (2h auto-clear), On lunch break (1h auto-clear), On leave (date range)',
            'Messenger fullpage view at /dm — WhatsApp-style two-panel layout, optional profile + shared-files panel, topbar chat icon with unread badge',
            'Video meetings — self-hosted Jitsi at meet.digicrats.com, no moderator lock, unlimited duration, opens in a new browser tab with auto-close on end',
            'Meeting transcription — Whisper base multilingual via Jigasi + Skynet, EN / ID selectable per meeting, transcript captured live on the starter side',
            'AI meeting summary — GPT-4o summarizes the transcript into Key points, Decisions, Action items, auto-posts to chat when the meeting ends',
            'Command palette — ⌘K / Ctrl+K global search across nav, tickets, projects, people, goals',
            'Sidebar — 5 accordion groups (Workspace / Reports / Performance / Team / Admin), version pill at the bottom links to this changelog',
            'Auth & UX — custom login UI, favicon, sidebar background blur, light / dark theme, image compression on upload, timezone auto-detection',
            'Mobile — Expo React Native companion app with Sanctum auth and 6 modules (projects, tickets, daily / weekly reports, discussions, users)',
            'Realtime everywhere — Kanban + Scrum ticket-move broadcasts, messenger events, status changes, all via Pusher',
            'Integrations — Google SMTP for transactional email, AI chatbot (gpt-4o) with project knowledge base, PDF docs, activity log with tree view',
            'Roles & audit — QA Reviewer role, global Activity Log page, ticket auto-link (#code becomes a badge in any message)',
            'Changelog page + version pill — this page exists now, and the v1.0.0 pill at the bottom of the sidebar opens it',
        ],
    ],

];
