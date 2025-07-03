<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class AuctionWinnerNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $winner_id;
    public $message;

    public function __construct($auction_id, $winner_id)
    {
        $this->auction_id = $auction_id;
        $this->winner_id = $winner_id;
        $this->message = 'Congratulations! You have won an auction.';

        //$this->storeNotification(); already done in end_auctions
    }

    private function storeNotification()
    {
        Notification::create([
            'notified_id' => $this->winner_id,
            'urgency' => 'high',
            'text' => $this->message,
            'url' => route('auction.show', ['id' => $this->auction_id]),
            'seen' => false,
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.Member.' . $this->winner_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}
