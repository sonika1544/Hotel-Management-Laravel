<?php

namespace App\Http\Controllers;

class NotificationsController extends Controller
{
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }

    public function routeTo($id)
    {
        $notification = auth()->user()->Notifications->find($id);
        if ($notification) {
            $notification->markAsRead();
            
            // Check if URL exists in notification data
            if (isset($notification->data['url'])) {
                return redirect($notification->data['url']);
            }
            
            // Default fallback to dashboard if no URL is found
            return redirect()->route('dashboard.index');
        }
        
        // If notification not found, redirect to dashboard
        return redirect()->route('dashboard.index');
    }
}
