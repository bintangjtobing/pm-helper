<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'color', 'is_default', 'order',
        'project_id', 'role_group'
    ];

    /**
     * Role groups and which roles can transition to statuses in each group.
     */
    public const ROLE_GROUP_ACCESS = [
        'dev'      => ['Super Admin', 'Project Manager', 'Developer'],
        'qa'       => ['Super Admin', 'Project Manager', 'QA / Tester'],
        'business' => ['Super Admin', 'Project Manager', 'DevOps'],
        'any'      => null, // null = all roles allowed
    ];

    /**
     * Check if a user can transition a ticket to this status.
     */
    public function canBeSetByUser(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) return false;

        // 'any' group = everyone can use it
        if ($this->role_group === 'any' || !$this->role_group) {
            return true;
        }

        $allowedRoles = self::ROLE_GROUP_ACCESS[$this->role_group] ?? null;
        if ($allowedRoles === null) {
            return true;
        }

        return $user->hasAnyRole($allowedRoles);
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function (TicketStatus $item) {
            if ($item->is_default) {
                $query = TicketStatus::where('id', '<>', $item->id)
                    ->where('is_default', true);
                if ($item->project_id) {
                    $query->where('project_id', $item->project->id);
                }
                $query->update(['is_default' => false]);
            }

            $query = TicketStatus::where('order', '>=', $item->order)->where('id', '<>', $item->id);
            if ($item->project_id) {
                $query->where('project_id', $item->project->id);
            }
            $toUpdate = $query->orderBy('order', 'asc')
                ->get();
            $order = $item->order;
            foreach ($toUpdate as $i) {
                if ($i->order == $order || $i->order == ($order + 1)) {
                    $i->order = $i->order + 1;
                    $i->save();
                    $order = $i->order;
                }
            }
        });
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'status_id', 'id')->withTrashed();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}
