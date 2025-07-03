@extends('layouts.app')
@section('content')

<section class="introduction-section text-center py-5">
  <div style="display: flex; flex-direction: column; align-items: center">
    <h1 class="display-4">Welcome to <span class="fw-bold logo-text">Treasure Bids</span></h1>
    <p class="lead mt-3">Your gateway to exquisite antiques, artwork, and luxury auctions.</p>
    <p class="intro-description">
      You can start by browsing some of our trending auctions below or use the search bar above to find something
      specific.
    </p>
    <a class="most-important-button-in-the-world" href="{{ route('category.show', 1) }}">Check out our auctions</a>
  </div>
</section>

<div class="nav">
  <div class="navButton" id="prev">
    <i class="bi bi-arrow-left"></i>
  </div>
  <div class="preview">
    <div class="auction-container">
      @foreach (collect($auctions)->take(5) as $index => $auction)
      <div class="card preview-auction" data-index="{{ $index }}"
        onclick="window.location.href='/auctions/{{$auction->id}}';">
        <div class="image-container">
          <img src="{{ $auction->getAuctionImage() }}" alt="Auction Image"/>
          
        </div>
        <div class="preview-info">
          <div class="preview-title-descryption" >
            <div class="preview-title">
              {{$auction->title}}
            </div>
            <div class="preview-description">
              {{$auction->description}}
            </div>
          </div>
          <div class="preview-price">
            {{$auction->highestbid ? $auction->highestbid->value : "$auction->starting_price"}} €
          </div>
          <div style="display: flex;justify-content: space-between">
            <div class="preview-auction-categories" style="display: flex;flex-direction: row;align-self: center;overflow-x: scroll;">
              @include('partials.item_categories', ['categories' => $auction->category])
            </div>
            <div id="time-left" 
                class="text-center time-left" 
                data-start-date="{{ $auction->starting_date->format('Y-m-d\TH:i:s') }}"
                data-end-date="{{ $auction->end_date->format('Y-m-d\TH:i:s') }}">
                Loading...
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="navButton" id="next">
    <i class="bi bi-arrow-right"></i>
  </div>
</div>
</div>

<script src="{{ asset('js/homepage.js') }}"></script>


@endsection