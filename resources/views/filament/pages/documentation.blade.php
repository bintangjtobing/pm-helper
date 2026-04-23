<x-filament::page>
@php
// Reusable styles
$h2 = 'font-size:20px;font-weight:700;color:#f3f4f6;margin:0 0 20px 0;padding-bottom:12px;border-bottom:1px solid #374151;';
$h3 = 'font-size:15px;font-weight:600;color:#e5e7eb;margin:24px 0 10px 0;';
$p = 'font-size:14px;line-height:1.75;color:#9ca3af;margin:0 0 12px 0;';
$li = 'font-size:13px;line-height:1.7;color:#9ca3af;margin:0 0 6px 0;padding-left:6px;';
$card = 'background:#1f2937;border:1px solid #374151;border-radius:8px;padding:24px 28px;margin-bottom:16px;';
$badge = 'display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;';
$code = 'padding:2px 6px;border-radius:4px;background:#374151;color:#60a5fa;font-size:12px;font-family:monospace;';
$flow = 'padding:14px 20px;border-radius:6px;background:#111827;border:1px solid #1f2937;font-size:13px;color:#d1d5db;margin:10px 0 14px 0;';
$tbl = 'width:100%;border-collapse:collapse;font-size:13px;margin:10px 0 14px 0;';
$th = 'text-align:left;padding:8px 12px;border-bottom:2px solid #374151;color:#e5e7eb;font-weight:600;';
$td = 'padding:7px 12px;border-bottom:1px solid #1f2937;color:#9ca3af;';
@endphp

<div x-data="{
    search: '',
    openFaq: null,
    get filteredSections() {
        if (!this.search || this.search.length < 2) return [];
        const q = this.search.toLowerCase();
        const results = [];
        document.querySelectorAll('[data-doc-section]').forEach(el => {
            const text = el.textContent.toLowerCase();
            const title = el.dataset.docSection;
            if (text.includes(q) || title.toLowerCase().includes(q)) results.push({ title, id: el.id });
        });
        return results;
    }
}" style="max-width:900px;margin:0 auto;">

    {{-- Search --}}
    <div style="position:sticky;top:0;z-index:30;padding:8px 0 16px 0;background:rgba(17,24,39,0.97);backdrop-filter:blur(8px);">
        <div style="position:relative;">
            <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Search documentation..."
                style="width:100%;padding:10px 16px 10px 40px;font-size:14px;border-radius:8px;background:#1f2937;border:1px solid #374151;color:#f3f4f6;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#374151'" />
        </div>
        <template x-if="search.length > 1 && filteredSections.length > 0">
            <div style="margin-top:8px;padding:6px;border-radius:8px;background:#1f2937;border:1px solid #374151;max-height:200px;overflow-y:auto;">
                <template x-for="result in filteredSections" :key="result.id">
                    <a :href="'#' + result.id" @click="search = ''" style="display:block;padding:7px 12px;font-size:13px;color:#d1d5db;border-radius:6px;text-decoration:none;" onmouseover="this.style.background='#374151';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='#d1d5db'" x-text="result.title"></a>
                </template>
            </div>
        </template>
    </div>

    {{-- TOC --}}
    <div style="{{ $card }}">
        <h2 style="font-size:15px;font-weight:700;color:#f3f4f6;margin:0 0 14px 0;">Table of Contents</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 20px;">
            @php $toc = [['getting-started','Getting Started'],['dashboard','Dashboard'],['projects','Projects'],['tickets','Tickets & Request System'],['kanban','Kanban Board'],['comments','Comments & Mentions'],['reports','Daily & Weekly Reports'],['discussions','Discussions'],['timesheet','Timesheet & Time Logging'],['notifications','Notifications'],['roles','Roles & Permissions'],['organization','Organization & Departments'],['profile','Profile Settings'],['feedback','Customer Feedback'],['performance','Performance (OKR & KPI)'],['mcp','MCP Integration (Claude)'],['writing-rules','Writing Guidelines'],['dos-donts',"Do's & Don'ts"],['faq','FAQ']]; @endphp
            @foreach($toc as $i => $item)
            <a href="#{{ $item[0] }}" style="display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:5px;text-decoration:none;font-size:13px;color:#9ca3af;" onmouseover="this.style.background='#374151';this.style.color='#f3f4f6'" onmouseout="this.style.background='transparent';this.style.color='#9ca3af'">
                <span style="min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;border-radius:4px;background:#374151;color:#6b7280;font-size:10px;font-weight:700;">{{ $i + 1 }}</span>
                {{ $item[1] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- ===== SECTIONS ===== --}}

    {{-- 1. GETTING STARTED --}}
    <div style="{{ $card }}" id="getting-started" data-doc-section="Getting Started">
        <h2 style="{{ $h2 }}">1. Getting Started</h2>

        <h3 style="{{ $h3 }}">What is PM Helper?</h3>
        <p style="{{ $p }}">PM Helper is a comprehensive project management platform built for digital marketing companies. It combines task tracking, team reporting, client feedback, internal discussions, time logging, organization management, and real-time notifications in one unified system.</p>

        <h3 style="{{ $h3 }}">First Time Login</h3>
        <ol style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">You will receive an email invitation with a <strong style="color:#e5e7eb;">verification link</strong></li>
            <li style="{{ $li }}">Click <strong style="color:#e5e7eb;">"Verify My Account"</strong> to set your password</li>
            <li style="{{ $li }}">Login at <strong style="color:#f3f4f6;">pm.digicrats.com</strong></li>
            <li style="{{ $li }}">Complete your profile: upload photo, set gender, department, position, timezone</li>
            <li style="{{ $li }}">Your timezone is auto-detected from your browser on first login</li>
        </ol>

        <h3 style="{{ $h3 }}">Navigation</h3>
        <p style="{{ $p }}">The left sidebar contains menu items grouped by category. Your visible menus depend on your assigned role:</p>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Group</th><th style="{{ $th }}">Contains</th></tr>
            <tr><td style="{{ $td }}">Management</td><td style="{{ $td }}">Projects, Tickets, Project Audit, Customer Feedback</td></tr>
            <tr><td style="{{ $td }}">Reports</td><td style="{{ $td }}">Daily Reports, Weekly Reports, Discussions</td></tr>
            <tr><td style="{{ $td }}">Organization</td><td style="{{ $td }}">Departments, Positions, Org Chart</td></tr>
            <tr><td style="{{ $td }}">Referential</td><td style="{{ $td }}">Statuses, Types, Priorities, Activities</td></tr>
            <tr><td style="{{ $td }}">Settings</td><td style="{{ $td }}">Motivational Quotes, Birthday Wishes (Super Admin)</td></tr>
        </table>
    </div>

    {{-- 2. DASHBOARD --}}
    <div style="{{ $card }}" id="dashboard" data-doc-section="Dashboard">
        <h2 style="{{ $h2 }}">2. Dashboard</h2>
        <p style="{{ $p }}">The dashboard is your home screen showing real-time data through multiple widgets:</p>

        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Widget</th><th style="{{ $th }}">Description</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Greeting</strong></td><td style="{{ $td }}">Personalized greeting (Good Morning/Afternoon/Evening/Night) with random motivational quote and current date.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Project Audit</strong></td><td style="{{ $td }}">Total projects, health score (0-100), overdue count, and completion rate.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Birthday Banner</strong></td><td style="{{ $td }}">Appears when a team member has a birthday. Shows animated balloons, personalized age-based wish, and illustration. Different message for the birthday person vs. teammates.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Reports Overview</strong></td><td style="{{ $td }}">Daily reports today (X/total), weekly reports this week, pending reviews, and activity streak percentage.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Discussions</strong></td><td style="{{ $td }}">Status counters (Open, In Discussion, Resolved), high priority alerts, and latest active topics.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Activity Feed</strong></td><td style="{{ $td }}">Live feed of status changes, comments, and report submissions from your projects.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Favorite Projects</strong></td><td style="{{ $td }}">Quick access cards for your starred projects with ticket count and progress.</td></tr>
        </table>
    </div>

    {{-- 3. PROJECTS --}}
    <div style="{{ $card }}" id="projects" data-doc-section="Projects">
        <h2 style="{{ $h2 }}">3. Projects</h2>
        <p style="{{ $p }}">Projects are the top-level container for all work. Each project has its own tickets, sprints, board, and team members.</p>

        <h3 style="{{ $h3 }}">Creating a Project</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Name & Description</strong> - Project title and summary</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Ticket Prefix</strong> - e.g. "QOS" generates ticket codes QOS-1, QOS-2, etc.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Team Members</strong> - Assign who can view and work on the project</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Project Type</strong> - Kanban, Scrum, or custom workflow</li>
        </ul>

        <h3 style="{{ $h3 }}">Project Views</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Details</strong> - Project info, members, goals, settings</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Board</strong> - Kanban board with drag & drop</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Tickets</strong> - Full list view with filters and search</li>
        </ul>
    </div>

    {{-- 4. TICKETS & REQUEST --}}
    <div style="{{ $card }}" id="tickets" data-doc-section="Tickets and Request System">
        <h2 style="{{ $h2 }}">4. Tickets & Request System</h2>

        <h3 style="{{ $h3 }}">Ticket Types</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Type</th><th style="{{ $th }}">Purpose</th><th style="{{ $th }}">Who Creates</th></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#8b5cf620;color:#8b5cf6;">Request</span></td><td style="{{ $td }}">Internal request from any department. Requires PM approval.</td><td style="{{ $td }}">All roles</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#3b82f620;color:#3b82f6;">Feature</span></td><td style="{{ $td }}">New functionality to build</td><td style="{{ $td }}">Delivery roles</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#22c55e20;color:#22c55e;">Task</span></td><td style="{{ $td }}">General work item</td><td style="{{ $td }}">Delivery roles</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#ef444420;color:#ef4444;">Bug</span></td><td style="{{ $td }}">Something broken that needs fixing</td><td style="{{ $td }}">Delivery + QA</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#f59e0b20;color:#f59e0b;">Improvement</span></td><td style="{{ $td }}">Enhancement to existing feature</td><td style="{{ $td }}">Delivery roles</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $badge }}background:#f9731620;color:#f97316;">Hotfix</span></td><td style="{{ $td }}">Urgent production fix</td><td style="{{ $td }}">Delivery roles</td></tr>
        </table>

        <h3 style="{{ $h3 }}">Request Workflow</h3>
        <p style="{{ $p }}">All roles can create a <strong style="color:#8b5cf6;">Request</strong> ticket. This goes through an approval process:</p>
        <div style="{{ $flow }}">
            <span style="{{ $badge }}background:#8b5cf620;color:#8b5cf6;">Request</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#f59e0b20;color:#f59e0b;">Under Review</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#22c55e20;color:#22c55e;">Approved</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#3b82f620;color:#3b82f6;">Convert to Task/Feature/Bug</span>
            <br><span style="display:inline-block;margin:8px 0 0 154px;color:#4b5563;">&#8600;</span>
            <span style="{{ $badge }}background:#ef444420;color:#ef4444;">Rejected</span>
            <span style="color:#6b7280;font-size:11px;margin-left:4px;">(with mandatory reason)</span>
        </div>

        <h3 style="{{ $h3 }}">Required Fields for Request</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Field</th><th style="{{ $th }}">Description</th><th style="{{ $th }}">Required</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Objective</strong></td><td style="{{ $td }}">What do you want to achieve?</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Expected Outcome</strong></td><td style="{{ $td }}">What is the expected result if fulfilled?</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Impact</strong></td><td style="{{ $td }}">Low / Medium / High / Critical</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Department</strong></td><td style="{{ $td }}">Which department is requesting</td><td style="{{ $td }}">Optional</td></tr>
        </table>

        <h3 style="{{ $h3 }}">PM/Executive Actions</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><span style="{{ $badge }}background:#f59e0b20;color:#f59e0b;">Start Review</span> - Mark request as being reviewed</li>
            <li style="{{ $li }}"><span style="{{ $badge }}background:#22c55e20;color:#22c55e;">Approve</span> - Approve the request for execution</li>
            <li style="{{ $li }}"><span style="{{ $badge }}background:#ef444420;color:#ef4444;">Reject</span> - Reject with mandatory reason</li>
            <li style="{{ $li }}"><span style="{{ $badge }}background:#3b82f620;color:#3b82f6;">Convert</span> - Change type to Task/Feature/Bug and set delivery status</li>
        </ul>
    </div>

    {{-- 5. KANBAN --}}
    <div style="{{ $card }}" id="kanban" data-doc-section="Kanban Board">
        <h2 style="{{ $h2 }}">5. Kanban Board</h2>
        <p style="{{ $p }}">Visual board where tickets are organized by status columns.</p>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Each column represents a ticket status (e.g. To Do, In Progress, Done)</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Drag and drop</strong> ticket cards between columns to update status</li>
            <li style="{{ $li }}">Click a ticket card to open its detail view</li>
            <li style="{{ $li }}">Cards show: ticket code, name, assignee avatar, priority indicator</li>
        </ul>
    </div>

    {{-- 6. COMMENTS --}}
    <div style="{{ $card }}" id="comments" data-doc-section="Comments and Mentions">
        <h2 style="{{ $h2 }}">6. Comments & Mentions</h2>

        <h3 style="{{ $h3 }}">Rich Text Comments</h3>
        <p style="{{ $p }}">The comment editor supports: <strong style="color:#e5e7eb;">bold</strong>, <em>italic</em>, lists, links, code blocks, and image uploads.</p>

        <h3 style="{{ $h3 }}">@Mention System</h3>
        <p style="{{ $p }}">Type <code style="{{ $code }}">@</code> followed by a name or username to mention someone:</p>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">A dropdown appears with matching users (avatar + name + username)</li>
            <li style="{{ $li }}">Navigate with <code style="{{ $code }}">Arrow keys</code>, select with <code style="{{ $code }}">Enter</code> or <code style="{{ $code }}">Tab</code></li>
            <li style="{{ $li }}">Mentioned users receive <strong style="color:#e5e7eb;">email + bell notification</strong></li>
            <li style="{{ $li }}">Mentions render as <span style="background:rgba(59,130,246,0.15);color:#3b82f6;padding:1px 6px;border-radius:4px;font-size:12px;">@username</span> blue badges</li>
            <li style="{{ $li }}">Works in both <strong style="color:#e5e7eb;">ticket comments</strong> and <strong style="color:#e5e7eb;">discussion replies</strong></li>
        </ul>

        <h3 style="{{ $h3 }}">Time Logging via /spend</h3>
        <p style="{{ $p }}">Log time directly in comments using the <code style="{{ $code }}">/spend</code> command:</p>
        <div style="{{ $flow }}font-family:monospace;">
            /spend 2h 30m - Worked on API integration<br>
            /spend 45m - Quick bug fix<br>
            /spend 1h - Code review
        </div>

        <h3 style="{{ $h3 }}">Deleting Comments</h3>
        <p style="{{ $p }}">You can <strong style="color:#e5e7eb;">always delete your own comments</strong>. Super Admins can delete any comment.</p>
    </div>

    {{-- 7. REPORTS --}}
    <div style="{{ $card }}" id="reports" data-doc-section="Daily and Weekly Reports">
        <h2 style="{{ $h2 }}">7. Daily & Weekly Reports</h2>

        <h3 style="{{ $h3 }}">Daily Reports</h3>
        <p style="{{ $p }}">Standup-style daily reporting with three sections:</p>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Section</th><th style="{{ $th }}">Purpose</th><th style="{{ $th }}">Required</th></tr>
            <tr><td style="{{ $td }}"><span style="color:#22c55e;">What was accomplished</span></td><td style="{{ $td }}">What you worked on and completed today</td><td style="{{ $td }}">Yes</td></tr>
            <tr><td style="{{ $td }}"><span style="color:#3b82f6;">Plans for tomorrow</span></td><td style="{{ $td }}">What you plan to work on next</td><td style="{{ $td }}">Yes</td></tr>
            <tr><td style="{{ $td }}"><span style="color:#ef4444;">Blockers / Issues</span></td><td style="{{ $td }}">Any impediments or problems</td><td style="{{ $td }}">Optional</td></tr>
        </table>

        <h3 style="{{ $h3 }}">Weekly Reports</h3>
        <p style="{{ $p }}">Comprehensive weekly summary with auto-generated progress data from ticket activity. Supports file attachments (PDF/DOCX) and AI-powered metric extraction.</p>

        <h3 style="{{ $h3 }}">Report Workflow</h3>
        <div style="{{ $flow }}">
            <span style="{{ $badge }}background:#6b728020;color:#9ca3af;">Draft</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#3b82f620;color:#3b82f6;">Submitted</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#22c55e20;color:#22c55e;">Acknowledged</span>
            <span style="color:#6b7280;font-size:11px;margin-left:8px;">by PM / Executive</span>
        </div>
        <p style="{{ $p }}">When submitted, <strong style="color:#e5e7eb;">PM, Executive, and Stakeholder</strong> roles receive email notification. Markdown is fully supported in report content.</p>
    </div>

    {{-- 8. DISCUSSIONS --}}
    <div style="{{ $card }}" id="discussions" data-doc-section="Discussions">
        <h2 style="{{ $h2 }}">8. Discussions</h2>
        <p style="{{ $p }}">Thread-based discussion board for topics that need to be recorded and decided on. Not a ticket, but a structured conversation.</p>

        <h3 style="{{ $h3 }}">Creating a Discussion</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Title</strong> - Clear topic title</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Description</strong> - Supports Markdown (headings, tables, code blocks)</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Priority</strong> - Low / Medium / High</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Project</strong> - Optional, link to a specific project</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Linked Ticket</strong> - Optional, reference a related ticket</li>
        </ul>

        <h3 style="{{ $h3 }}">Status Flow</h3>
        <div style="{{ $flow }}">
            <span style="{{ $badge }}background:#3b82f620;color:#3b82f6;">Open</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#f59e0b20;color:#f59e0b;">In Discussion</span>
            <span style="color:#6b7280;font-size:11px;">(auto on first reply)</span>
            <span style="color:#4b5563;margin:0 6px;">&#8594;</span>
            <span style="{{ $badge }}background:#22c55e20;color:#22c55e;">Resolved</span>
            <span style="color:#4b5563;margin:0 3px;">/</span>
            <span style="{{ $badge }}background:#6b728020;color:#9ca3af;">Closed</span>
        </div>
    </div>

    {{-- 9. TIMESHEET --}}
    <div style="{{ $card }}" id="timesheet" data-doc-section="Timesheet and Time Logging">
        <h2 style="{{ $h2 }}">9. Timesheet & Time Logging</h2>
        <p style="{{ $p }}">Two ways to log time on tickets:</p>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Method</th><th style="{{ $th }}">How</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Log Time button</strong></td><td style="{{ $td }}">On ticket detail page, click "Log time" &#8594; enter hours, minutes, activity type, and optional description</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">/spend command</strong></td><td style="{{ $td }}">In a comment: <code style="{{ $code }}">/spend 2h 30m</code> - auto-logged and removed from comment text</td></tr>
        </table>
        <p style="{{ $p }}">PM and Executive can view the <strong style="color:#e5e7eb;">Timesheet Dashboard</strong> with aggregated data per user, project, and time period. CSV export is available per ticket.</p>
    </div>

    {{-- 10. NOTIFICATIONS --}}
    <div style="{{ $card }}" id="notifications" data-doc-section="Notifications">
        <h2 style="{{ $h2 }}">10. Notifications</h2>
        <p style="{{ $p }}">Two notification channels:</p>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Bell Icon</strong> (top-right) - Real-time via Pusher WebSocket. Updated instantly.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Email</strong> - Sent to your primary email + secondary CC email (if configured in profile).</li>
        </ul>

        <h3 style="{{ $h3 }}">Notification Triggers</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Event</th><th style="{{ $th }}">Who Gets Notified</th></tr>
            <tr><td style="{{ $td }}">Ticket created</td><td style="{{ $td }}">Project members</td></tr>
            <tr><td style="{{ $td }}">Ticket status changed</td><td style="{{ $td }}">Owner + responsible + subscribers</td></tr>
            <tr><td style="{{ $td }}">New comment</td><td style="{{ $td }}">Ticket subscribers</td></tr>
            <tr><td style="{{ $td }}">@Mentioned</td><td style="{{ $td }}">Mentioned user</td></tr>
            <tr><td style="{{ $td }}">Daily/Weekly report submitted</td><td style="{{ $td }}">PM + Executive + Stakeholder</td></tr>
            <tr><td style="{{ $td }}">Discussion created</td><td style="{{ $td }}">Project members + Super Admin</td></tr>
            <tr><td style="{{ $td }}">Discussion reply</td><td style="{{ $td }}">Author + all participants</td></tr>
            <tr><td style="{{ $td }}">Feedback submitted/updated</td><td style="{{ $td }}">PM + Super Admin</td></tr>
            <tr><td style="{{ $td }}">Feedback converted to ticket</td><td style="{{ $td }}">Feedback submitter</td></tr>
            <tr><td style="{{ $td }}">Account created</td><td style="{{ $td }}">New user (verification email)</td></tr>
        </table>
    </div>

    {{-- 11. ROLES --}}
    <div style="{{ $card }}" id="roles" data-doc-section="Roles and Permissions">
        <h2 style="{{ $h2 }}">11. Roles & Permissions</h2>
        <p style="{{ $p }}">Each user is assigned <strong style="color:#e5e7eb;">one role</strong> that determines system access. Roles are <strong style="color:#e5e7eb;">independent of department/position</strong>.</p>

        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Role</th><th style="{{ $th }}">Layer</th><th style="{{ $th }}">Key Capabilities</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#ef4444;">Super Admin</strong></td><td style="{{ $td }}">System</td><td style="{{ $td }}">Full access to everything. Manages roles, settings, quotes.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#f87171;">Executive</strong></td><td style="{{ $td }}">Strategic</td><td style="{{ $td }}">View all data. Approve/reject/convert requests. No operational work.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#3b82f6;">Project Manager</strong></td><td style="{{ $td }}">Delivery</td><td style="{{ $td }}">Full project/ticket/sprint control. Approve requests. View timesheet.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#22c55e;">Developer</strong></td><td style="{{ $td }}">Execution</td><td style="{{ $td }}">Create/update tickets. Comment. Log time.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#f59e0b;">QA / Tester</strong></td><td style="{{ $td }}">Quality</td><td style="{{ $td }}">Update ticket status. Create bugs. Manage feedback.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#8b5cf6;">DevOps</strong></td><td style="{{ $td }}">Deployment</td><td style="{{ $td }}">Update tickets/status. View timesheet dashboard.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#06b6d4;">Account Manager</strong></td><td style="{{ $td }}">Client</td><td style="{{ $td }}">Manage projects. Create tickets. Handle client feedback.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#f97316;">Sales</strong></td><td style="{{ $td }}">Revenue</td><td style="{{ $td }}">View projects. Create requests. Manage feedback.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#a855f7;">Digital Marketer</strong></td><td style="{{ $td }}">Delivery Support</td><td style="{{ $td }}">View/update tickets. Comment on work.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#c084fc;">Content Writer</strong></td><td style="{{ $td }}">Delivery Support</td><td style="{{ $td }}">View/update tickets. Comment on content work.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#34d399;">Designer</strong></td><td style="{{ $td }}">Creative</td><td style="{{ $td }}">View/update tickets (own tasks). Comment.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#22d3ee;">Data Analyst</strong></td><td style="{{ $td }}">Data</td><td style="{{ $td }}">View dashboards, timesheet, feedback. Create requests.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#94a3b8;">Operations</strong></td><td style="{{ $td }}">Ops</td><td style="{{ $td }}">Create/update tickets. Manage sprints and activities.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#10b981;">HR</strong></td><td style="{{ $td }}">Internal</td><td style="{{ $td }}">Manage users/roles. Create requests. No project access.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#6b7280;">Finance</strong></td><td style="{{ $td }}">Internal</td><td style="{{ $td }}">View timesheet/users. Create requests.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#fb923c;">Stakeholder</strong></td><td style="{{ $td }}">View Only</td><td style="{{ $td }}">View projects/tickets. Submit feedback. View reports.</td></tr>
        </table>

        <h3 style="{{ $h3 }}">Request Permissions Matrix</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Role Group</th><th style="{{ $th }}">Create Request</th><th style="{{ $th }}">Create Task/Bug</th><th style="{{ $th }}">Approve/Reject</th><th style="{{ $th }}">Convert</th></tr>
            <tr><td style="{{ $td }}">HR, Finance, Sales</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td></tr>
            <tr><td style="{{ $td }}">Marketer, Writer, Designer</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td></tr>
            <tr><td style="{{ $td }}">Dev, QA, DevOps, Ops</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td><td style="{{ $td }}"><span style="color:#ef4444;">No</span></td></tr>
            <tr><td style="{{ $td }}">PM, Executive, Super Admin</td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td><td style="{{ $td }}"><span style="color:#22c55e;">Yes</span></td></tr>
        </table>
    </div>

    {{-- 12. ORGANIZATION --}}
    <div style="{{ $card }}" id="organization" data-doc-section="Organization and Departments">
        <h2 style="{{ $h2 }}">12. Organization & Departments</h2>

        <h3 style="{{ $h3 }}">Structure</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">13 Departments</strong> - 10 Core + 3 Advanced (managed by Super Admin)</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">50+ Positions</strong> - Linked to departments with levels: Staff, Lead, Manager, Head, C-Level</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Supervisor Chain</strong> - Each user can have a direct supervisor, creating the org hierarchy</li>
        </ul>

        <h3 style="{{ $h3 }}">Organization Chart (two views)</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Chart View</strong> - Visual tree with photos, colored position badges, connecting lines based on supervisor relationships</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Data View</strong> - Department cards showing all positions and assigned members</li>
        </ul>

        <div style="{{ $flow }}">
            <strong style="color:#e5e7eb;">Important:</strong> Department and Position are for <span style="color:#f59e0b;">organizational identity</span> (org chart, profile). They do NOT affect permissions. Only your <span style="color:#3b82f6;">Role</span> determines system access.
        </div>
    </div>

    {{-- 13. PROFILE --}}
    <div style="{{ $card }}" id="profile" data-doc-section="Profile Settings">
        <h2 style="{{ $h2 }}">13. Profile Settings</h2>
        <p style="{{ $p }}">Access via your avatar (top-right corner) &#8594; click your name.</p>

        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Field</th><th style="{{ $th }}">Description</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Profile Picture</strong></td><td style="{{ $td }}">Upload JPG/PNG/WebP (auto-cropped 200x200). If not uploaded, a gender-based cartoon avatar is assigned.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Name</strong></td><td style="{{ $td }}">Your full display name</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Username</strong></td><td style="{{ $td }}">For @mentions (letters, numbers, underscores only)</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Email</strong></td><td style="{{ $td }}">Primary email. Changing requires re-verification.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Secondary Email (CC)</strong></td><td style="{{ $td }}">Optional CC address for all email notifications</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Gender</strong></td><td style="{{ $td }}">Male / Female / Other. Triggers default avatar assignment.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Birthday</strong></td><td style="{{ $td }}">For birthday celebration banner on your special day</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Department & Position</strong></td><td style="{{ $td }}">For organization chart display</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Direct Supervisor</strong></td><td style="{{ $td }}">Creates hierarchy in org chart (supervisor &#8594; you)</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Timezone</strong></td><td style="{{ $td }}">Auto-detected from browser. Manually overridable. All times displayed in your timezone.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Language</strong></td><td style="{{ $td }}">UI language preference</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Default Project</strong></td><td style="{{ $td }}">Quick access project shortcut</td></tr>
        </table>
    </div>

    {{-- 14. FEEDBACK --}}
    <div style="{{ $card }}" id="feedback" data-doc-section="Customer Feedback">
        <h2 style="{{ $h2 }}">14. Customer Feedback</h2>
        <p style="{{ $p }}">Collect and track client feedback with full traceability to tickets:</p>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Stakeholders and QA can <strong style="color:#e5e7eb;">submit feedback</strong> linked to a project</li>
            <li style="{{ $li }}">PM and Account Managers can <strong style="color:#e5e7eb;">update status</strong> and <strong style="color:#e5e7eb;">convert to ticket</strong></li>
            <li style="{{ $li }}">Converted feedback auto-links to the created ticket for traceability</li>
            <li style="{{ $li }}">All updates trigger email notifications to the original submitter</li>
        </ul>
    </div>

    {{-- 15. PERFORMANCE (OKR & KPI) --}}
    <div style="{{ $card }}" id="performance" data-doc-section="Performance OKR KPI Goals Objectives Key Results Review Self Supervisor">
        <h2 style="{{ $h2 }}">15. Performance (OKR &amp; KPI)</h2>

        <h3 style="{{ $h3 }}">What are OKR and KPI?</h3>
        <p style="{{ $p }}">PM Helper has a unified <strong style="color:#e5e7eb;">Performance</strong> module for tracking goals across the company. Two goal types share the same data model:</p>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">OKR (Objective &amp; Key Results)</strong> — qualitative Objectives broken into 3–5 measurable Key Results. Time-boxed per quarter. Answers <em style="color:#d1d5db;">"what are we trying to achieve, and how will we know we succeeded?"</em></li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">KPI (Key Performance Indicator)</strong> — ongoing quantitative metrics, usually monthly. Answers <em style="color:#d1d5db;">"what numbers should stay healthy?"</em></li>
        </ul>
        <p style="{{ $p }}">Both are transparent by default (any signed-in user can view Company + Department OKRs) and support cascade hierarchy: Company &rarr; Department &rarr; Individual.</p>

        <h3 style="{{ $h3 }}">Who can create and review?</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Role</th><th style="{{ $th }}">Can do</th></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Super Admin</strong></td><td style="{{ $td }}">Create / edit / delete any Goal at any level (Company, Department, Individual). Create Periods. Generate Reviews. See every review.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Supervisor</strong> (anyone with direct reports via <span style="{{ $code }}">users.supervisor_id</span>)</td><td style="{{ $td }}">See Team OKR + Team Reviews for their direct reports. Finalize submitted reviews. Add per-KR notes + overall feedback.</td></tr>
            <tr><td style="{{ $td }}"><strong style="color:#e5e7eb;">Individual contributor</strong></td><td style="{{ $td }}">See My OKR + My Review. Update KR progress manually or through Weekly Reports. Submit self-review, acknowledge or dispute the final result.</td></tr>
        </table>
        <div style="{{ $flow }}"><strong style="color:#fcd34d;">Current policy:</strong> only Super Admin creates Goals in the admin panel. Individual contributors do not directly author their own OKRs in the system; instead they discuss their proposed OKRs with their supervisor, and a Super Admin (or the supervisor when they also hold Super Admin) records them under the correct owner. This keeps weights auditable and prevents unbudgeted self-inflation. If you need a looser model later, tell us.</div>

        <h3 style="{{ $h3 }}">The goal hierarchy</h3>
        <div style="{{ $flow }}">
            <strong style="color:#e5e7eb;">Company</strong> (e.g. "Reach $1M ARR")<br>
            &nbsp;&nbsp;&darr; cascades to &rarr;<br>
            <strong style="color:#e5e7eb;">Department</strong> (e.g. Marketing: "Drive 50% of pipeline")<br>
            &nbsp;&nbsp;&darr; cascades to &rarr;<br>
            <strong style="color:#e5e7eb;">Individual</strong> (e.g. Bintang: "Ship GA4 migration")
        </div>
        <p style="{{ $p }}">Each Individual Objective optionally links to a parent Company or Department Objective through the <span style="{{ $code }}">parent_id</span> field. The cascade is for visibility — weights and scoring are still computed per level.</p>

        <h3 style="{{ $h3 }}">Weight rules (critical)</h3>
        <p style="{{ $p }}">Weights exist at two levels, and both must sum to exactly 100%:</p>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Per user, per period:</strong> the sum of all your Individual Objectives' weights = 100%. Example: 4 Objectives at 25% each, or 5 Objectives at 25% / 25% / 20% / 20% / 10%.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Per Objective:</strong> the sum of all its Key Results' weights = 100%. Example: 4 KRs at 30% / 25% / 25% / 20%.</li>
        </ul>
        <p style="{{ $p }}">The weight budget indicator in the form shows the current total and flags over- or under-allocation. Under-allocated drafts are allowed, but a save that exceeds 100% is rejected at the database level.</p>

        <h3 style="{{ $h3 }}">How to write a good Objective</h3>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Qualitative + aspirational.</strong> "Strengthen organic conversion tracking" — not "Implement GA4 event X".</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Time-boxed to the period.</strong> Quarterly for OKR Objectives. Don't carry the same Objective across three quarters without rewording.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">3–5 KRs per Objective.</strong> Fewer and it's underspecified; more and it's a todo list.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Align upward.</strong> Every Individual Objective should trace back to a Department or Company Objective.</li>
        </ul>

        <h3 style="{{ $h3 }}">How to write a good Key Result</h3>
        <p style="{{ $p }}">KRs are <strong style="color:#e5e7eb;">measurable outcomes</strong>, not tasks. Use the fields:</p>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Field</th><th style="{{ $th }}">Purpose</th><th style="{{ $th }}">Example</th></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">title</span></td><td style="{{ $td }}">One-sentence outcome</td><td style="{{ $td }}">"Achieve ≥95% event tracking accuracy"</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">how_to_measure</span></td><td style="{{ $td }}">Plain-language method</td><td style="{{ $td }}">"QA audit cross-referencing GA4 vs backend DB; ≤5% variance"</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">target_value</span>, <span style="{{ $code }}">current_value</span>, <span style="{{ $code }}">unit</span></td><td style="{{ $td }}">Numeric target + progress</td><td style="{{ $td }}">95, 92, %</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">direction</span></td><td style="{{ $td }}">How progress is scored</td><td style="{{ $td }}">Increase (default), Decrease (e.g. bug count), Maintain (e.g. uptime)</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">progress_mode</span></td><td style="{{ $td }}">Who updates the number</td><td style="{{ $td }}">Manual, Auto (system-calculated), Hybrid</td></tr>
            <tr><td style="{{ $td }}"><span style="{{ $code }}">alignment_note</span></td><td style="{{ $td }}">Cross-team link</td><td style="{{ $td }}">"Amber O2-KR1"</td></tr>
        </table>

        <h3 style="{{ $h3 }}">Auto-calculated KRs</h3>
        <p style="{{ $p }}">Set <span style="{{ $code }}">progress_mode = Auto</span> or <span style="{{ $code }}">Hybrid</span> and pick a source. The scheduler runs <span style="{{ $code }}">goals:recalculate</span> every hour and updates <span style="{{ $code }}">current_value</span> from live data:</p>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Source</th><th style="{{ $th }}">What it counts</th><th style="{{ $th }}">Typical use</th></tr>
            <tr><td style="{{ $td }}">Tickets</td><td style="{{ $td }}">Ticket count filtered by assignee (owner), statuses, and optional project</td><td style="{{ $td }}">"Ship 20 features this quarter"</td></tr>
            <tr><td style="{{ $td }}">Daily Reports</td><td style="{{ $td }}">Submitted daily reports by the goal owner</td><td style="{{ $td }}">"Submit daily reports 60+ days this quarter"</td></tr>
            <tr><td style="{{ $td }}">Weekly Reports</td><td style="{{ $td }}">Submitted weekly reports by the goal owner</td><td style="{{ $td }}">"Submit 12 weekly reports on time"</td></tr>
            <tr><td style="{{ $td }}">Custom</td><td style="{{ $td }}">Manual number (no auto-calc)</td><td style="{{ $td }}">Revenue, NPS, anything external</td></tr>
        </table>

        <h3 style="{{ $h3 }}">The lifecycle of a period</h3>
        <div style="{{ $flow }}">
            <strong style="color:#60a5fa;">Draft</strong> period created by Super Admin &rarr; Objectives &amp; KRs defined &rarr;
            <strong style="color:#22c55e;">Active</strong> (goal updates visible everywhere) &rarr;
            <strong style="color:#9ca3af;">Closed</strong> (reviews auto-generated, scores frozen)
        </div>
        <p style="{{ $p }}">Changing a period's status to <strong style="color:#e5e7eb;">Closed</strong> automatically creates a <strong style="color:#e5e7eb;">Goal Review</strong> for every user who owns at least one Objective in that period, with per-KR snapshots and a computed system score. The admin can also trigger this manually via the <strong style="color:#e5e7eb;">Generate Reviews</strong> button on the period edit page.</p>

        <h3 style="{{ $h3 }}">Progress updates during a period</h3>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Update Progress button</strong> on each KR row (in the goal edit page) — opens a modal for a new value + note. Logged to <span style="{{ $code }}">key_result_updates</span> with <span style="{{ $code }}">source = manual</span>.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Weekly Report integration</strong> — when editing your weekly report, click <strong style="color:#e5e7eb;">Update OKR Progress</strong>. A modal lists every active KR you own; submitted values are logged with <span style="{{ $code }}">source = weekly_report</span> and <span style="{{ $code }}">week_start</span> set to the report's week.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Auto scheduler</strong> — runs hourly, updates Auto/Hybrid KRs from their configured source. Logged with <span style="{{ $code }}">source = auto</span>.</li>
        </ul>

        <h3 style="{{ $h3 }}">The review workflow</h3>
        <div style="{{ $flow }}">
            Period closes &rarr; system generates <strong style="color:#60a5fa;">Goal Review</strong> per user<br>
            &darr;<br>
            <strong style="color:#fcd34d;">Pending self-review</strong> — employee opens <em style="color:#d1d5db;">My Review</em>, reviews each KR's system score, adjusts the Self column, writes a narrative, submits &rarr; supervisor notified<br>
            &darr;<br>
            <strong style="color:#60a5fa;">Pending supervisor</strong> — supervisor opens <em style="color:#d1d5db;">Team Reviews</em>, reads system + self scores side by side, enters Final column values + overall feedback, submits &rarr; employee notified<br>
            &darr;<br>
            <strong style="color:#22c55e;">Completed</strong> — employee can Acknowledge (normal case) or Dispute (supervisor re-notified)<br>
        </div>
        <p style="{{ $p }}"><strong style="color:#e5e7eb;">C-level users</strong> (no supervisor assigned) will still get a review generated — they can self-submit, but their Final score needs to be entered by an admin since there is no supervisor chain above them.</p>

        <h3 style="{{ $h3 }}">Scoring — how the final number is calculated</h3>
        <p style="{{ $p }}">Three numbers are stored for every review:</p>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}"><strong style="color:#60a5fa;">System score</strong> — computed automatically from the KR <span style="{{ $code }}">current_value / target_value</span> at the moment the review was generated. This is the raw "what the data says" baseline.</li>
            <li style="{{ $li }}"><strong style="color:#fcd34d;">Self score</strong> — what the employee claims per KR, based on the recommended system score. They can adjust up or down with context.</li>
            <li style="{{ $li }}"><strong style="color:#22c55e;">Final score</strong> — what the supervisor records per KR after reading system + self. This is the score of record.</li>
        </ul>
        <p style="{{ $p }}">The <strong style="color:#e5e7eb;">overall review score</strong> is a weighted average:</p>
        <div style="{{ $flow }}">
            Per Objective: sum over its KRs of <span style="{{ $code }}">(kr_weight / 100) × kr_final_score</span><br>
            Overall: sum over your Objectives of <span style="{{ $code }}">(objective_weight / 100) × objective_score</span>
        </div>
        <p style="{{ $p }}">Result is a 0–100% figure. Colour bands used across the UI: <span style="padding:1px 6px;border-radius:3px;background:#064e3b;color:#6ee7b7;font-weight:600;">≥70 on track</span> <span style="padding:1px 6px;border-radius:3px;background:#78350f;color:#fcd34d;font-weight:600;">40–69 at risk</span> <span style="padding:1px 6px;border-radius:3px;background:#7f1d1d;color:#fca5a5;font-weight:600;">&lt;40 missed</span></p>

        <h3 style="{{ $h3 }}">Best practices</h3>
        <ul style="padding-left:20px;margin:0 0 14px 0;">
            <li style="{{ $li }}">Write OKRs at the start of the quarter — don't backfill at the end. Targets should be <strong style="color:#e5e7eb;">uncomfortable but plausible</strong>.</li>
            <li style="{{ $li }}">Review progress at least weekly — integrate with your Weekly Report submission.</li>
            <li style="{{ $li }}">Keep KRs measurable. If you can't put a number on it, it doesn't belong.</li>
            <li style="{{ $li }}">Self-review honestly — the supervisor sees the gap between system, self, and final. A self that's far from system without narrative context looks suspicious.</li>
            <li style="{{ $li }}">Supervisor feedback should explain the Final number, especially when it deviates from the self score. "This is the score of record" is not enough context.</li>
            <li style="{{ $li }}">OKRs are for <strong style="color:#e5e7eb;">growth and clarity</strong>, not punishment. A 60% final on an ambitious OKR is often healthier than a 100% on a sandbagged one.</li>
        </ul>

        <h3 style="{{ $h3 }}">Where to find what (quick reference)</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">I want to…</th><th style="{{ $th }}">Go to</th></tr>
            <tr><td style="{{ $td }}">See my own progress</td><td style="{{ $td }}">Performance &rarr; My OKR</td></tr>
            <tr><td style="{{ $td }}">See company-wide goals (transparent)</td><td style="{{ $td }}">Performance &rarr; Company OKR</td></tr>
            <tr><td style="{{ $td }}">See my direct reports' progress</td><td style="{{ $td }}">Performance &rarr; Team OKR <em style="color:#6b7280;">(visible if you have reports)</em></td></tr>
            <tr><td style="{{ $td }}">Update a KR's value manually</td><td style="{{ $td }}">Performance &rarr; Goals &rarr; edit your goal &rarr; KR row &rarr; <strong style="color:#e5e7eb;">Update Progress</strong></td></tr>
            <tr><td style="{{ $td }}">Update KR values for the current week</td><td style="{{ $td }}">Weekly Reports &rarr; edit current week's report &rarr; <strong style="color:#e5e7eb;">Update OKR Progress</strong> button</td></tr>
            <tr><td style="{{ $td }}">Submit my self-review</td><td style="{{ $td }}">Performance &rarr; My Review <em style="color:#6b7280;">(appears when a review exists)</em></td></tr>
            <tr><td style="{{ $td }}">Finalize reviews for my team</td><td style="{{ $td }}">Performance &rarr; Team Reviews</td></tr>
            <tr><td style="{{ $td }}">Create or close a period</td><td style="{{ $td }}">Performance &rarr; Periods <em style="color:#6b7280;">(Super Admin)</em></td></tr>
        </table>
    </div>

    {{-- 16. MCP INTEGRATION --}}
    <div style="{{ $card }}" id="mcp" data-doc-section="MCP Integration Claude Model Context Protocol Tokens">
        <h2 style="{{ $h2 }}">16. MCP Integration (Claude)</h2>

        <p style="{{ $p }}">PMHelper exposes a <strong style="color:#e5e7eb;">Model Context Protocol</strong> endpoint so you can connect Claude Desktop or Claude Code to PMHelper. With it, Claude can list tickets, read comments, create daily reports, etc. — using your permissions.</p>

        <h3 style="{{ $h3 }}">Step 1 — Create an MCP token</h3>
        <ol style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Open <a href="{{ route('filament.pages.mcp-tokens') }}" style="color:#3b82f6;">MCP Tokens</a> from the sidebar.</li>
            <li style="{{ $li }}">Click <strong style="color:#e5e7eb;">New MCP token</strong>, name it after the device that will use it (e.g. <em>Bintang MacBook</em>).</li>
            <li style="{{ $li }}">Copy the token string — it is shown <strong style="color:#ef4444;">only once</strong>. If lost, revoke and generate a new one.</li>
        </ol>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 12px 0;">One token per machine is best. Tokens inherit your role/permissions, so any action taken via MCP is attributed to you.</p>

        <h3 style="{{ $h3 }}">Step 2 — Register the server in Claude</h3>

        <p style="font-size:13px;font-weight:600;color:#e5e7eb;margin:10px 0 6px 0;">Option A — Claude Code (CLI)</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 6px 0;">Run this single command in your terminal:</p>
        <pre style="background:#111827;border:1px solid #374151;border-radius:6px;padding:10px 12px;font-size:11px;color:#e5e7eb;overflow-x:auto;margin:0 0 10px 0;white-space:pre-wrap;word-break:break-all;">claude mcp add --transport http pmhelper {{ url('/api/mcp') }} --header "Authorization: Bearer &lt;your-token&gt;"</pre>

        <p style="font-size:13px;font-weight:600;color:#e5e7eb;margin:10px 0 6px 0;">Option B — Claude Desktop app (OAuth, no manual token)</p>
        <p style="font-size:12px;color:#9ca3af;margin:0 0 8px 0;">Claude Desktop uses an OAuth flow — you don't paste a token anywhere. It will briefly open your browser for you to approve access.</p>
        <ol style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Open Claude Desktop → <strong style="color:#e5e7eb;">Settings</strong> → <strong style="color:#e5e7eb;">Connectors</strong> (or <em>Integrations</em>).</li>
            <li style="{{ $li }}">Click <strong style="color:#e5e7eb;">Add custom connector</strong>.</li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Name:</strong> <code style="background:#1f2937;padding:2px 6px;border-radius:4px;font-size:11px;color:#e5e7eb;">PMHelper</code></li>
            <li style="{{ $li }}"><strong style="color:#e5e7eb;">Remote MCP server URL:</strong> <code style="background:#1f2937;padding:2px 6px;border-radius:4px;font-size:11px;color:#e5e7eb;">{{ url('/api/mcp') }}</code></li>
            <li style="{{ $li }}">Leave <em>OAuth Client ID</em> / <em>OAuth Client Secret</em> <strong style="color:#e5e7eb;">empty</strong> — PMHelper issues them automatically via dynamic client registration.</li>
            <li style="{{ $li }}">Click <strong style="color:#e5e7eb;">Add</strong>. Claude opens <code style="background:#1f2937;padding:2px 6px;border-radius:4px;font-size:11px;color:#e5e7eb;">{{ url('/mcp/authorize') }}</code> in your browser — <strong style="color:#e5e7eb;">make sure you're logged in to PMHelper there</strong>, then click <strong style="color:#22c55e;">Authorize</strong>.</li>
            <li style="{{ $li }}">Browser returns to Claude Desktop with a "Connected" state. Tools appear in your next chat.</li>
        </ol>
        <p style="font-size:12px;color:#9ca3af;margin:10px 0 12px 0;">Access tokens minted via OAuth are visible on the <a href="{{ route('filament.pages.mcp-tokens') }}" style="color:#3b82f6;">MCP Tokens</a> page (named <code style="background:#1f2937;padding:2px 6px;border-radius:4px;font-size:11px;color:#e5e7eb;">mcp:oauth:...</code>) — you can revoke them like any other token.</p>

        <h3 style="{{ $h3 }}">Available tools</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Tool</th><th style="{{ $th }}">Purpose</th></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">list_tickets</code></td><td style="{{ $td }}">List tickets. Filters: project_id, status_id, assignee_id, mine, search, limit.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">get_ticket</code></td><td style="{{ $td }}">Full ticket detail incl. comments (by id or code).</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">create_ticket</code></td><td style="{{ $td }}">Create a ticket in a project you can access.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">update_ticket_status</code></td><td style="{{ $td }}">Move a ticket to a new status (respects role-group gates).</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">add_ticket_comment</code></td><td style="{{ $td }}">Add a comment. Content may include @mentions.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">list_discussions</code> / <code style="font-size:11px;">get_discussion</code></td><td style="{{ $td }}">Browse discussions + replies.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">add_discussion_comment</code></td><td style="{{ $td }}">Reply to a discussion.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">list_daily_reports</code> / <code style="font-size:11px;">get_daily_report</code></td><td style="{{ $td }}">Own reports by default; team-wide when you pass user_id or all_accessible.</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">create_daily_report</code></td><td style="{{ $td }}">Create a draft or submitted report (one per user/project/date).</td></tr>
            <tr><td style="{{ $td }}"><code style="font-size:11px;">list_projects</code> / <code style="font-size:11px;">list_ticket_statuses</code> / <code style="font-size:11px;">list_users</code></td><td style="{{ $td }}">Lookup helpers for resolving IDs.</td></tr>
        </table>

        <h3 style="{{ $h3 }}">Example prompts</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}"><em>"Ringkas semua komentar di QOS-51 dalam bentuk timeline."</em></li>
            <li style="{{ $li }}"><em>"Cek tiket status Retest di project QineticOS, lalu bikin daily report draft hari ini yang ngerangkum kerjaan dari tiket-tiket itu."</em></li>
            <li style="{{ $li }}"><em>"Balas discussion #42 dengan konfirmasi bahwa fix sudah di-deploy dan minta QA mulai retest."</em></li>
        </ul>

        <h3 style="{{ $h3 }}">Security notes</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Tokens inherit your permissions — <strong style="color:#e5e7eb;">don't share the token</strong> with teammates; let them create their own.</li>
            <li style="{{ $li }}">The <em>Last used</em> column shows when a token was last touched. A long-idle token is a candidate to revoke.</li>
            <li style="{{ $li }}">Lost a laptop or quitting a project? Revoke the token from the MCP Tokens page and generate a new one for the replacement device.</li>
            <li style="{{ $li }}">Comments / tickets created via MCP are attributed to the token owner. Activity log + email notifications behave exactly as if you created them through the web UI.</li>
        </ul>
    </div>

    {{-- 17. WRITING GUIDELINES --}}
    <div style="{{ $card }}" id="writing-rules" data-doc-section="Writing Guidelines">
        <h2 style="{{ $h2 }}">17. Writing Guidelines</h2>

        <h3 style="{{ $h3 }}">Ticket Names</h3>
        <table style="{{ $tbl }}">
            <tr><th style="{{ $th }}">Rule</th><th style="{{ $th }}">Good Example</th><th style="{{ $th }}">Bad Example</th></tr>
            <tr><td style="{{ $td }}">Be specific</td><td style="{{ $td }}"><span style="color:#22c55e;">Fix login redirect loop on mobile Safari</span></td><td style="{{ $td }}"><span style="color:#ef4444;">Fix login</span></td></tr>
            <tr><td style="{{ $td }}">Start with verb</td><td style="{{ $td }}"><span style="color:#22c55e;">Add CSV export to user list</span></td><td style="{{ $td }}"><span style="color:#ef4444;">CSV export</span></td></tr>
            <tr><td style="{{ $td }}">Include context</td><td style="{{ $td }}"><span style="color:#22c55e;">Update pricing page hero section copy</span></td><td style="{{ $td }}"><span style="color:#ef4444;">Update text</span></td></tr>
            <tr><td style="{{ $td }}">Keep under 80 chars</td><td style="{{ $td }}"><span style="color:#22c55e;">Implement dark mode for email templates</span></td><td style="{{ $td }}"><span style="color:#ef4444;">We need to implement dark mode for all our email templates because they look bad</span></td></tr>
        </table>

        <h3 style="{{ $h3 }}">Ticket Descriptions</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Explain the <strong style="color:#e5e7eb;">why</strong>, not just the what</li>
            <li style="{{ $li }}">Include acceptance criteria when possible</li>
            <li style="{{ $li }}">Attach screenshots, mockups, or reference links</li>
            <li style="{{ $li }}">For bugs: always fill Steps to Reproduce, Expected vs Actual Behavior</li>
        </ul>

        <h3 style="{{ $h3 }}">Request Writing</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Be clear about the <strong style="color:#e5e7eb;">objective</strong> - what problem are you solving?</li>
            <li style="{{ $li }}">Define measurable <strong style="color:#e5e7eb;">expected outcome</strong></li>
            <li style="{{ $li }}">Set realistic <strong style="color:#e5e7eb;">impact level</strong> - not everything is Critical</li>
            <li style="{{ $li }}">Include relevant context in the description</li>
        </ul>

        <h3 style="{{ $h3 }}">Daily Report Tips</h3>
        <ul style="padding-left:20px;margin:0 0 12px 0;">
            <li style="{{ $li }}">Use bullet points, not paragraphs</li>
            <li style="{{ $li }}">Reference ticket codes (e.g. QOS-75)</li>
            <li style="{{ $li }}">Mention blockers early - don't wait</li>
            <li style="{{ $li }}">Plans should be actionable and specific</li>
        </ul>
    </div>

    {{-- 18. DO'S AND DON'TS --}}
    <div style="{{ $card }}" id="dos-donts" data-doc-section="Do's and Don'ts">
        <h2 style="{{ $h2 }}">18. Do's & Don'ts</h2>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <h3 style="font-size:14px;font-weight:600;color:#22c55e;margin:0 0 12px 0;">DO's</h3>
                <ul style="padding-left:0;list-style:none;margin:0;">
                    @foreach(['Update ticket status when you start/finish work','Log your time regularly','Submit daily reports every working day','Use @mentions to notify relevant people','Write clear, specific ticket names','Use Request type for cross-department asks','Keep discussions focused and on-topic','Complete your profile (photo, department, position)'] as $item)
                    <li style="display:flex;align-items:flex-start;gap:8px;margin-bottom:8px;font-size:13px;color:#9ca3af;line-height:1.5;">
                        <span style="color:#22c55e;font-size:14px;margin-top:1px;flex-shrink:0;">&#10003;</span> {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:600;color:#ef4444;margin:0 0 12px 0;">DON'Ts</h3>
                <ul style="padding-left:0;list-style:none;margin:0;">
                    @foreach(["Don't create execution tickets without proper requirement","Don't change someone else's ticket status without telling them","Don't mark all requests as Critical impact","Don't leave tickets stuck in In Progress forever","Don't skip daily reports - it hurts team visibility","Don't create duplicate tickets - search first","Don't use comments for off-topic conversations","Don't ignore notifications - they exist for a reason"] as $item)
                    <li style="display:flex;align-items:flex-start;gap:8px;margin-bottom:8px;font-size:13px;color:#9ca3af;line-height:1.5;">
                        <span style="color:#ef4444;font-size:14px;margin-top:1px;flex-shrink:0;">&#10007;</span> {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- 18. FAQ --}}
    <div style="{{ $card }}" id="faq" data-doc-section="Frequently Asked Questions">
        <h2 style="{{ $h2 }}">19. FAQ</h2>

        @php
        $faqs = [
            ['I forgot my password. How do I reset it?', 'Click "Forgot Password" on the login page. A password reset link will be sent to your registered email address.'],
            ['I can\'t see a project. Why?', 'You need to be added as a team member of the project. Ask the project owner or a PM to add you.'],
            ['How do I change my role?', 'Only Super Admin can assign roles. Contact your system administrator.'],
            ['Can I create tickets directly without using Request?', 'Depends on your role. Delivery roles (Developer, QA, Designer, etc.) can create Task/Bug/Feature directly. Non-delivery roles (HR, Sales, Finance) should use the Request type which goes through PM approval.'],
            ['How does the birthday feature work?', 'Set your birthday in Profile Settings. On your birthday, a celebration banner with your name, age, animated balloons, and a personalized wish appears on everyone\'s dashboard.'],
            ['Why am I not receiving email notifications?', 'Check: (1) Is your email verified? (2) Check spam/junk folder. (3) If using secondary CC email, make sure it\'s set correctly in Profile Settings.'],
            ['How do I mention someone in a comment?', 'Type @ followed by their username. A dropdown of matching users will appear. Select with Enter or Tab. The mentioned person receives a notification.'],
            ['Can I delete a ticket?', 'Only Super Admin can delete tickets. Other users can change status to Closed or Rejected.'],
            ['What timezone does the app use?', 'Default is Asia/Jakarta (GMT+7). Your timezone is auto-detected from your browser on first login. You can manually change it in Profile Settings under "Timezone".'],
            ['How do I export my timesheet data?', 'On any ticket that has logged hours, click the three-dot menu and select "Export time logged" to download a CSV file.'],
            ['What\'s the difference between Role and Position?', 'Role = system permissions (what you can do in the app). Position = organizational title (displayed in org chart and profile). They are completely independent.'],
            ['How do I subscribe to a ticket for updates?', 'On any ticket detail page, click the "Subscribe" bell button. You\'ll receive notifications for all status changes and new comments on that ticket.'],
            ['Who writes my OKRs — me or my manager?', 'Currently only Super Admin authors Goals in the admin panel. You discuss proposed OKRs with your supervisor first, and the admin enters them under your ownership. You have full control over updating KR progress during the period and filling in your self-review at period close.'],
            ['Do my OKR weights have to add up to 100%?', 'Yes — twice. The sum of all your Individual Objective weights per period must equal 100%, and the sum of Key Result weights within each Objective must also equal 100%. The form blocks saves that would exceed 100%; under-allocated drafts are allowed but can\'t be activated.'],
            ['What\'s the difference between System, Self, and Final score?', 'System is computed automatically from KR current/target values at review time. Self is what you claim when filling your self-review. Final is what your supervisor records after reading both. Final is the score of record. All three are stored so gaps are visible.'],
            ['How is the overall review score calculated?', 'For each Objective: Σ (kr_weight / 100) × kr_final_score. Overall: Σ (objective_weight / 100) × objective_score. So a 70% on a high-weight Objective counts for more than a 100% on a 5% Objective.'],
            ['Can a KR update itself automatically?', 'Yes. Set its progress_mode to Auto or Hybrid and pick a source (Tickets, Daily Reports, Weekly Reports, or Custom). The goals:recalculate command runs hourly and writes new values to the KR\'s history. Hybrid means auto-calculated but the supervisor/owner can still override.'],
            ['What happens when a period closes?', 'Reviews are auto-generated for every user who owns at least one Objective in that period. KR progress is snapshotted, system scores are computed, and the review moves to pending_self status. Employees fill self-review; supervisors finalize; employees acknowledge or dispute.'],
            ['I don\'t have a supervisor — how does my review work?', 'C-Level users can self-submit, but since there\'s no supervisor above them, a Super Admin finalizes their review. Contact admin to arrange this — typically a board/leadership-level review.'],
        ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:6px;">
            @foreach($faqs as $i => $faq)
            <div style="border:1px solid #374151;border-radius:6px;overflow:hidden;">
                <button @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:transparent;border:none;cursor:pointer;text-align:left;">
                    <span style="font-size:13px;font-weight:500;color:#e5e7eb;">{{ $faq[0] }}</span>
                    <svg :style="openFaq === {{ $i }} ? 'transform:rotate(180deg)' : ''" width="14" height="14" style="min-width:14px;min-height:14px;max-width:14px;max-height:14px;color:#6b7280;transition:transform 0.2s;flex-shrink:0;margin-left:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openFaq === {{ $i }}" x-collapse style="padding:0 16px 14px 16px;">
                    <p style="font-size:13px;line-height:1.7;color:#9ca3af;margin:0;">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Footer --}}
    <div style="text-align:center;padding:32px 0;font-size:11px;color:#4b5563;">
        <p style="margin:0;">PM Helper Documentation - Last updated {{ now()->format('d F Y') }}</p>
        <p style="margin:4px 0 0 0;">Built for Capella Digicrats ID</p>
    </div>
</div>
</x-filament::page>
