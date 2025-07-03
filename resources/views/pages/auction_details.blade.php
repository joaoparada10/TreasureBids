@extends('layouts.app')

@section('title', 'TB - '.$auction->title)

@section('content')

@php
use Illuminate\Support\Facades\Storage;
@endphp

  <div class="auction_essentials_card">

    <div class="item_picture">
      <img src="{{ $auction->getAuctionImage() }}" alt="Auction Image"
        style="max-width: 500px; max-height: 500px; object-fit: contain;width:100%;">
    </div>
    <div class="test">
      <div class="auction_essentials_card_title_section">
        <h2 style="color: black">{{ $auction->title }}</h2>
        <div id="time-left" class="text-center time-left" data-start-date="{{ $auction->starting_date }}" data-end-date="{{ $auction->end_date }}">
          Loading...
        </div>
        <div class="owner-rating">
          
          <div style="color:black">
            @if ($averageRating)
            <div class="bold " style="color:#00000044;padding: 5px">{{ $auction->owner->username }}'s Rating:</div>
            <div id="star-display" class="star-rating justify-content-center" rating={{$averageRating}}></div>
            @else
            @endif
          </div>
        </div>
        <div class="auction-category-list">@include('partials.item_categories', ['categories' => $auction->category])</div>
      </div>
      <div class="auction_essentials_card_bid_section">
        @if (Auth::guard('web')->check() && (auth()->user()->id !== $auction->owner_id))
        <div class="favorite" onclick="toggleFollow({{ $auction->id }})">
          @if ($isFollowing)
          <i id="follow-star" class="bi bi-star-fill"></i>
          @else
          <i id="follow-star" class="bi bi-star"></i>
          @endif
        </div>
        @endif
        <div class="no-star">
        @if ($auction->highestbid)
        <div class="highest_bid">
          <div class="bold " style="color:#00000044">Highest bid:</div>
          <div class="highest_bid_value bold text-center">

            €{{ $auction->highestbid->value }}

          </div>
        </div>
        @else
        <div class="highest_bid">
          <div class="bold" style="color:#00000044">Starting price:</div>
          <div class="highest_bid_value bold text-center">
            €{{ $auction->starting_price }}
          </div>
        </div>
        @endif
          <div class="crumbs_and_edit_details_button">
        @if ($auction->owner_id === auth()->id() && ($auction->status === 'Active' || $auction->status === 'Scheduled'))
        <div class="auction-management">
          <a href="{{ route('auctions.edit', $auction->id) }}" class="btn btn-outline-primary">Edit Details</a>
          <form action="{{ route('auctions.cancel', $auction) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
              onclick="return confirm('Are you sure you want to cancel this auction? This action cannot be undone.')">
              Cancel Auction
            </button>
          </form>
        </div>
        @endif
      </div>
    </div>
        <!-- Buttons for Transaction Logic -->
        @if ($auction->status === 'Concluded')
        @if ($auction->highestbid)
        <p>
          Winner:
          <a href="{{ route('profile', $auction->highestBid->bidder->username) }}">
            {{ $auction->highestBid->bidder->username }}
          </a>
        </p>
        @else
        <p>No bids were placed on this auction.</p>
        @endif
        @if ($auction->owner_id === auth()->id())
        

        @if (!$auction->transaction && $auction->highestBid)
        <!-- No transaction exists -->
        <p>The auction is concluded. Please create a transaction for the winner.</p>
        <a href="{{ route('transactions.create', $auction->id) }}" class="btn btn-primary">Create Transaction</a>
        @elseif ($auction->highestBid)
        <a href="{{ route('transactions.show', $auction->transaction->id) }}" class="btn btn-secondary">View
          Transaction</a>
        @endif

        @elseif ($auction->highestBid && $auction->highestBid->user_id === auth()->id())
        <!-- Auction winner logic -->
        <p>Congratulations, you won the auction!</p>
        @if (!$auction->transaction)
        <!-- No transaction exists -->
        <p>Please wait for the Auction Owner to create the transaction.</p>
        @else
        <a href="{{ route('transactions.show', $auction->transaction->id) }}" class="btn btn-secondary">View
          Transaction</a>
        @endif
        @endif
        @endif
        @if ($auction->status === 'Cancelled')
        <p>This Auction has been cancelled.</p>
        @endif

        @if (Auth::guard('web')->check() && (auth()->user()->id !== $auction->owner_id) && $auction->status ===
        'Active')
        <div class="place_bid">
          <form class="place_bid_form" action="{{ route('auction.bid', $auction->id) }}" method="POST">
            @csrf
            <div class="bid_box">
              <label style="color:#00000044">Your Credits: €{{auth()->user()->credit}}</label>
              <input id="bid" type="number" name="bid" value="{{ old('bid') }}" placeholder="Place bid..." required
                autofocus>
            </div>
            <div class="bid_button">
              <button type="submit" class="btn btn-outline-primary">
                Bid
              </button>
            </div>
          </form>

        </div>
        @endif
      </div>
    </div>
  </div>
  <div class="auction_essentials_card" style="display: block ! important;">
    <div class="auction_description">
      <h3 style="color:#000000">Description</h3>
      <div style="color: black">{{$auction->description}}</div>
    </div>
    @if (($auction->owner_id === auth()->id() || collect($auction['bids'])->contains('user_id', auth()->id())) && $auction->highestBid)
    <div class="auction_bidding_history">
      <h3 style="color:#000000">Bidding History </h3>
      <div class="bidding_list">
        <div class="arrow-container">
          <div class="arrow-head"></div>
          <div class="arrow-tail"></div>
        </div>
        <div class="bidding_members">
          @foreach ($auction->bids->reverse() as $bid)
          <div class="member-bid">
            <a class="member-no-value" href="{{ route('profile', $bid->bidder->username) }}">
              <div class="bid-profile">
                <img src="{{ $bid->bidder->getProfileImage() }}" width="50" height="50" / alt="Bidder Profile Picture">
              </div>
              <div class="bid-username">{{$bid->bidder->username}}</div>
            </a>
            <div class="bid-value">{{$bid->value}} €</div>
          </div>
          @endforeach

        </div>
      </div>
    </div>
    @endif
  </div>

  <script>
    starRating()
  </script>

  @endsection