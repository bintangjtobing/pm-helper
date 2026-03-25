# Roles & Permissions

This document defines the role-based access control (RBAC) structure for PMHelper.

---

## Role Overview

| Role | Level | Total Permissions | Purpose |
|------|-------|:-----------------:|---------|
| **Super Admin** | System | 69 | Full system control |
| **Project Manager** | Business | 36 | Project & delivery control |
| **Developer** | Execution | 23 | Task execution |
| **QA / Tester** | Quality | 27 | Quality validation |
| **DevOps** | Delivery | 24 | Deployment & infrastructure |
| **Stakeholder** | Visibility | 14 | Read-only visibility + feedback |

---

## Super Admin

> Full system control. Only assign to trusted system administrators.

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View, Create, Update, Delete |
| **Project Statuses** | List, View, Create, Update, Delete |
| **Tickets** | List, View, Create, Update, Delete |
| **Ticket Statuses** | List, View, Create, Update, Delete |
| **Ticket Types** | List, View, Create, Update, Delete |
| **Ticket Priorities** | List, View, Create, Update, Delete |
| **Sprints** | List, View, Create, Update, Delete |
| **Comments** | List, View, Create, Update, Delete |
| **Activities** | List, View, Create, Update, Delete |
| **Users** | List, View, Create, Update, Delete |
| **Roles** | List, View, Create, Update, Delete |
| **Permissions** | List, View, Create, Update, Delete |
| **Customer Feedback** | List, View, Create, Update, Delete |
| **System** | Manage general settings, Import from Jira |
| **Timesheet** | List timesheet data, View timesheet dashboard |

---

## Project Manager

> Project & delivery control. Can manage projects, sprints, tickets, and view reports. Cannot manage system-level entities (roles, permissions, users).

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View, Create, Update |
| **Project Statuses** | List, View, Update |
| **Tickets** | List, View, Create, Update |
| **Ticket Statuses** | List, View, Update |
| **Ticket Types** | List, View |
| **Ticket Priorities** | List, View, Update |
| **Sprints** | List, View, Create, Update |
| **Comments** | List, View, Create, Update |
| **Activities** | List, View |
| **Users** | List, View |
| **Customer Feedback** | List, View, Update |
| **Timesheet** | List timesheet data, View timesheet dashboard |

**Cannot:** Delete projects/tickets, manage roles/permissions, manage users, manage general settings.

---

## Developer

> Task execution. Can work on tickets, log time, and comment. Cannot delete or manage projects.

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View |
| **Project Statuses** | List, View |
| **Tickets** | List, View, Create, Update |
| **Ticket Statuses** | List, View |
| **Ticket Types** | List, View |
| **Ticket Priorities** | List, View |
| **Sprints** | List, View |
| **Comments** | List, View, Create, Update |
| **Activities** | List, View |
| **Timesheet** | List timesheet data |

**Cannot:** Delete tickets, create projects, manage users, update priority, view timesheet dashboard.

---

## QA / Tester

> Quality validation. Can update ticket status (critical for QA flow), create bug reports, and manage customer feedback.

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View |
| **Project Statuses** | List, View |
| **Tickets** | List, View, Update |
| **Ticket Statuses** | List, View, Update |
| **Ticket Types** | List, View |
| **Ticket Priorities** | List, View |
| **Sprints** | List, View |
| **Comments** | List, View, Create, Update |
| **Activities** | List, View |
| **Customer Feedback** | List, View, Create, Update |
| **Timesheet** | List timesheet data |

**Cannot:** Create tickets (use Bug Report via comments), delete anything, create projects, update priority.

### Status Transition Rights (Role-Based)

QA / Tester can only move tickets to statuses in the `qa` role group:
- QA Testing
- QA Failed
- Retest
- QA Passed

---

## DevOps

> Deployment & infrastructure. Can update project and ticket status for release tracking.

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View |
| **Project Statuses** | List, View, Update |
| **Tickets** | List, View, Update |
| **Ticket Statuses** | List, View, Update |
| **Ticket Types** | List, View |
| **Ticket Priorities** | List, View |
| **Sprints** | List, View |
| **Comments** | List, View, Create |
| **Activities** | List, View |
| **Timesheet** | List timesheet data, View timesheet dashboard |

**Cannot:** Create projects/tickets, manage business logic (priority, feature decisions), manage users.

### Status Transition Rights (Role-Based)

DevOps can only move tickets to statuses in the `business` role group:
- Waiting Approval
- Approved
- Rejected
- Ready for Release
- Released

---

## Stakeholder

> Read-only visibility with feedback submission. For clients, executives, and external stakeholders.

| Category | Permissions |
|----------|-------------|
| **Projects** | List, View |
| **Project Statuses** | List, View |
| **Tickets** | List, View |
| **Ticket Statuses** | View |
| **Ticket Types** | View |
| **Ticket Priorities** | View |
| **Sprints** | List, View |
| **Customer Feedback** | List, View, Create |

**Cannot:** Update or delete anything. Create projects, tickets, or comments. View timesheet data.

---

## Status Transition Matrix

| Status | Role Group | Super Admin | PM | Developer | QA | DevOps | Stakeholder |
|--------|:----------:|:-----------:|:--:|:---------:|:--:|:------:|:-----------:|
| Backlog | any | Yes | Yes | Yes | Yes | Yes | No |
| Ready for Dev | dev | Yes | Yes | Yes | No | No | No |
| In Progress | dev | Yes | Yes | Yes | No | No | No |
| Code Review | dev | Yes | Yes | Yes | No | No | No |
| Ready for QA | dev | Yes | Yes | Yes | No | No | No |
| QA Testing | qa | Yes | Yes | No | Yes | No | No |
| QA Failed | qa | Yes | Yes | No | Yes | No | No |
| Fixing | dev | Yes | Yes | Yes | No | No | No |
| Retest | qa | Yes | Yes | No | Yes | No | No |
| QA Passed | qa | Yes | Yes | No | Yes | No | No |
| Waiting Approval | business | Yes | Yes | No | No | Yes | No |
| Approved | business | Yes | Yes | No | No | Yes | No |
| Rejected | business | Yes | Yes | No | No | Yes | No |
| Ready for Release | business | Yes | Yes | No | No | Yes | No |
| Released | business | Yes | Yes | No | No | Yes | No |

---

*Last updated: 2026-03-25*
