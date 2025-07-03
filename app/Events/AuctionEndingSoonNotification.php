<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class AuctionEndingSoonNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $user_ids;
    public $message;

    public function __construct($auction_id, $user_ids)
    {
        $this->auction_id = $auction_id;
        $this->user_ids = $user_ids;
        $this->message = 'An auction you are participating in is ending soon.';

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
                'seen' => false,
            ]);
        }
    }

    public function broadcastOn()
    {
        return collect($this->user_ids)->map(fn($user_id) => new PrivateChannel('App.Models.Member.' . $user_id))->toArray();
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}

