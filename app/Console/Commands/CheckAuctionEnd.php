<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Events\AuctionEndingSoonNotification;
use App\Events\OwnedAuctionEndedNotification;
use App\Events\ParticipatingAuctionEndedNotification;
use App\Events\AuctionWinnerNotification;


class CheckAuctionEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:check-end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any auction has ended and process them accordingly';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $now = Carbon::now();
    $startWindow = $now->copy()->addMinutes(14)->startOfMinute();
    $endWindow = $now->copy()->addMinutes(15)->startOfMinute();

    // Deal with finished auctions
    // This part only triggers the notification events
    $endedAuctions = Auction::where('status', 'Active')
        ->where('end_date', '<', $now)
        ->get();
    
        foreach ($endedAuctions as $auction) {
            $winner = $auction->bids()
                ->orderBy('value', 'desc')
                ->orderBy('date', 'asc')
                ->first();

            if ($winner) {
                event(new AuctionWinnerNotification($auction->id, $winner->user_id));
                $bidderIds = $auction->bids()
                ->where('user_id', '!=', $winner->user_id)
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            }

            else {
                $bidderIds = $auction->bids()
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            }
            if (!empty($bidderIds)) {
                event(new ParticipatingAuctionEndedNotification($auction->id, $bidderIds));
            }
            event(new OwnedAuctionEndedNotification($auction->id, $auction->owner_id));
            
        }
    
    // This part takes care of the actual database operations
    try {
        DB::statement('SELECT end_auctions()');

        $this->info('end_auctions function executed successfully.');
    } catch (\Exception $e) {
        $this->error('Error executing end_auctions: ' . $e->getMessage());
    }

    // Find auctions ending in the next 15 minutes
    $almostEndedAuctions = Auction::whereBetween('end_date', [$startWindow, $endWindow])
        ->where('status', 'Active')
        ->get();

    foreach ($almostEndedAuctions as $auction) {

        $bidderIds = $auction->bids()->distinct()->pluck('user_id')->toArray();
        event(new AuctionEndingSoonNotification($auction->id, $bidderIds));
    }
}
}
