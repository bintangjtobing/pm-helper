<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\WeeklyReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WeeklyReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('List weekly reports');
    }

    public function view(User $user, WeeklyReport $weeklyReport): bool
    {
        if (!$user->can('View weekly report')) {
            return false;
        }

        // Owner can always view their own
        if ($weeklyReport->user_id === $user->id) {
            return true;
        }

        // Super Admin and Stakeholder see all
        if ($user->hasRole(['Super Admin', 'Stakeholder'])) {
            return true;
        }

        // PM can see reports from team members on shared projects
        if ($user->hasRole('Project Manager')) {
            return $this->sharesProject($user, $weeklyReport->user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('Create weekly report');
    }

    public function update(User $user, WeeklyReport $weeklyReport): bool
    {
        return $user->can('Update weekly report')
            && $weeklyReport->user_id === $user->id
            && $weeklyReport->status === 'draft';
    }

    public function delete(User $user, WeeklyReport $weeklyReport): bool
    {
        if (!$user->can('Delete weekly report')) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $weeklyReport->user_id === $user->id && $weeklyReport->status === 'draft';
    }

    private function sharesProject(User $pm, User $author): bool
    {
        $pmProjectIds = $this->getUserProjectIds($pm);
        $authorProjectIds = $this->getUserProjectIds($author);

        return $pmProjectIds->intersect($authorProjectIds)->isNotEmpty();
    }

    private function getUserProjectIds(User $user)
    {
        $owned = Project::where('owner_id', $user->id)->pluck('id');
        $attached = $user->projects()->pluck('projects.id');

        return $owned->merge($attached)->unique();
    }
}
