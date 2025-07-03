<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class OwnedAuctionEndedNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $owner_id;
    public $message;

    public function __construct($auction_id, $owner_id)
    {
        $this->auction_id = $auction_id;
        $this->owner_id = $owner_id;
        $this->message = 'One of your auctions has ended. Check the final status.';

        //$this->storeNotification(); already done in end_auctions
    }

    private function storeNotification()
    {
        Notification::create([
            'notified_id' => $this->owner_id,
            'urgency' => 'Medium',
            'text' => $this->message,
            'url' => route('auction.show', ['id' => $this->auction_id]),
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