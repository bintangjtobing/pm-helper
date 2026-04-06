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
            @php $toc = [['getting-started','Getting Started'],['dashboard','Dashboard'],['projects','Projects'],['tickets','Tickets & Request System'],['kanban','Kanban Board'],['comments','Comments & Mentions'],['reports','Daily & Weekly Reports'],['discussions','Discussions'],['timesheet','Timesheet & Time Logging'],['notifications','Notifications'],['roles','Roles & Permissions'],['organization','Organization & Departments'],['profile','Profile Settings'],['feedback','Customer Feedback'],['writing-rules','Writing Guidelines'],['dos-donts',"Do's & Don'ts"],['faq','FAQ']]; @endphp
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

    {{-- 15. WRITING GUIDELINES --}}
    <div style="{{ $card }}" id="writing-rules" data-doc-section="Writing Guidelines">
        <h2 style="{{ $h2 }}">15. Writing Guidelines</h2>

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

    {{-- 16. DO'S AND DON'TS --}}
    <div style="{{ $card }}" id="dos-donts" data-doc-section="Do's and Don'ts">
        <h2 style="{{ $h2 }}">16. Do's & Don'ts</h2>

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

    {{-- 17. FAQ --}}
    <div style="{{ $card }}" id="faq" data-doc-section="Frequently Asked Questions">
        <h2 style="{{ $h2 }}">17. FAQ</h2>

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
        ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:6px;">
            @foreach($faqs as $i => $faq)
            <div style="border:1px solid #374151;border-radius:6px;overflow:hidden;">
                <button @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:transparent;border:none;cursor:pointer;text-align:left;">
                    <span style="font-size:13px;font-weight:500;color:#e5e7eb;">{{ $faq[0] }}</span>
                    <svg :style="openFaq === {{ $i }} ? 'transform:rotate(180deg)' : ''" style="width:14px;height:14px;color:#6b7280;transition:transform 0.2s;flex-shrink:0;margin-left:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
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
