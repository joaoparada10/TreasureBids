<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class OwnedAuctionCancelledNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $owner_id;
    public $message;

    public function __construct($auction_id, $owner_id)
    {
        $this->auction_id = $auction_id;
        $this->owner_id = $owner_id;
        $this->message = 'One of your auctions has been canceled by an admin.';

        $this->storeNotification();
    }

    private function storeNotification()
    {
        Notification::create([
            'notified_id' => $this->owner_id,
            'urgency' => 'Medium',
            'text' => $this->message,
            'url' => route('profile.redirect'),
            'seen' => false,
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.Member.' . $this->owner_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}
