<?php

namespace App\Http\Livewire\Ticket;

use App\Models\Ticket;
use App\Models\TicketQaChecklist;
use Filament\Facades\Filament;
use Livewire\Component;

class QaChecklist extends Component
{
    public Ticket $ticket;
    public string $newItemDescription = '';
    public ?int $editingItemId = null;
    public string $editingNotes = '';

    protected $listeners = ['qaChecklistUpdated' => '$refresh'];

    public function render()
    {
        $checklists = $this->ticket->qaChecklists()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $summary = [
            'total' => $checklists->count(),
            'passed' => $checklists->where('status', 'passed')->count(),
            'failed' => $checklists->where('status', 'failed')->count(),
            'pending' => $checklists->where('status', 'pending')->count(),
        ];

        return view('livewire.ticket.qa-checklist', [
            'checklists' => $checklists,
            'summary' => $summary,
        ]);
    }

    public function addItem(): void
    {
        if (empty(trim($this->newItemDescription))) {
            return;
        }

        TicketQaChecklist::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->user()->id,
            'description' => trim($this->newItemDescription),
            'status' => 'pending',
        ]);

        $this->newItemDescription = '';
        Filament::notify('success', __('QA checklist item added'));
    }

    public function setStatus(int $itemId, string $status): void
    {
        $item = TicketQaChecklist::where('id', $itemId)
            ->where('ticket_id', $this->ticket->id)
            ->first();

        if (!$item) return;

        $item->update(['status' => $status]);
        Filament::notify('success', __('Status updated to :status', ['status' => $status]));
    }

    public function startEditNotes(int $itemId): void
    {
        $item = TicketQaChecklist::find($itemId);
        $this->editingItemId = $itemId;
        $this->editingNotes = $item->notes ?? '';
    }

    public function saveNotes(): void
    {
        if (!$this->editingItemId) return;

        $item = TicketQaChecklist::where('id', $this->editingItemId)
            ->where('ticket_id', $this->ticket->id)
            ->first();

        if ($item) {
            $item->update(['notes' => $this->editingNotes]);
            Filament::notify('success', __('Notes saved'));
        }

        $this->editingItemId = null;
        $this->editingNotes = '';
    }

    public function cancelEditNotes(): void
    {
        $this->editingItemId = null;
        $this->editingNotes = '';
    }

    public function deleteItem(int $itemId): void
    {
        TicketQaChecklist::where('id', $itemId)
            ->where('ticket_id', $this->ticket->id)
            ->delete();

        Filament::notify('success', __('Checklist item removed'));
    }
}
