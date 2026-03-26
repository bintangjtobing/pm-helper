<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where(function ($query) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($query) {
                                return $query->where('users.id', auth()->user()->id);
                            });
                    });
            });
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearchQuery();

        if (blank($search)) {
            return $query;
        }

        // First apply default column search
        $query = parent::applySearchToTableQuery($query);

        // Then extend with content, code, and comments search
        return $query->orWhere(function (Builder $q) use ($search) {
            $q->where(function ($sub) {
                // Re-apply access control inside orWhere
                return $sub->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhereHas('project', function ($pq) {
                        return $pq->where('owner_id', auth()->user()->id)
                            ->orWhereHas('users', function ($uq) {
                                return $uq->where('users.id', auth()->user()->id);
                            });
                    });
            })->where(function ($sub) use ($search) {
                $sub->where('content', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('comments', function ($cq) use ($search) {
                        $cq->where('content', 'like', "%{$search}%");
                    });
            });
        });
    }
}
