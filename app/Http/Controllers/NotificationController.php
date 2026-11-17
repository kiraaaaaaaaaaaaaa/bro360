<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact(
            'notifications',
            'unreadCount'
        ));
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        abort_unless(
            $notification->notifiable_id === Auth::id()
            && $notification->notifiable_type === User::class,
            403
        );

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(DatabaseNotification $notification)
{
    abort_unless(
        (int) $notification->notifiable_id === (int) Auth::id()
        && $notification->notifiable_type === User::class,
        403
    );

    $notification->markAsRead();

    $url = $notification->data['url']
        ?? route('notifications.index');

    return redirect($url);
}


}