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
        'version' => '1.3.4',
        'released_at' => '2026-04-24',
        'type' => 'patch',
        'title' => 'Weekly Report — per-project scoping, no more cross-project ticket leak',
        'highlights' => [
            '🔒 Picking a Project on a Weekly Report now scopes the auto-summary to THAT project only — previously it still showed tickets from every project the author had access to, so people saw QOS tickets on a Website Digicrats report',
            '🛡️ Added an access check: if someone tries to scope to a project they are not a member or owner of, the auto-summary returns empty instead of silently falling back to all projects',
            '🔁 Switching the Project or Report Week dropdown now regenerates the Report Content on the fly (reactive) instead of leaving the previous week\'s/project\'s content stuck in the editor',
            '🧮 Tight per-project scope drops the generic "owner_id=me OR responsible_id=me" OR when a project is selected, so you only see tickets inside the chosen project — not your tickets from unrelated projects',
        ],
    ],

    [
        'version' => '1.3.3',
        'released_at' => '2026-04-24',
        'type' => 'patch',
        'title' => 'Ticket comments — vertical timeline, collapsible by default',
        'highlights' => [
            '🧵 Long comment threads no longer force endless scrolling — each comment collapses to a one-line header (author · time · preview) anchored to a round avatar node on a vertical rail',
            '▾ Click any header to expand the full rendered body, video attachments, and Raise / Edit / Delete actions; chevron rotates so you can see at a glance which rows are open',
            '📌 The newest comment stays expanded by default, so the common "open ticket → read latest reply" flow is unchanged',
            '🎨 Theme-aware rail (dark + light), action buttons only appear on the expanded row so collapsed rows read clean',
        ],
    ],

    [
        'version' => '1.3.2',
        'released_at' => '2026-04-24',
        'type' => 'patch',
        'title' => 'Messenger — sharper images, pasting screenshots finally works',
        'highlights' => [
            '🖼️ Uploaded images no longer halved and heavily compressed — resize ratio 0.45 → 0.9 and quality 85 → 90, screenshots look crisp at retina density (only affects new uploads; already-uploaded media stays as is)',
            '📐 Attachment thumbnail slot widened 240 → 380px so previews do not feel cramped; click still opens full-size lightbox',
            '📋 Paste (Cmd+V) screenshots or images in the composer — previously silently broken, now uploads on the fly',
            '🪟 Sequential pastes accumulate: take screenshot → paste → take another → paste → both stay attached until you send',
            '🗂️ Drag-and-drop multiple files from Finder into the composer; each chip has an × to remove before sending',
            '📨 Outbound email sender switched to noreply@digicrats.com (from bintang.jerry@digicrats.com) — all notifications, invites, daily/weekly reports, and AI replies now come from the shared mailbox',
        ],
    ],

    [
        'version' => '1.3.1',
        'released_at' => '2026-04-23',
        'type' => 'patch',
        'title' => 'MCP OAuth — Claude Desktop works out of the box',
        'highlights' => [
            '🔐 New OAuth 2.0 authorization server at /.well-known/oauth-authorization-server + /oauth/authorize + /oauth/token so Claude Desktop\'s Custom Connector UI works without pasting a token',
            '🪪 Dynamic client registration (RFC 7591) — Claude registers itself, you just click "Authorize" once in the browser',
            '🔒 PKCE-S256 enforced, auth codes live 5 min + single-use, redirect_uri exact-match, 90-day access tokens stored in the same Sanctum table as hand-issued tokens',
            '📚 Docs section 16 updated: Claude Desktop now uses OAuth (no manual token paste), Claude Code CLI still supports the --header flow',
            '🧩 Zero impact on existing login, Filament, Livewire, or mobile API — new tables oauth_clients / oauth_auth_codes only',
        ],
    ],

    [
        'version' => '1.3.0',
        'released_at' => '2026-04-23',
        'type' => 'minor',
        'title' => 'MCP integration — connect Claude to PMHelper',
        'highlights' => [
            '🤖 New MCP (Model Context Protocol) endpoint at /api/mcp so Claude Desktop and Claude Code can browse tickets, read comments, update statuses, and create daily reports as you — respecting your role and project access',
            '🔑 Per-user token management: open "MCP Tokens" in the sidebar, name each token after the device that will use it, copy the string once. Tokens inherit your permissions',
            '🧰 14 tools exposed: list_tickets / get_ticket / create_ticket / update_ticket_status / add_ticket_comment, list_discussions / get_discussion / add_discussion_comment, list_daily_reports / get_daily_report / create_daily_report, plus list_projects / list_ticket_statuses / list_users helpers',
            '📚 Documentation section 16 covers setup for Claude Code CLI and Claude Desktop custom connector, full tool catalog, and security notes',
            '📥 Kanban + Scrum + Tickets list: "Download JSON" action — pick statuses, optionally include comments, get a project-scoped snapshot without hitting the API',
            '🔍 Tickets table: multi-code search ("51, 52, 53") returns every match, bulk "Update status" action, and bulk actions rendered inline instead of behind the 3-dot menu',
            '🖼️ Messenger image lightbox: natural-size preview (scrollable when larger than the viewport) instead of shrinking everything to fit',
        ],
    ],

    [
        'version' => '1.2.0',
        'released_at' => '2026-04-22',
        'type' => 'minor',
        'title' => 'Shared documents panel on ticket sidebar',
        'highlights' => [
            '📎 New "Shared documents" section in the ticket detail sidebar — surfaces every link shared in the ticket description or in any comment, plus any video/file uploaded via the comment composer',
            '🎨 Each item carries an accent icon derived from its kind (docs blue, API emerald, staging amber, Figma pink, repo violet, Slack purple, video red, image amber, file slate) so you can scan the rail and spot the type you need at a glance',
            '👤 Shows the person who shared it + a relative timestamp ("Bintang Jerry · 2 hours ago") so you can trace who introduced which reference',
            '⚙️ Behind the scenes: new ticket_shared_resources table materialized by observers on Ticket, TicketComment, TicketCommentAttachment saves — no parsing cost at render time',
            '🧠 HTML-aware parser picks up both anchored links and bare URLs in comment bodies; deduped per ticket so the sidebar stays tidy',
            '🔄 Existing tickets/comments backfilled via `php artisan pmhelper:backfill-shared-resources`',
        ],
    ],

    [
        'version' => '1.1.2',
        'released_at' => '2026-04-22',
        'type' => 'patch',
        'title' => 'Command palette now searches Discussions',
        'highlights' => [
            '🔎 ⌘K / Ctrl+K palette picks up Discussions — search by title and jump straight to the discussion view',
            '🔐 Results respect the same permission scoping as the Discussions page: Super Admin & Stakeholder see everything, Project Managers see their projects, everyone else sees their own + project discussions',
            '💬 Shows "Discussion · {project name}" (or "Discussion · General" for unprojected) as the subtitle so you can tell similar titles apart',
        ],
    ],

    [
        'version' => '1.1.1',
        'released_at' => '2026-04-22',
        'type' => 'patch',
        'title' => 'Kanban project-link pills redesign',
        'highlights' => [
            '✨ Kanban header link-pills got a major glow-up — type-aware accent dots auto-detect URL kind (docs blue, API emerald, staging amber, Figma pink, repo violet, Slack purple) so you can scan the rail at a glance',
            '✨ Glass-surface treatment with soft gradient + inner highlight replaces the flat grey pill — same density, much more refined',
            '🪄 Hover motion: pill lifts 1px, border picks up the accent, the up-right arrow springs with a small overshoot, and a muted monospace hostname (docs.digicrats.com) fades in next to the label',
            '♿ Focus-visible ring for keyboard nav and full prefers-reduced-motion support — no animation if the OS says no',
            '🌗 Light-theme variant bundled — pills render cleanly under both .dark and default themes',
        ],
    ],

    [
        'version' => '1.1.0',
        'released_at' => '2026-04-21',
        'type' => 'minor',
        'title' => 'Meeting polish + Kanban project switcher + paste uploads',
        'highlights' => [
            '🎥 Meetings now open in a new browser tab (via /dm/meet/{slug}) and auto-close when the call ends — main PMHelper tab posts the "Meeting ended" marker and AI summary over a BroadcastChannel bridge',
            '🔄 Kanban & Scrum page headers now have a project-switcher dropdown — jump straight to any other project\'s board without going back to the list',
            '📋 Messenger composer now accepts screenshot paste (Cmd/Ctrl+V) and drag-drop anywhere in the composer area, not just inside the text field',
            '🚦 Moving a Kanban card to a status you do not have permission for now shows a toast and snaps the card back instead of crashing with a 500',
            '👍 "Waiting Approval" is now open to every role — it\'s a request, not an approval. Only Approved / Rejected stay locked to the business group',
            '🐛 Fix: meeting end flow was silently failing because the bridge was dispatching to the wrong Livewire component (chat-widget instead of messenger). Status and summary updates now reliably run',
            '🐛 Fix: Jitsi iframe overflowing upward and covering the language selector + End button — iframe now pinned inside its container',
            '🐛 Fix: shared-files paperclip on /dm used a wrong column name — no more 500 when opening the side panel',
            '🐛 Fix: duplicate message-read race condition that intermittently 500\'d the messenger',
        ],
    ],

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
