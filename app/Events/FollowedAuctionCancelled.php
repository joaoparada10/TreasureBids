<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Notification;

class FollowedAuctionCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $auction_id;
    public $user_ids; // Array of user IDs to notify

    /**
     * Create a new event instance.
     */
    public function __construct($auction_id, $user_ids)
    {
        $this->auction_id = $auction_id;
        $this->user_ids = $user_ids;
        $this->message = 'An auction you followed has been cancelled.';
        $this->storeNotifications();
    }

    private function storeNotifications()
    {
        foreach ($this->user_ids as $user_id) {
            Notification::create([
                'notified_id' => $user_id, 
                'urgency' => 'Medium',
                'text' => $this->message,
                'url' => route('auction.show', ['id' => $this->auction_id]),
                'date' => now(),
                'seen' => false,
            ]);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
{
        
    return collect($this->user_ids)->map(function ($user_id) {
        return new PrivateChannel('App.Models.Member.' . $user_id);
    })->toArray();
}


    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}
