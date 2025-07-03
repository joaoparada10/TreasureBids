<div class="card auction-card" data-href="{{ route('auction.show', $auction->id) }}">
    <div style="display: flex;flex-direction: column; width: 300px;">
        <img src="{{ $auction->getAuctionImage() }}" alt="Auction Image"
        style="max-width: 350px; max-height: 200px; object-fit: cover;border-top-left-radius: 10px;border-top-right-radius: 10px;">
        <div class="auction-card-info">
            <div style="padding: 1em;">{{$auction->title}}</div>
            <div style="margin: 1em; display: flex; flex-direction: row; justify-content: space-between; padding: 5px;align-items: center;">
                <div class="card-highest-bid">{{$auction->highestBid ? '€'.$auction->highestBid->value : "no bids"}}</div>
                <div id="time-left" 
                    class="text-center time-left" 
                    data-start-date="{{ $auction->starting_date ? $auction->starting_date->format('Y-m-d\TH:i:s') : '' }}"
                    data-end-date="{{ $auction->end_date ? $auction->end_date->format('Y-m-d\TH:i:s') : '' }}">
                    Loading...
                </div>
            </div>
        </div>
    </div>
    <div class="tags" style="margin-top: 1em;">
            @include('partials.item_categories', ['categories' => $auction->category])
            <!-- @include('partials.item_categories', ['categories' => $auction->category]) -->
    </div>
</div>

