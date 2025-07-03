<div class="auction_bidding_history">
    <h3 style="color:#000000">Bidding History</h3>
    <div class="bidding_list">
        <div class="arrow-container">
            <div class="arrow-head"></div>
            <div class="arrow-tail"></div>
        </div>
        <div class="bidding_members">
            @foreach ($auction->bids->reverse() as $bid)
                <a class="member-bid" href="{{ route('profile', $bid->bidder->username) }}">
                    <div class="member-no-value">
                        <div class="bid-profile">
                                <img src="{{ $bid->bidder->getProfileImage() }}" width="50" height="50" alt="Bidder Profile Picture"/>
                        </div>
                        <div class="bid-username">{{ $bid->bidder->username }}</div>
                    </div>
                    <div class="bid-value">{{ $bid->value }} €</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
