@php
    use Carbon\Carbon;
@endphp
<div class="profile-auctions created-auctions mb-5 {{ request('createdAuctionsPage') || !request('biddedAuctionsPage') && !request('followedAuctionsPage') ? 'selected' : '' }}">
        <h2 class="text-secondary">Created Auctions</h2>
        @if(isset($createdAuctions) && $createdAuctions->isEmpty())
        <p class="text-muted">No live auctions created by you.</p>
        @else
        <ul class="list-group">
          @foreach($createdAuctions as $auction)
          @php
          $now = Carbon::now();
          $startDate = Carbon::parse($auction->starting_date);
          $endDate = Carbon::parse($auction->end_date);
          @endphp
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <a href="{{ route('auction.show', $auction->id) }}" class="text-decoration-none text-dark">
                <strong>{{ $auction->title }}</strong>
              </a>
              <span class="badge rounded-pill" style="
                  background-color: 
                    {{ $auction->status === 'Active' ? 'green' : 
                      ($auction->status === 'Scheduled' ? '#2C3E50' : 
                      ($auction->status === 'Concluded' ? '#CBA135' : 
                      ($auction->status === 'Cancelled' ? 'red' : 'grey'))) }};
                  color: white;
                ">
                {{ $auction->status }}
              </span>
            </div>
            <span class="badge bg-secondary rounded-pill">
              @if($auction->status === 'Scheduled')
              Starts in: {{ $startDate->diffForHumans($now, true) }}
              @elseif($auction->status === 'Concluded')
              Ended at: {{ $endDate->format('F j, Y, g:i a') }}
              @elseif($auction->status === 'Cancelled')
              Cancelled at: {{ $endDate->format('F j, Y, g:i a') }}
              @else
              Ends in: {{ $endDate->diffForHumans($now, true) }}
              @endif
            </span>
          </li>
          @endforeach
        </ul>
        <div class="mt-3">
          {{ $createdAuctions->appends(['section' => 'auctions'])->links() }}
      </div>
        @endif
      </div>
