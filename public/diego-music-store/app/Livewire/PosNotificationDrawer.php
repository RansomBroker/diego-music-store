<?php

namespace App\Livewire;

use App\Models\EmployeeTask;
use Livewire\Component;

class PosNotificationDrawer extends Component
{
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function completeTask($notificationId, $taskId)
    {
        $this->markAsRead($notificationId);

        $task = EmployeeTask::find($taskId);
        if ($task && $task->status === 'pending') {
            $task->update(['status' => 'completed']);
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return <<<'HTML'
<div></div>
HTML;
        }

        $unreadNotifications = $user->unreadNotifications;
        $unreadCount = $unreadNotifications->count();

        // Separate notifications by type logic
        $productNotifications = $unreadNotifications->where('data.type', 'produk');
        $taskNotifications = $unreadNotifications->where('data.type', '!=', 'produk');

        $this->dispatch('notifications-updated', count: $unreadCount);

        return view('livewire.pos-notification-drawer', [
            'unreadCount' => $unreadCount,
            'productNotifications' => $productNotifications,
            'taskNotifications' => $taskNotifications,
        ]);
    }
}
