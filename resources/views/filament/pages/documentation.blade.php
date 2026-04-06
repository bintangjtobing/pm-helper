<x-filament::page>
<div x-data="{
    search: '',
    get filteredSections() {
        if (!this.search) return [];
        const q = this.search.toLowerCase();
        const results = [];
        document.querySelectorAll('[data-doc-section]').forEach(el => {
            const text = el.textContent.toLowerCase();
            const title = el.dataset.docSection;
            if (text.includes(q) || title.toLowerCase().includes(q)) {
                results.push({ title, id: el.id });
            }
        });
        return results;
    }
}" class="max-w-5xl mx-auto">

    {{-- Search --}}
    <div class="sticky top-0 z-30 pb-4 bg-gray-900/95 backdrop-blur-sm -mx-4 px-4 pt-2">
        <div class="relative">
            <input type="text" x-model="search" placeholder="{{ __('Search documentation...') }}"
                class="w-full px-4 py-3 pl-10 text-sm rounded-lg bg-gray-800 border border-gray-700 text-white placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500" />
            <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        {{-- Search Results --}}
        <template x-if="search.length > 1 && filteredSections.length > 0">
            <div class="mt-2 p-3 rounded-lg bg-gray-800 border border-gray-700 max-h-48 overflow-y-auto">
                <template x-for="result in filteredSections" :key="result.id">
                    <a :href="'#' + result.id" @click="search = ''" class="block px-3 py-2 text-sm text-gray-300 rounded hover:bg-gray-700 hover:text-white" x-text="result.title"></a>
                </template>
            </div>
        </template>
    </div>

    {{-- Table of Contents --}}
    <x-filament::card>
        <h2 class="text-lg font-bold text-white mb-4">Table of Contents</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-1 text-sm">
            <a href="#getting-started" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">1. Getting Started</a>
            <a href="#dashboard" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">2. Dashboard</a>
            <a href="#projects" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">3. Projects</a>
            <a href="#tickets" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">4. Tickets & Request System</a>
            <a href="#kanban" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">5. Kanban Board</a>
            <a href="#comments" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">6. Comments & Mentions</a>
            <a href="#reports" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">7. Daily & Weekly Reports</a>
            <a href="#discussions" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">8. Discussions</a>
            <a href="#timesheet" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">9. Timesheet & Time Logging</a>
            <a href="#notifications" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">10. Notifications</a>
            <a href="#roles" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">11. Roles & Permissions</a>
            <a href="#organization" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">12. Organization & Departments</a>
            <a href="#profile" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">13. Profile Settings</a>
            <a href="#feedback" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">14. Customer Feedback</a>
            <a href="#writing-rules" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">15. Writing Guidelines</a>
            <a href="#dos-donts" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">16. Do's & Don'ts</a>
            <a href="#faq" class="px-3 py-1.5 rounded hover:bg-gray-800 text-gray-300 hover:text-white">17. FAQ</a>
        </div>
    </x-filament::card>

    <div class="mt-6 space-y-6">

        {{-- 1. GETTING STARTED --}}
        <x-filament::card>
            <div id="getting-started" data-doc-section="Getting Started">
                <h2 class="text-xl font-bold text-white mb-4">1. Getting Started</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">What is PM Helper?</h3>
                <p class="text-sm text-gray-400 leading-relaxed">PM Helper is a comprehensive project management platform built for digital marketing companies. It provides tools for task tracking, team reporting, client feedback, internal discussions, time logging, and organization management — all in one place.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">First Time Login</h3>
                <ol class="text-sm text-gray-400 list-decimal pl-5 space-y-1">
                    <li>You will receive an email invitation with a verification link</li>
                    <li>Click "Verify My Account" to set your password</li>
                    <li>Login at <strong class="text-white">pm.digicrats.com</strong></li>
                    <li>Complete your profile: upload photo, set gender, department, position</li>
                    <li>Your timezone will be auto-detected from your browser</li>
                </ol>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Navigation</h3>
                <p class="text-sm text-gray-400">The sidebar on the left contains all menu items grouped by category: Management, Reports, Organization, Referential, and Settings. Your visible menus depend on your assigned role.</p>
            </div>
        </x-filament::card>

        {{-- 2. DASHBOARD --}}
        <x-filament::card>
            <div id="dashboard" data-doc-section="Dashboard">
                <h2 class="text-xl font-bold text-white mb-4">2. Dashboard</h2>
                <p class="text-sm text-gray-400 mb-3">The dashboard is your home screen with real-time widgets:</p>

                <div class="space-y-3 text-sm">
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Greeting Widget</strong>
                        <p class="text-gray-400 mt-1">Personalized greeting based on time of day (Good Morning/Afternoon/Evening/Night) with a random motivational quote.</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Project Audit Overview</strong>
                        <p class="text-gray-400 mt-1">Total projects, health score, overdue count, and completion rate across all your projects.</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Birthday Celebration</strong>
                        <p class="text-gray-400 mt-1">Appears when a team member has a birthday today. Shows animated balloons, personalized wish, and illustration. If YOU are the birthday person, you'll see "Happy Birthday to You!"</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Reports Overview</strong>
                        <p class="text-gray-400 mt-1">Daily reports submitted today, weekly reports this week, pending reviews, and weekly activity streak.</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Discussions</strong>
                        <p class="text-gray-400 mt-1">Active discussions with status counters (Open, In Discussion, Resolved) and high priority alerts.</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Recent Activity Feed</strong>
                        <p class="text-gray-400 mt-1">Live feed of status changes, comments, and weekly reports from your projects.</p>
                    </div>
                </div>
            </div>
        </x-filament::card>

        {{-- 3. PROJECTS --}}
        <x-filament::card>
            <div id="projects" data-doc-section="Projects">
                <h2 class="text-xl font-bold text-white mb-4">3. Projects</h2>
                <p class="text-sm text-gray-400 mb-3">Projects are the top-level container for all work. Each project has tickets, sprints, and team members.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Creating a Project</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Name, description, and ticket prefix (e.g., "QOS" generates QOS-1, QOS-2...)</li>
                    <li>Assign team members who can view and work on the project</li>
                    <li>Set project status and type (Kanban, Scrum, etc.)</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Project Views</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-gray-200">Details</strong> — Project info, members, settings</li>
                    <li><strong class="text-gray-200">Board</strong> — Kanban board view</li>
                    <li><strong class="text-gray-200">Tickets</strong> — List of all tickets</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 4. TICKETS & REQUEST SYSTEM --}}
        <x-filament::card>
            <div id="tickets" data-doc-section="Tickets and Request System">
                <h2 class="text-xl font-bold text-white mb-4">4. Tickets & Request System</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Ticket Types</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm mb-4">
                    <div class="p-2 rounded bg-gray-800"><span class="text-purple-400 font-semibold">Request</span> — Internal request from any department</div>
                    <div class="p-2 rounded bg-gray-800"><span class="text-blue-400 font-semibold">Feature</span> — New functionality</div>
                    <div class="p-2 rounded bg-gray-800"><span class="text-green-400 font-semibold">Task</span> — General work item</div>
                    <div class="p-2 rounded bg-gray-800"><span class="text-red-400 font-semibold">Bug</span> — Something broken</div>
                    <div class="p-2 rounded bg-gray-800"><span class="text-yellow-400 font-semibold">Improvement</span> — Enhancement</div>
                    <div class="p-2 rounded bg-gray-800"><span class="text-orange-400 font-semibold">Hotfix</span> — Urgent fix</div>
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Request System (New)</h3>
                <p class="text-sm text-gray-400 mb-2">All roles can create a <strong class="text-purple-400">Request</strong> ticket. This goes through an approval workflow before becoming an execution ticket.</p>

                <div class="p-4 rounded bg-gray-800 text-sm text-gray-300 font-mono mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-1 rounded bg-purple-500/20 text-purple-400">Request</span>
                        <span class="text-gray-500">→</span>
                        <span class="px-2 py-1 rounded bg-yellow-500/20 text-yellow-400">Under Review</span>
                        <span class="text-gray-500">→</span>
                        <span class="px-2 py-1 rounded bg-green-500/20 text-green-400">Approved</span>
                        <span class="text-gray-500">→</span>
                        <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400">Convert to Task/Feature/Bug</span>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="ml-[200px] text-gray-500">↘</span>
                        <span class="px-2 py-1 rounded bg-red-500/20 text-red-400">Rejected</span>
                        <span class="text-gray-500 text-xs">(with reason)</span>
                    </div>
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Request Required Fields</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-gray-200">Objective</strong> — What do you want to achieve?</li>
                    <li><strong class="text-gray-200">Expected Outcome</strong> — What is the expected result?</li>
                    <li><strong class="text-gray-200">Impact</strong> — Low / Medium / High / Critical</li>
                    <li><strong class="text-gray-200">Department</strong> — Which department is requesting</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">PM/Executive Actions on Requests</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-yellow-300">Start Review</strong> — Mark request as being reviewed</li>
                    <li><strong class="text-green-300">Approve</strong> — Approve the request</li>
                    <li><strong class="text-red-300">Reject</strong> — Reject with mandatory reason</li>
                    <li><strong class="text-blue-300">Convert to Task</strong> — Change type to Task/Feature/Bug and assign to delivery</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Bug Report Fields</h3>
                <p class="text-sm text-gray-400">When creating a Bug or Hotfix ticket, additional fields appear: Steps to Reproduce, Expected Behavior, Actual Behavior, and Environment.</p>
            </div>
        </x-filament::card>

        {{-- 5. KANBAN --}}
        <x-filament::card>
            <div id="kanban" data-doc-section="Kanban Board">
                <h2 class="text-xl font-bold text-white mb-4">5. Kanban Board</h2>
                <p class="text-sm text-gray-400 mb-3">Visual board where tickets are organized by status columns. Drag and drop tickets between columns to change their status.</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Each column represents a ticket status</li>
                    <li>Drag a ticket card to another column to update its status</li>
                    <li>Click on a ticket card to view its details</li>
                    <li>Ticket cards show: code, name, assignee avatar, priority color</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 6. COMMENTS & MENTIONS --}}
        <x-filament::card>
            <div id="comments" data-doc-section="Comments and Mentions">
                <h2 class="text-xl font-bold text-white mb-4">6. Comments & Mentions</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Adding Comments</h3>
                <p class="text-sm text-gray-400 mb-2">Use the rich text editor at the bottom of any ticket to add comments. Supports bold, italic, lists, links, code blocks, and images.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">@Mentions</h3>
                <p class="text-sm text-gray-400 mb-2">Type <code class="px-1.5 py-0.5 rounded bg-gray-700 text-blue-400">@</code> followed by a username to mention someone. A dropdown will appear with matching users.</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Works in ticket comments and discussion replies</li>
                    <li>Mentioned users receive an email + bell notification</li>
                    <li>Mentions render as <span class="px-1 rounded bg-blue-500/15 text-blue-400">@username</span> blue badges</li>
                    <li>Navigate with Arrow keys, select with Enter/Tab</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Time Logging via Comments</h3>
                <p class="text-sm text-gray-400">You can log time directly in comments using the <code class="px-1.5 py-0.5 rounded bg-gray-700 text-green-400">/spend</code> command:</p>
                <div class="mt-2 p-3 rounded bg-gray-800 text-sm font-mono text-gray-300">
                    /spend 2h 30m — Worked on API integration<br>
                    /spend 45m — Quick bug fix
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Deleting Comments</h3>
                <p class="text-sm text-gray-400">You can always delete your own comments. Super Admins can delete any comment.</p>
            </div>
        </x-filament::card>

        {{-- 7. REPORTS --}}
        <x-filament::card>
            <div id="reports" data-doc-section="Daily and Weekly Reports">
                <h2 class="text-xl font-bold text-white mb-4">7. Daily & Weekly Reports</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Daily Reports</h3>
                <p class="text-sm text-gray-400 mb-2">Standup-style daily reporting with three sections:</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-green-300">What was accomplished today</strong> — What you worked on</li>
                    <li><strong class="text-blue-300">Plans for tomorrow</strong> — What's next</li>
                    <li><strong class="text-red-300">Blockers / Issues</strong> — Any impediments (optional)</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Weekly Reports</h3>
                <p class="text-sm text-gray-400 mb-2">Comprehensive weekly summary with auto-generated progress data from tickets. Can attach PDF/DOCX files and extract metrics via AI.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Report Workflow</h3>
                <div class="p-3 rounded bg-gray-800 text-sm text-gray-300 font-mono">
                    Draft → Submitted → Acknowledged (by PM/Executive)
                </div>
                <p class="text-sm text-gray-400 mt-2">When you submit a report, PM, Executive, and Stakeholder roles receive an email notification.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Markdown Support</h3>
                <p class="text-sm text-gray-400">Report content supports Markdown: <code class="px-1 rounded bg-gray-700">## Heading</code>, <code class="px-1 rounded bg-gray-700">**bold**</code>, <code class="px-1 rounded bg-gray-700">- bullet list</code>, tables, code blocks.</p>
            </div>
        </x-filament::card>

        {{-- 8. DISCUSSIONS --}}
        <x-filament::card>
            <div id="discussions" data-doc-section="Discussions">
                <h2 class="text-xl font-bold text-white mb-4">8. Discussions</h2>
                <p class="text-sm text-gray-400 mb-3">Discussion board for topics that need to be recorded but aren't tickets. Think of it as a structured chat for decisions.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Creating a Discussion</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Title, description (supports Markdown), priority, project (optional), linked ticket (optional)</li>
                    <li>Everyone in the project gets notified</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Status Flow</h3>
                <div class="p-3 rounded bg-gray-800 text-sm text-gray-300 font-mono">
                    Open → In Discussion (auto when first reply) → Resolved / Closed
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Replying</h3>
                <p class="text-sm text-gray-400">Use the reply box at the bottom. Supports @mentions. All participants get notified on new replies.</p>
            </div>
        </x-filament::card>

        {{-- 9. TIMESHEET --}}
        <x-filament::card>
            <div id="timesheet" data-doc-section="Timesheet and Time Logging">
                <h2 class="text-xl font-bold text-white mb-4">9. Timesheet & Time Logging</h2>
                <p class="text-sm text-gray-400 mb-3">Track time spent on tickets. Two ways to log time:</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-gray-200">Log Time button</strong> — On any ticket detail page, click "Log time" and enter hours + minutes + activity type</li>
                    <li><strong class="text-gray-200">/spend command</strong> — In a comment, type <code class="px-1 rounded bg-gray-700">/spend 2h 30m</code></li>
                </ul>
                <p class="text-sm text-gray-400 mt-2">PM and Executive roles can view the Timesheet dashboard with aggregated data per user, project, and time period.</p>
            </div>
        </x-filament::card>

        {{-- 10. NOTIFICATIONS --}}
        <x-filament::card>
            <div id="notifications" data-doc-section="Notifications">
                <h2 class="text-xl font-bold text-white mb-4">10. Notifications</h2>
                <p class="text-sm text-gray-400 mb-3">You receive notifications via two channels:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm mb-3">
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Bell Icon (In-App)</strong>
                        <p class="text-gray-400 mt-1">Real-time via Pusher WebSocket. Click the bell in the top-right to see unread notifications.</p>
                    </div>
                    <div class="p-3 rounded bg-gray-800">
                        <strong class="text-white">Email</strong>
                        <p class="text-gray-400 mt-1">Sent to your email (and secondary CC email if set). Clean template with action buttons.</p>
                    </div>
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">What Triggers Notifications</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>New ticket created (to project members)</li>
                    <li>Ticket status changed (to owner + responsible)</li>
                    <li>New comment on ticket (to subscribers)</li>
                    <li>@Mentioned in comment (to mentioned user)</li>
                    <li>Daily/Weekly report submitted (to PM + Executive + Stakeholder)</li>
                    <li>New discussion created (to project members)</li>
                    <li>Discussion reply (to author + participants)</li>
                    <li>Customer feedback submitted/updated/converted</li>
                    <li>Account created (verification email)</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 11. ROLES & PERMISSIONS --}}
        <x-filament::card>
            <div id="roles" data-doc-section="Roles and Permissions">
                <h2 class="text-xl font-bold text-white mb-4">11. Roles & Permissions</h2>
                <p class="text-sm text-gray-400 mb-3">Each user is assigned one role that determines what they can see and do. Roles are independent of department/position.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="py-2 px-3 text-gray-300 font-semibold">Role</th>
                                <th class="py-2 px-3 text-gray-300 font-semibold">Layer</th>
                                <th class="py-2 px-3 text-gray-300 font-semibold">Key Capabilities</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-400">
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-red-400 font-medium">Super Admin</td><td class="py-2 px-3">System</td><td class="py-2 px-3">Full access to everything</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-red-300 font-medium">Executive</td><td class="py-2 px-3">Strategic</td><td class="py-2 px-3">View all, approve/reject requests, no operational</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-blue-400 font-medium">Project Manager</td><td class="py-2 px-3">Delivery</td><td class="py-2 px-3">Full project/ticket control, approve requests, manage sprints</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-green-400 font-medium">Developer</td><td class="py-2 px-3">Execution</td><td class="py-2 px-3">Create/update tickets, comment, log time</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-amber-400 font-medium">QA / Tester</td><td class="py-2 px-3">Quality</td><td class="py-2 px-3">Update tickets, update status, create feedback</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-violet-400 font-medium">DevOps</td><td class="py-2 px-3">Deployment</td><td class="py-2 px-3">Update tickets/status, view timesheet</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-blue-300 font-medium">Account Manager</td><td class="py-2 px-3">Client</td><td class="py-2 px-3">Manage projects, create tickets, handle feedback</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-orange-400 font-medium">Sales</td><td class="py-2 px-3">Revenue</td><td class="py-2 px-3">View projects, create requests, manage feedback</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-purple-400 font-medium">Digital Marketer</td><td class="py-2 px-3">Delivery Support</td><td class="py-2 px-3">View/update tickets, comment</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-purple-300 font-medium">Content Writer</td><td class="py-2 px-3">Delivery Support</td><td class="py-2 px-3">View/update tickets, comment</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-green-300 font-medium">Designer</td><td class="py-2 px-3">Creative</td><td class="py-2 px-3">View/update tickets, comment</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-cyan-400 font-medium">Data Analyst</td><td class="py-2 px-3">Data</td><td class="py-2 px-3">View dashboards, timesheet, feedback</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-gray-300 font-medium">Operations</td><td class="py-2 px-3">Ops</td><td class="py-2 px-3">Create/update tickets, manage activities</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-emerald-400 font-medium">HR</td><td class="py-2 px-3">Internal</td><td class="py-2 px-3">Manage users, create requests (no project access)</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-gray-400 font-medium">Finance</td><td class="py-2 px-3">Internal</td><td class="py-2 px-3">View timesheet/users, create requests</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-2 px-3 text-orange-300 font-medium">Stakeholder</td><td class="py-2 px-3">View Only</td><td class="py-2 px-3">View projects/tickets, submit feedback</td></tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Request Permissions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="py-2 px-3 text-gray-300">Role</th>
                                <th class="py-2 px-3 text-gray-300">Create Request</th>
                                <th class="py-2 px-3 text-gray-300">Create Task/Bug</th>
                                <th class="py-2 px-3 text-gray-300">Approve/Reject</th>
                                <th class="py-2 px-3 text-gray-300">Convert</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-400">
                            <tr class="border-b border-gray-800"><td class="py-1 px-3">HR, Finance, Sales</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-red-400">No</td><td class="py-1 px-3 text-red-400">No</td><td class="py-1 px-3 text-red-400">No</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-1 px-3">Marketer, Writer, Designer</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-red-400">No</td><td class="py-1 px-3 text-red-400">No</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-1 px-3">Dev, QA, DevOps, Ops</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-red-400">No</td><td class="py-1 px-3 text-red-400">No</td></tr>
                            <tr class="border-b border-gray-800"><td class="py-1 px-3">PM, Executive, Super Admin</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-green-400">Yes</td><td class="py-1 px-3 text-green-400">Yes</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::card>

        {{-- 12. ORGANIZATION --}}
        <x-filament::card>
            <div id="organization" data-doc-section="Organization and Departments">
                <h2 class="text-xl font-bold text-white mb-4">12. Organization & Departments</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Departments (13 total)</h3>
                <p class="text-sm text-gray-400 mb-2">10 Core + 3 Advanced departments. Managed by Super Admin under Organization > Departments.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Positions</h3>
                <p class="text-sm text-gray-400 mb-2">50+ positions linked to departments. Each position has a level: Staff, Lead, Manager, Head, C-Level. Managed under Organization > Positions.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Organization Chart</h3>
                <p class="text-sm text-gray-400 mb-2">Visual hierarchy tree based on supervisor relationships. Two views:</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-gray-200">Chart View</strong> — Visual tree with photos, colored position badges, connecting lines</li>
                    <li><strong class="text-gray-200">Data View</strong> — Department cards with positions and member avatars</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Important</h3>
                <p class="text-sm text-gray-400">Department and Position are for <strong class="text-white">organizational identity</strong> (org chart, profile display). They do NOT affect permissions — only your <strong class="text-white">Role</strong> determines what you can access.</p>
            </div>
        </x-filament::card>

        {{-- 13. PROFILE --}}
        <x-filament::card>
            <div id="profile" data-doc-section="Profile Settings">
                <h2 class="text-xl font-bold text-white mb-4">13. Profile Settings</h2>
                <p class="text-sm text-gray-400 mb-3">Access via your avatar (top-right) > click your name.</p>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Available Fields</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li><strong class="text-gray-200">Profile Picture</strong> — Upload JPG/PNG/WebP (auto-cropped to 200x200). If not uploaded, a gender-based avatar is assigned automatically.</li>
                    <li><strong class="text-gray-200">Name</strong> — Your display name</li>
                    <li><strong class="text-gray-200">Username</strong> — For @mentions (letters, numbers, underscores)</li>
                    <li><strong class="text-gray-200">Email</strong> — Primary email (requires verification if changed)</li>
                    <li><strong class="text-gray-200">Secondary Email (CC)</strong> — Optional CC email for notifications</li>
                    <li><strong class="text-gray-200">Gender</strong> — Male/Female/Other (assigns default avatar if no upload)</li>
                    <li><strong class="text-gray-200">Birthday</strong> — For birthday celebration feature</li>
                    <li><strong class="text-gray-200">Department & Position</strong> — For org chart</li>
                    <li><strong class="text-gray-200">Direct Supervisor</strong> — Creates hierarchy in org chart</li>
                    <li><strong class="text-gray-200">Timezone</strong> — Auto-detected, manually overridable</li>
                    <li><strong class="text-gray-200">Language</strong> — UI language preference</li>
                    <li><strong class="text-gray-200">Default Project</strong> — Quick access project</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 14. CUSTOMER FEEDBACK --}}
        <x-filament::card>
            <div id="feedback" data-doc-section="Customer Feedback">
                <h2 class="text-xl font-bold text-white mb-4">14. Customer Feedback</h2>
                <p class="text-sm text-gray-400 mb-3">Collect and track client feedback. Feedback can be converted into tickets for action.</p>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Stakeholders and QA can submit feedback</li>
                    <li>PM and Account Managers can update and convert to ticket</li>
                    <li>Converted feedback links to the ticket for traceability</li>
                    <li>All updates trigger email notifications to the submitter</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 15. WRITING GUIDELINES --}}
        <x-filament::card>
            <div id="writing-rules" data-doc-section="Writing Guidelines">
                <h2 class="text-xl font-bold text-white mb-4">15. Writing Guidelines</h2>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Ticket Naming</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Be specific: <span class="text-green-400">"Fix login redirect loop on mobile Safari"</span> not <span class="text-red-400">"Fix login"</span></li>
                    <li>Start with a verb: Fix, Add, Update, Remove, Implement</li>
                    <li>Include context: which page, which feature, which user type</li>
                    <li>Keep under 80 characters</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Ticket Description</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Explain the <strong class="text-gray-200">why</strong>, not just the what</li>
                    <li>Include acceptance criteria when possible</li>
                    <li>Attach screenshots or references</li>
                    <li>For bugs: always fill Steps to Reproduce, Expected vs Actual</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Comment Etiquette</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Keep comments relevant to the ticket</li>
                    <li>Use @mention to notify specific people</li>
                    <li>Log time with <code class="px-1 rounded bg-gray-700">/spend</code> when applicable</li>
                    <li>Update status when you start/finish work</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Request Writing</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Be clear about the <strong class="text-gray-200">objective</strong> — what problem are you solving?</li>
                    <li>Define <strong class="text-gray-200">expected outcome</strong> — how do you measure success?</li>
                    <li>Set realistic <strong class="text-gray-200">impact</strong> — don't mark everything as Critical</li>
                    <li>Include relevant links, mockups, or references in the description</li>
                </ul>

                <h3 class="text-sm font-semibold text-gray-300 mt-4 mb-2">Daily Report Tips</h3>
                <ul class="text-sm text-gray-400 list-disc pl-5 space-y-1">
                    <li>Be concise — bullet points, not essays</li>
                    <li>Reference ticket codes (e.g., QOS-75)</li>
                    <li>Mention blockers early — don't wait until they're critical</li>
                    <li>Plans should be actionable, not vague</li>
                </ul>
            </div>
        </x-filament::card>

        {{-- 16. DO'S AND DON'TS --}}
        <x-filament::card>
            <div id="dos-donts" data-doc-section="Do's and Don'ts">
                <h2 class="text-xl font-bold text-white mb-4">16. Do's & Don'ts</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-green-400 mb-3">DO's</h3>
                        <ul class="text-sm text-gray-400 space-y-2">
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Update ticket status when you start/finish work</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Log your time regularly</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Submit daily reports every working day</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Use @mentions to notify relevant people</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Write clear ticket names and descriptions</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Use Request type for cross-department asks</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Keep discussions focused and productive</li>
                            <li class="flex items-start gap-2"><span class="text-green-400 mt-0.5">&#10003;</span> Complete your profile (photo, department, position)</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-red-400 mb-3">DON'Ts</h3>
                        <ul class="text-sm text-gray-400 space-y-2">
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't create execution tickets (Task/Bug) without proper requirement</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't change another person's ticket status without communication</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't mark all requests as "Critical" impact</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't leave tickets in "In Progress" indefinitely</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't skip daily reports — it affects team visibility</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't create duplicate tickets — search first</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't use comments for off-topic chat</li>
                            <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&#10007;</span> Don't ignore notifications — they're there for a reason</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::card>

        {{-- 17. FAQ --}}
        <x-filament::card>
            <div id="faq" data-doc-section="Frequently Asked Questions">
                <h2 class="text-xl font-bold text-white mb-4">17. FAQ</h2>

                <div class="space-y-4 text-sm" x-data="{ open: null }">
                    @php
                    $faqs = [
                        ['q' => 'I forgot my password. How do I reset it?', 'a' => 'Click "Forgot Password" on the login page. A reset link will be sent to your email.'],
                        ['q' => 'I can\'t see a project. Why?', 'a' => 'You need to be added as a member of the project by the project owner or PM.'],
                        ['q' => 'How do I change my role?', 'a' => 'Only Super Admin can change roles. Contact your administrator.'],
                        ['q' => 'Can I create tickets directly without a Request?', 'a' => 'Depends on your role. Delivery roles (Dev, QA, Designer, etc.) can create Task/Bug/Feature directly. Non-delivery roles (HR, Sales, Finance) should use the Request type.'],
                        ['q' => 'How does the birthday feature work?', 'a' => 'Set your birthday in Profile Settings. On your birthday, a celebration banner appears on everyone\'s dashboard with your name, age, and animated balloons around your nav avatar.'],
                        ['q' => 'Why am I not getting email notifications?', 'a' => 'Check if your email is verified. Also check spam/junk folder. If using secondary email, ensure it\'s set in Profile Settings.'],
                        ['q' => 'How do I mention someone?', 'a' => 'Type @ followed by their username in any comment or discussion reply. A dropdown will appear — select the person.'],
                        ['q' => 'Can I delete a ticket?', 'a' => 'Only Super Admin can delete tickets. Other users can close or mark as resolved.'],
                        ['q' => 'What timezone does the app use?', 'a' => 'Default is Asia/Jakarta (GMT+7). Your timezone is auto-detected from your browser and can be changed in Profile Settings.'],
                        ['q' => 'How do I export timesheet data?', 'a' => 'On any ticket with logged hours, click the "Export time logged" button to download a CSV.'],
                        ['q' => 'What is the difference between Role and Position?', 'a' => 'Role = system permissions (what you can do). Position = organizational title (for org chart display). They are independent.'],
                        ['q' => 'How do I subscribe to a ticket?', 'a' => 'Click the "Subscribe" button on the ticket detail page. You\'ll receive notifications for all updates.'],
                    ];
                    @endphp

                    @foreach($faqs as $i => $faq)
                    <div class="border border-gray-700 rounded-lg overflow-hidden">
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-800 transition-colors">
                            <span class="font-medium text-gray-200">{{ $faq['q'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0 ml-2" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse class="px-4 pb-3">
                            <p class="text-gray-400">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </x-filament::card>

        {{-- Footer --}}
        <div class="text-center py-8 text-xs text-gray-500">
            <p>PM Helper Documentation — Last updated {{ now()->format('d F Y') }}</p>
            <p class="mt-1">Built for Capella Digicrats ID</p>
        </div>
    </div>
</div>
</x-filament::page>
