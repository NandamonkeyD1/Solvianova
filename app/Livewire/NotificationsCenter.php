<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;

class NotificationsCenter extends Component
{
    public string $filterType = 'ALL';

    public function markAsRead(int $id)
    {
        $n = Notification::where('user_id', auth()->id())->find($id);
        if ($n) {
            $n->update(['is_read' => true]);
            if ($n->link) {
                return redirect()->to($n->link);
            }
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        $this->dispatch('toast', message: 'Seluruh notifikasi telah ditandai dibaca.');
    }

    public function render()
    {
        $query = Notification::where('user_id', auth()->id())->latest();

        if ($this->filterType === 'UNREAD') {
            $query->where('is_read', false);
        } elseif ($this->filterType !== 'ALL') {
            $query->where('type', $this->filterType);
        }

        $notifications = $query->get();

        return view('livewire.notifications-center', [
            'notifications' => $notifications,
        ]);
    }
}
