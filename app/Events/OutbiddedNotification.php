<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class OutbiddedNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $bidder_id;
    public $message;

    public function __construct($auction_id, $bidder_id)
    {
        $this->auction_id = $auction_id;
        $this->bidder_id = $bidder_id;
        $this->message = 'You have been outbid on an auction.';

        //$this->storeNotification(); this is already done in a trigger
    }

    private function storeNotification()
    {
        Notification::create([
            'notified_id' => $this->bidder_id,
            'urgency' => 'high',
            'text' => $this->message,
            'url' => route('auction.show', ['id' => $this->auction_id]),
            'seen' => false,
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.Member.' . $this->bidder_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}
