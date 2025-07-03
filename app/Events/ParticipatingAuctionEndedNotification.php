<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;

class ParticipatingAuctionEndedNotification implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $auction_id;
    public $member_ids;
    public $message;

    public function __construct($auction_id, $member_ids)
    {
        $this->auction_id = $auction_id;
        $this->member_ids = $member_ids;
        $this->message = 'An auction you are participating in has ended. Check the final status.';

        $this->storeNotifications();
    }

    private function storeNotifications()
    {
        foreach ($this->member_ids as $member_id) {
            Notification::create([
                'notified_id' => $member_id,
                'urgency' => 'Medium',
                'text' => $this->message,
                'url' => route('auction.show', ['id' => $this->auction_id]),
                'seen' => false,
            ]);
        }
    }

    public function broadcastOn()
    {
        return collect($this->member_ids)->map(fn($member_id) => new PrivateChannel('App.Models.Member.' . $member_id))->toArray();
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'auction_id' => $this->auction_id,
        ];
    }
}
