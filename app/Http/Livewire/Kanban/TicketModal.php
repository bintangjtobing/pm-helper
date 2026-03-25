<?php

namespace App\Http\Livewire\Kanban;

use App\Models\Ticket;
use Livewire\Component;

class TicketModal extends Component
{
    public $showModal = false;
    public $ticketId = null;
    public $ticket = null;

    protected $listeners = [
        'openTicketModalComponent' => 'openModal',
        'closeTicketModalComponent' => 'closeModal'
    ];

    public function openModal($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->ticket = Ticket::with([
            'owner', 'responsible', 'status', 'type', 'priority',
            'project', 'epic', 'sprint', 'comments.user',
            'activities.user', 'activities.oldStatus', 'activities.newStatus',
            'hours.user', 'hours.activity'
        ])->find($ticketId);

        // Security: verify user has access to this ticket
        if (!$this->ticket || !auth()->user()->can('view', $this->ticket)) {
            $this->ticket = null;
            $this->ticketId = null;
            return;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->ticketId = null;
        $this->ticket = null;
    }

    public function render()
    {
        return view('livewire.kanban.ticket-modal');
    }
}
